@php
 $extensions = json_encode(getFileExtensions(3));
@endphp
@extends('admin.layouts.master')
@section('title', __('General Settings'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.css')}}">
  <link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
@endsection

@section('page_content')
<div class="row">
    <div class="col-md-3 settings_bar_gap">
        @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
      <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">{{ __('General Settings Form') }}</h3>
          </div>

          <form action="{{ url(config('adminPrefix').'/settings') }}" method="post" class="form-horizontal" enctype="multipart/form-data" id="general_settings_form">
              @csrf
              <div class="box-body">

                {{-- Name --}}
                <div class="form-group row">
                  <label class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
                  <div class="col-sm-6">
                    <input type="text" name="name" id="name" class="form-control f-14" value="{{ $result['name'] }}" placeholder="{{ __('Name') }}">
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                  </div>
                </div>

                <!-- Logo -->
                <div class="form-group row">
                  <label class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end mt-2" for="logo">{{ __('Logo') }}</label>
                  <div class="col-sm-6">
                    <input type="file" name="photos[logo]" id="logo" class="form-control f-14 input-file-field" data-rel="{{ isset($result['logo']) ? $result['logo'] : '' }}" value="{{ old('photos[logo]') }}" placeholder="{{ __('photos[logo]') }}">
                    <span class="text-danger">{{ $errors->first('photos[logo]') }}</span>
                    <div class="clearfix"></div>
                    <small class="form-text text-muted f-12">
                      <strong>{{ allowedImageDimension(288,90) }}</strong>
                    </small>

                    <div class="position-relative d-flex">
                      <img class="d-block mt-1" src='{{ image($result['logo'], 'logo') }}' width="250" height="66" id="{{  isset($result['logo']) ? 'logo-preview' : 'logo-demo-preview'}}">
                      @if (isset($result['logo']))
                      <span class="remove_img_preview_site_logo"></span>
                      @endif
                    </div>
                  </div>
                </div>

                <!-- Favicon -->
                <div class="form-group row">
                  <label class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end mt-2" for="Favicon">{{ __('Favicon') }}</label>
                  <div class="col-sm-6">
                    <input type="file" name="photos[favicon]" id="favicon" class="form-control f-14 input-file-field" data-favicon="{{ isset($result['favicon']) ? $result['favicon'] : '' }}" value="{{ old('photos[favicon]') }}" placeholder="{{ __('photos[favicon]') }}">
                    <span class="text-danger">{{ $errors->first('photos[favicon]') }}</span>
                    <div class="clearfix"></div>
                    <small class="form-text text-muted f-12"><strong>{{ allowedImageDimension(40,40) }}</strong></small>
                    
                    <div class="setting-img d-flex">
                      <img src='{{ image($result['favicon'], 'favicon') }}' width="40" height="40" id="{{ isset($result['favicon']) ? 'favicon-preview' : 'favicon-demo-preview'}}">
                      @if (isset($result['favicon']))
                      <span class="remove_fav_preview"></span>
                      @endif
                    </div>
                  </div>
                </div>

                <!-- Head Code -->
                <div class="form-group row">
                  <label for="head_code" class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end">{{ __('Google Analytics Tracking Code') }}</label>
                  <div class="col-sm-6">
                    <textarea id="head_code" name="head_code" placeholder="{{ __('Google Analytics Tracking Code') }}" rows="3" class="form-control f-14">{{ $result['head_code'] }}</textarea>
                    <span class="text-danger">{{ $errors->first('head_code') }}</span>
                  </div>
                </div>

                <!-- Google reCAPTCHA -->
                <div class="form-group row">
                  <label class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end" for="has_captcha">{{ __('Google reCaptcha') }}</label>
                  <div class="col-sm-6">
                    <select class="form-control f-14 has_captcha select2" name="has_captcha" id="has_captcha">
                        <option value='login' {{ $result['has_captcha'] == 'login' ? 'selected':""}}>{{ __('Login') }}</option>
                        <option value='registration' {{ $result['has_captcha'] == 'registration' ? 'selected':""}}>{{ __('Registration') }}</option>
                        <option value='login_and_registration' {{ $result['has_captcha'] == 'login_and_registration' ? 'selected':""}}>{{ __('Login And Registration') }}</option>
                        <option value='Disabled' {{ $result['has_captcha'] == 'Disabled' ? 'selected':""}}>{{ __('Disabled') }}</option>
                    </select>
                  </div>
                </div>

                <!-- Login Via -->
                <div class="form-group row">
                  <label class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end" for="login_via">{{ __('Login Via') }}</label>
                  <div class="col-sm-6">
                    <select class="form-control f-14 login_via select2" name="login_via" id="login_via">
                        <option value='email_only' {{ $result['login_via'] == 'email_only' ? 'selected':""}}>{{ __('Email Only') }}</option>
                        <option value='phone_only' {{ $result['login_via'] == 'phone_only' ? 'selected':""}}>{{ __('Phone Only') }}</option>
                        <option value='email_or_phone' {{ $result['login_via'] == 'email_or_phone' ? 'selected':""}}>{{ __('Email or Phone') }}</option>
                    </select>
                    <span id="sms-error"></span>
                  </div>
                </div>

                <!-- Default Currency -->
                <div class="form-group row">
                  <label for="default_currency" class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end">{{ __('Default Currency') }}</label>
                  <div class="col-sm-6">
                    <select class="form-control f-14 default_currency select2" name="default_currency" id="default_currency">
                        @foreach ($currency as $key => $value)
                          <option value='{{ $key }}' {{ $result['default_currency'] == $key ? 'selected':""}}> {{ $value }}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <!-- Allowed Wallets -->
                <div class="form-group row">
                  <label for="allowed-wallets" class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end mt-2">{{ __('Allowed Wallets') }}</label>
                  <div class="col-sm-6">
                    <select class="allowed-wallets form-control p-0 f-14 select2" multiple="multiple" name="allowed_wallets[]" id="allowed-wallets">
                      @foreach($allowedWallets as $key => $value)
                          <option value='{{ $key }}'>{{ $value }}</option>
                      @endforeach
                  </select>
                    <small class="form-text text-muted f-12"><strong>*{{ __('This currency wallets will be generated during registration besides default one') }}</strong></small>
                  </div>
                </div>

                <!-- Default Language -->
                <div class="form-group row">
                  <label for="default_language" class="col-sm-4 control-label mt-11 f-14 fw-bold text-sm-end">{{ __('Default Language') }}</label>
                  <div class="col-sm-6">
                    <select class="form-control f-14 default_language select2" name="default_language" id="default_language">
                        @foreach ($language as $key => $value)
                          <option value='{{ $key }}' {{ $result['default_language'] == $key ? 'selected':""}}> {{ $value }}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
              </div>

              @if(Common::has_permission(auth('admin')->user()->id, 'edit_general_setting'))
                  <div class="box-footer">
                    <button type="submit" class="btn btn-theme pull-right f-14" id="general-settings-submit">
                        <i class="fa fa-spinner fa-spin d-none"></i> <span id="general-settings-submit-text">{{ __('Submit') }}</span>
                    </button>
                  </div>
              @endif
          </form>
      </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')
  <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.js')}}" type="text/javascript"></script>
  <script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script>
  @include('common.read-file-on-change')

  <script type="text/javascript">
    "use strict";
    var extensions = JSON.parse(@json($extensions));
    var extensionsValidationRule = extensions.join('|');
    var extensionsValidation = extensions.join(', ');
    var errorMessage = '{{ __("Please select (:x) file.") }}';
    var extensionsValidationMessage = errorMessage.replace(':x', extensionsValidation);
    var submittingText = '{{ __("Submitting...") }}';
    var defaultImageUrl = '{{ url("public/uploads/userPic/default-image.png") }}';
    var selectedAllowedWallets = '{{ $selectedAllowedWallets }}';
  </script>
  <script src="{{ asset('public/admin/customs/js/settings/general/settings.min.js') }}" type="text/javascript"></script>
@endpush
