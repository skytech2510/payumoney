@extends('frontend.layouts.app')

@section('content')
    <!-- Login page start -->
    <div class="container-fluid container-layout px-0" id="register-form">
        <div class="main-auth-div">
            <div class="row">
                <div class="col-md-6 col-xl-5 hide-small-device">
                    <div class="bg-pattern">
                        <div class="bg-content">
                            <div class="d-flex justify-content-start"> 
                                <div class="logo-div">
                                    <a href="{{ url('/') }}">
                                        <img src="{{ image(settings('logo'), 'logo') }}" alt="{{ __('Brand Logo') }}">
                                    </a>
                                </div>
                            </div> 
                            <div class="transaction-block">
                                <div class="transaction-text">
                                    <h3 class="mb-6p">{{ __('Hassle free money') }}</h3>
                                    <h1 class="mb-2p">{{ __('Transactions') }}</h1>
                                    <h2>{{ __('Right at you fingertips') }}</h2>
                                </div>
                            </div>
                            <div class="transaction-image">
                                <div class="static-image">
                                    <img class="img img-fluid" src="{{ asset('public/frontend/templates/images/login/signup-static-img.svg') }}">
                                </div>
                            </div>
                        </div>
                    </div>                  
                </div>
                <div class="col-md-6 col-12 col-xl-7">
                    <div class="auth-section d-flex align-items-center">
                          <div class="auth-module">
                            <form action="{{ url('register/store') }}" class="form-horizontal" id="register-form" method="POST">
                                @csrf
                                <input type="hidden" name="has_captcha" value="{{ isset($enabledCaptcha) && ($enabledCaptcha == 'registration' || $enabledCaptcha == 'login_and_registration') ? 'registration' : 'Disabled' }}">
                                <input type="hidden" name="defaultCountry" id="defaultCountry" class="form-control" value="{{ $formInfo['defaultCountry'] }}">
                                <input type="hidden" name="carrierCode" id="carrierCode" class="form-control" value="{{ $formInfo['carrierCode'] }}">
                                <input type="hidden" name="formattedPhone" id="formattedPhone" class="form-control" value="{{ $formInfo['formattedPhone'] }}">
                                <input type="hidden" name="first_name" id="first_name" class="form-control" value="{{ $formInfo['first_name'] }}">
                                <input type="hidden" name="last_name" id="last_name" class="form-control" value="{{ $formInfo['last_name'] }}">
                                <input type="hidden" name="email" id="email" class="form-control" value="{{ $formInfo['email'] }}">
                                <input type="hidden" name="phone" id="phone" class="form-control" value="{{ $formInfo['phone'] }}">
                                <input type="hidden" name="password" id="password" class="form-control" value="{{ $formInfo['password'] }}">
                                <input type="hidden" name="password_confirmation" id="password_confirmation" class="form-control" value="{{ $formInfo['password_confirmation'] }}">

                                <div class="auth-module-header">
                                    <div class="d-flex align-items-center back-direction usertype-top">
                                        <a href="{{ url('register') }}" class="d-inline-flex align-items-center back-btn">
                                          {!! svgIcons('left_angle') !!}
                                          <span class="ms-1">{{ __('Back') }}</span>
                                        </a>
                                    </div>
                                    <p class="mb-0 text-start auth-title mt-20">{{ __('You are almost there...') }}</p>
                                    <p class="mb-0 auth-text text-start mt-12 leading-24"> {{ __('Select user account for personal use and merchant account for business payments.') }}</p>
                                </div>
                               
                                <div class="row">
                                    <div class="col-md-12">
                                        <p class="mb-0 type-text mt-3p">{{ __('Select Account Type') }}<span class="star">*</span></p>
                                        <div class="d-flex gap-16 mt-2 radio-hide">
                                            @if (!empty($checkUserRole))
                                            <input type="radio" name="type" id="user" value="user" required="" checked oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                            <label for="user" class="usertype-module usertype-photo d-inline-flex flex-column justify-content-center align-items-center user-selected">
                                                <img src="{{ asset('public/frontend/templates/images/login/woman-user.png') }}" alt="user">
                                                <p class="usertype mt-22">{{ __('User') }}</p>
                                            </label>
                                            @endif
                                            @if (!empty($checkMerchantRole))
                                            <input type="radio" name="type" id="merchant" value="merchant" required="" oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                            <label for="merchant" class="usertype-module usertype-photo d-inline-flex flex-column justify-content-center align-items-center">
                                                <img src="{{ asset('public/frontend/templates/images/login/men-merchant.png') }}" alt="merchant">
                                                <p class="usertype mt-22">{{ __('Merchant') }}</p>
                                            </label>
                                            @endif
                                        </div>
                                        <!-- reCaptcha -->
                                        @if (isset($enabledCaptcha) && ($enabledCaptcha == 'registration' || $enabledCaptcha == 'login_and_registration'))

                                            <div class="col-md-12 mt-3">
                                                {!! app('captcha')->display() !!}
                                                @error ('g-recaptcha-response')
                                                    <span class="error">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    <br>
                                                @enderror
                                                <br>
                                            </div>
                                        @endif
                                        <div class="policy d-flex justify-content-center mt-28">
                                            <p class="mb-0">{{ __('By signing up, you agree to our Terms, Data Policy and Cookie Policy.') }}</p>
                                        </div>
                                        <div class="d-grid sm-top mt-28">
                                            <button class="btn btn-lg btn-primary" type="submit" id="registrationSubmitBtn">
                                                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                                                    <span class="visually-hidden"></span>
                                                </div>
                                                <span class="px-1" id="registrationSubmitBtnTxt">{{ __('Create Account') }}</span>
                                                <span id="rightAngle">{!! svgIcons('right_angle_md') !!}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                          </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Login page end -->
@endsection

@section('js')
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
    <script>
        'use strict';
        let signingUpText = '{{ __("Signing Up...") }}';
    </script>

    <script src="{{ asset('public/frontend/customs/js/register/user-type.min.js') }}" type="text/javascript"></script>
@endsection