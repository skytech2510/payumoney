<link rel="stylesheet" type="text/css" href="{{ asset('Modules\Addons\Resources\assets\css\addon.css') }}">
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

<div style="padding-bottom: 40px" class="addons-section">
  <div class="addons-card">
      <h5>{{ __('Addons') }}</h5>
    </div>
    <hr>

  <div id="addons-form-container" style="display: {{ ($numberOfAddons > 0) ? 'block' : 'none' }}">
    <div class="addons-form">
     
      <div>
        <div>
          <p>{!! __('Please verify :a purchase code and username', ['a' =>   '<span style="color: #04a9f5">' . $name .'</span>']) !!}</p>
        </div>
      </div>
    </div>
    
  </div>
  <hr>
  <div style="position: absolute; right:50px; bottom:15px;" class="input-file-container"><a style="
  background-color: #04a9f5;
    color: white;
    font-size: 16px;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;" class="submit-style" href="{{ route('addon.verify', base64_encode($name)) }}">{{ __('Verify Puchase Code') }}</a></div>

</div>
<script src="{{ asset('Modules/Addons/Resources/assets/js/addons.min.js') }}"></script>