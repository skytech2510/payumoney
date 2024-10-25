@extends('frontend.layouts.app')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.css') }}">
@endsection
@section('content')
<div class="container-fluid container-layout px-0 main-containt">
    <div class="main-auth-div" id="google2faVerifySection">
        <div class="row">
            <div class="col-md-5 col-xl-5 hide-small-device">
                <div class="bg-pattern">
                    <div class="bg-content">
                        <div class="d-flex justify-content-start">
                            <div class="logo-div">
                                <a href="{{ url('/') }}">
                                    <img src="{{ image(settings('logo'), 'logo') }}" alt="{{ __('Logo') }}">
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
                                <img class="img img-fluid" src="{{ asset('public/frontend/templates/images/2fa/2fa-img.svg') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- google2fa-->
            <div class="col-md-7 col-12 col-xl-7" id="google2faVerify">
                <div class="auth-section d-flex align-items-center">
                    <div class="auth-module">
                        <div class="auth-module-header">
                            <div class="d-flex align-items-center back-direction authenticator-top">
                                <a href="javascript:void(0)" class="d-inline-flex align-items-center back-btn">
                                    <svg class="position-relative" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.47075 10.4709C8.7311 10.2105 8.7311 9.78842 8.47075 9.52807L4.94216 5.99947L8.47075 2.47087C8.7311 2.21053 8.7311 1.78842 8.47075 1.52807C8.2104 1.26772 7.78829 1.26772 7.52794 1.52807L3.52795 5.52807C3.2676 5.78842 3.2676 6.21053 3.52795 6.47088L7.52794 10.4709C7.78829 10.7312 8.2104 10.7312 8.47075 10.4709Z" fill="currentColor"></path>
                                    </svg>
                                    <span class="ms-1">{{ __('Back') }}</span>
                                </a>
                            </div>
                            <p class="mb-0 text-start auth-title mt-20">{{ __('Google Two Factor Authentication (2FA)') }}</p>
                            <p class="mb-0 auth-text text-start mt-12 leading-24 pe-3">{{ __('Please use your Google Authenticator App Download from') }} <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en&gl=US" class="d-link" target="_blank">{{ __('Google Play') }}</a> {{ __('or') }} <a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank">{{ __('App Store') }}</a> {{ __(' to scan this QR code.') }}
                            </p>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="img-qr mb-3">
                                    <img src="{{ session('data')['QR_Image'] }}" alt="QrCode" class="img-fluid">
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-lg btn-primary qr-submit mt-2 completeVerification" id="completeVerificationBtn">
                                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                            <span class="visually-hidden"></span>
                                        </div>
                                        <span class="px-1 text-uppercase" id="completeVerification_text">{{ __('Continue To Verification') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--section_2fa_otp-->
            <div class="col-md-7 col-12 col-xl-7 d-none" id="section_2fa_otp">
                <div class="auth-section d-flex align-items-center">
                    <div class="auth-module">
                        <div class="auth-module-header">
                            <div class="d-flex align-items-center back-direction confirm-top">
                                <a href="{{ url('/') }}" class="d-inline-flex align-items-center back-btn">
                                    <svg class="position-relative" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.47075 10.4709C8.7311 10.2105 8.7311 9.78842 8.47075 9.52807L4.94216 5.99947L8.47075 2.47087C8.7311 2.21053 8.7311 1.78842 8.47075 1.52807C8.2104 1.26772 7.78829 1.26772 7.52794 1.52807L3.52795 5.52807C3.2676 5.78842 3.2676 6.21053 3.52795 6.47088L7.52794 10.4709C7.78829 10.7312 8.2104 10.7312 8.47075 10.4709Z" fill="currentColor"></path>
                                    </svg>
                                    <span class="ms-1">{{ __('Back') }}</span>
                                </a>
                            </div>
                            <p class="mb-0 text-start auth-title mt-20">{{ __('Enter confirmation code') }}</p>
                            <p class="mb-0 auth-text text-start mt-12 leading-24">{{ __('Please enter the confirmation code you see on your authentication app.') }}</p>
                        </div>
                        <form method="post" id="otp_form" class="mt-4">
                            @csrf
                            <input type="hidden" name="two_step_verification_type" id="two_step_verification_type" value="{{ auth()->user()->user_detail->two_step_verification_type }}">
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label" for="one_time_password">{{ __('Verification code') }}</label>
                                        <input id="one_time_password" type="number" maxlength="6" class="form-control input-form-control" name="one_time_password" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required autofocus placeholder="{{ __('Enter the 6-digit OTP') }}">
                                        @error('one_time_password')
                                            <div class="error">{{ $message }}</div>
                                        @enderror
                                        <span class="error" id="message"></span>
                                    </div>
                                    @if (empty($checkDeviceLog))
                                        <div class="form-check custom-form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                            <label class="form-check-label" for="remember_me">{{ __('Remember me on this browser') }}</label>
                                        </div>
                                    @endif
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-lg btn-primary mt-2" id="verify_otp">
                                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                                <span class="visually-hidden"></span>
                                            </div>
                                            <span class="px-1 text-uppercase" id="verify_otp_text">{{ __('Verify & proceed') }}</span>
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
@endsection
@section('js')
    <script src="{{ asset('public/dist/libraries/fingerprintjs2/fingerprintjs2.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.js') }}" type="text/javascript"></script>

    <script>
        'use strict';
        var token = $('[name="_token"]').val();
        var google2fa_secret = "{{ session('data')['secret'] }}";
        var twofaVerifyGoogleAuthenticatorUrl = "{{ route('2fa-verify.google_authenticator') }}";
        var twofaVerifyGoogleOtpUrl = "{{ route('2fa-verify.google_otp') }}";
        var dashboardUrl = "{{ route('user.dashboard') }}";
        var submitBtnText = "{{ __('Submitting...') }}";
        var errorText = "{{ __('Error') }}!";
        var invalidCodeText = "{{ __('The verification code is invalid.') }}";
    </script>
    <script src="{{ asset('public/user/customs/js/settings.min.js') }}"></script>
@endsection
