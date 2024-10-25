@extends('admin.layouts.master')

@section('title', __('Edit Profile'))

@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/css/intlTelInput.min.css') }}">
@endsection

@section('page_content')
<div id="user-edit">
    <div class="box">
        <div class="panel-body ml-20">
            <ul class="nav nav-tabs cus f-14" role="tablist">
                @include('admin.users.user_tabs')
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="row align-items-center">
        <div class="col-md-4">
            <div class="d-flex align-items-center">
                <h3 class="f-24 mb-0">{{ getColumnValue($users) }}</h3>
                <p class="badge bg-{{ $users->status == 'Active' ? 'success' : ($users->status == 'Inactive' ? 'danger' : 'warning') }} mb-0 ms-1">{{ $users->status == 'Active' ? __('Active') : ($users->status == 'Inactive' ? __('Inactive') : __('Suspended')) }}</p>
            </div>
        </div>
        <div class="col-md-3"></div>

        <div class="col-md-5">
            <div class="pull-right">
                <a href="{{ url(config('adminPrefix').'/users/deposit/create/'.$users->id) }}" class="btn btn-theme f-14">{{ __('Deposit') }}</a>
                <a href="{{ url(config('adminPrefix').'/users/withdraw/create/'.$users->id) }}" class="btn btn-theme f-14 ms-1">{{ __('Withdraw') }}</a>
            </div>
        </div>
    </div>


    <div class="box mt-20">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <!-- form start -->
                    <form action="{{ url(config('adminPrefix').'/users/update') }}" class="form-horizontal" id="user_form" method="POST">
                        {{ csrf_field() }}

                        <input type="hidden" value="{{ $users->id }}" name="id" id="id" />
                        <input type="hidden" value="{{ $users->defaultCountry }}" name="user_defaultCountry" id="user_defaultCountry" />
                        <input type="hidden" value="{{ $users->carrierCode }}" name="user_carrierCode" id="user_carrierCode" />
                        <input type="hidden" value="{{ $users->formattedPhone }}" name="formattedPhone" id="formattedPhone">

                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">

                                     <!-- FirstName -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="first_name">{{ __('First Name') }}</label>
                                        <div class="col-sm-6">
                                            <input name="first_name" value="{{ $users->first_name }}" type="text" id="first_name" class="form-control f-14" placeholder="{{ __('Enter :x', ['x' => __('first name')]) }}" data-value-missing="{{ __('This field is required.') }}" maxlength="30" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('First name'), 'y' => __('30')]) }}">
                                            @if($errors->has('first_name'))
                                                <span class="error">
                                                    {{ $errors->first('first_name') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                     <!-- LastName -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="last_name">{{ __('Last Name') }}</label>
                                        <div class="col-sm-6">
                                            <input name="last_name" value="{{ $users->last_name }}" type="text" id="last_name"  class="form-control f-14" placeholder="{{ __('Enter :x', ['x' => __('last name')]) }}" required data-value-missing="{{ __('This field is required.') }}" maxlength="30" data-max-length="{{ __(':x length should be maximum :y charcters.', ['x' => __('Last name'), 'y' => __('30')]) }}">
                                            @if($errors->has('last_name'))
                                                <span class="error">
                                                    {{ $errors->first('last_name') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                     <!-- Phone -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="phone">{{ __('Phone') }}</label>
                                        <div class="col-sm-6">
                                            <input type="tel" class="form-control f-14" id="phone" name="phone">
                                            <span id="duplicate-phone-error"></span>
                                            <span id="tel-error"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14 require" for="email">{{ __('Email') }}</label>
                                        <div class="col-sm-6">
                                            <input name="email" value="{{ $users->email }}" type="email" id="email" class="form-control f-14" placeholder="{{ __('Enter a valid :x.', ['x' => __('email')] )}}" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')" data-type-mismatch="{{ __('Enter a valid :x.', [ 'x' => strtolower(__('email'))]) }}">
                                            @if($errors->has('email'))
                                                <span class="error">{{ $errors->first('email') }}</span>
                                            @endif
                                            <span id="emailError"></span>
                                            <span id="email-ok" class="text-success"></span>
                                        </div>
                                    </div>

                                    <!-- Role -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14 require" for="role">{{ __('Group') }}</label>
                                        <div class="col-sm-6">
                                            <select class="select2" name="role" id="role" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                                @foreach ($roles as $role)
                                                <option value='{{ $role->id }}' {{ $role->id == $users->role_id ? 'selected' : "" }}> {{ $role->display_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="password">
                                            {{ __('Password') }}
                                        </label>
                                        <div class="col-sm-6">
                                            <input name="password" type="password" id="password" class="form-control f-14" placeholder="{{ __('Enter new Password') }}" >
                                            @if($errors->has('password'))
                                                <span class="error">{{ $errors->first('password') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Password Confirmation -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="password_confirmation">
                                            {{ __('Confirm Password') }}
                                        </label>
                                        <div class="col-sm-6">
                                            <input name="password_confirmation" type="password" id="password_confirmation" class="form-control f-14" placeholder="{{ __('Confirm password') }}">
                                            @if($errors->has('password_confirmation'))
                                                <span class="error">
                                                    {{ $errors->first('password_confirmation') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="row form-group">
                                        <label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14 require" for="status">{{ __('Status') }}</label>
                                        <div class="col-sm-6">
                                            <select class="select2" name="status" id="status" required oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                                <option value='Active' {{ $users->status == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                                <option value='Inactive' {{ $users->status == 'Inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                                <option value='Suspended' {{ $users->status == 'Suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                                            </select>
                                            <label id="user-status" class="error" for="status"></label>
                                        </div>
                                    </div>

                                    <div class="row form-group align-items-center">
                                        <div class="col-sm-6 offset-md-3">
                                            <a class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/users') }}" id="users_cancel">{{ __('Cancel') }}</a>
                                            <button type="submit" class="btn btn-theme f-14" id="users_edit">
                                                <i class="fa fa-spinner fa-spin f-14 d-none"></i> <span id="users_edit_text">{{ __('Update') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/intl-tel-input-17.0.19/js/intlTelInput-jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/js/isValidPhoneNumber.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    'use strict';
    var userNameError = '{{ __("Please enter only alphabet and spaces.") }}';
    var userNameLengthError = '{{ __("Name length can not be more than 30 characters") }}';
    var utilsScriptLoadingPath = '{{ asset("public/dist/plugins/intl-tel-input-17.0.19/js/utils.min.js") }}';
    var formattedPhoneNumber = '{{ !empty($users->formattedPhone) ? $users->formattedPhone : NULL }}';
    var carrierCode = '{{ !empty($users->carrierCode) ? $users->carrierCode : NULL }}';
    var defaultCountry = '{{ !empty($users->defaultCountry) ? $users->defaultCountry : NULL }}';
    var validPhoneNumberErrorText = '{{ __("Please enter a valid international phone number.") }}';
    var inactiveWarning = '{!! __("Warning! User would not be able to login.") !!}';
    var suspendWarning = '{!! __("Warning! User would not be able to do any transaction.") !!}';
    var passwordMatchErrorText = '{{ __("Please enter same value as the password field.") }}';
    var updatingText = '{{ __("Updating...") }}';
</script>
<script src="{{ asset('public/admin/customs/js/user/user.min.js') }}" type="text/javascript"></script>
@endpush
