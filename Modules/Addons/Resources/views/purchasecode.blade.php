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
        @php
        $module = explode('_', base64_decode($name));
        @endphp
        <h5>{{ ucfirst(strtolower($module[0])) }} {{ __('verification') }}</h5>
    </div>
    <hr>

    <div>
        <form action="{{ route('addon.verify', $module[0]) }}" method="post" class="addons-form">
            @csrf
            <div>
                <!-- Purchase Code -->
                <div>
                    <p>{{ __('Purchase Code') }}</p>
                    <input type="text" value="{{ old('purchase_code') }}" placeholder="{{ __('Purchase Code') }}" name="purchase_code" required>
                </div>
                <!-- Envato Username -->
                <div>
                    <p>{{ __('Envato Username') }}</p>
                    <input type="text" value="{{ old('username') }}" placeholder="{{ __('Envato Username') }}" name="username" required>
                </div>
            </div>
            <div class="input-file-container-purchase">
                <button class="submit-style verify-btn" type="submit">{{ __('Verify') }}</button>
            </div>
        </form>
        <hr>
    </div>
  
</div>

<script>
  "use strict";
  var addonsNumber = "{{ $numberOfAddons }}";
</script>
<script src="{{ asset('Modules/Addons/Resources/assets/js/addons.min.js') }}"></script>