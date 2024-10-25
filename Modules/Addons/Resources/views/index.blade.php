<link rel="stylesheet" type="text/css" href="{{ asset('Modules\Addons\Resources\assets\css\addon.min.css') }}">
<?php 
  $addons = \Modules\Addons\Entities\Addon::all();
  $numberOfAddons = count(array_filter($addons, function($addon) { return !$addon->get('core'); }));
?>

@if(session('AddonMessage'))
  <div class="addon-alert addon-alert-{{ (session('AddonStatus') == 'success') ? 'success' : 'danger' }}">
    <span class="addon-alert-closebtn">&times;</span>  
    <strong>{{ session('AddonMessage') }}</strong>
  </div>
@endif

@if($errors->any())
  @foreach($errors->all() as $error)
    <div class="addon-alert addon-alert-danger">
      <span class="addon-alert-closebtn">&times;</span>  
      <strong>{{ $error }}</strong>
    </div>
  @endforeach
@endif

<div class="addons-section">
  <div class="addons-card">
      <h5>{{ __('Addons') }}</h5>
      <button id="upload-btn" class="upl-button f-14">{{ __('Upload Addon') }}</button>
    </div>
    <hr>

  <div id="addons-form-container" style="display: {{ ($numberOfAddons > 0) ? 'none' : 'block' }}">
    <form action="{{ route('addon.upload') }}" method="post" class="addons-form" enctype="multipart/form-data">
      @csrf
      <div>
        <!-- Purchase Code -->
        <div class="f-14">
          <p><strong>{{ __('Purchase Code') }}</strong></p>
          <input type="text" value="{{ old('purchase_code') }}" placeholder="Purchase Code" name="purchase_code" required>
        </div>
        <!-- Envato Username -->
        <div class="f-14 mt-2">
          <p><strong>{{ __('Envato Username') }}</strong></p>
          <input type="text" value="{{ old('username') }}" placeholder="Envato username" name="username" required>
        </div>
        <!-- Upload Module -->
        <div class="input-file-container f-14">
          <p><strong>{{ __('Module File') }}</strong></p>
          <input type="file" name="attachment" accept=".zip,.rar,.7zip" required>
          <div>
            <small class="form-text text-muted d-block">
              <strong>* {!! __('Script :x folder permission should be :y', ['x' => '<a class="link-parent" href="'. asset('public/admin/customs/images/module_permission.png') .'" target="_blank"><u>'. __('Modules') .'</u> <i class="fa fa-external-link-square" aria-hidden="true"></i></a>', 'y' => '0775' ]) !!}</strong>
            </small>
            <small class="form-text text-muted">
              <strong>* {!! __('Max :x should be minimum of :y', ['x' => '<a class="link-parent" target="_blank" href="https://docs.paymoney.techvill.net/how-to-increase-the-php-max-upload-size/"><u>'.__('Upload & Post size').'</u> <i class="fa fa-external-link-square" aria-hidden="true"></i></a>', 'y' => __('10MB')]) !!}</strong>
            </small>
          </div>
        </div>
        <!-- Upload Button -->
        <div class="input-file-container"><button class="submit-style f-14" type="submit">{{ __('Upload') }}</button></div>
      </div>
    </form>
    <hr>
  </div>

  <div class="addons-table-container f-14">
    @if($numberOfAddons > 0)
      <table>
          <thead>
            <tr>
                <th>{{ __('Addons') }}</th>
                <th>{{ __('Module') }}</th>
                <th>{{ __('Description') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($addons as $addon)
              @if($addon->get('core')) @continue @endif
              <tr>
                <td><img  class="addons-img" src="{{ addonThumbnail($addon->getName()) }}" alt="{{ $addon->getName() }}"></td>
                <td>
                  <strong>{{ $addon->getName() }}</strong>
                  <br>
                  <br>
                  <span class="pt-2">
                      <a href="{{ route('addon.switch-status', $addon->get('alias')) }}" class="addons-act">{{ $addon->isEnabled() ? __('Deactivate') : __('Activate') }}</a>

                      @if(Config( $addon->getLowerName() . '.options'))
                        @foreach(Config( $addon->getLowerName() . '.options') as $option)
                          | <a href="{{ isset($option['url']) ? $option['url'] : '' }}" class="addons-anchor" target="{{ isset($option['target']) ? $option['target'] : '' }}"> {{ isset($option['label']) ? $option['label'] : '' }}</a>
                        @endforeach
                      @endif

                  </span>
                </td>
                <td>
                  {{ $addon->get('description') }} <br><br> <span class="text-version">{{ __('Version:') }} <b>{{ $addon->get('version', 0) }}</b></span> 
                </td>
              </tr>
            @endforeach
          </tbody>
      </table>
    @endif
  </div>
</div>
<script>
  "use strict";
  var addonsNumber = "{{ $numberOfAddons }}";
</script>
<script src="{{ asset('Modules/Addons/Resources/assets/js/addons.min.js') }}"></script>

