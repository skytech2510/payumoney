@extends('frontend.layouts.app')
@section('styles')
@endsection
@section('content')
    <div class="container-fluid container-layout px-0 main-containt">
        <div class="main-auth-div" id="2faVerification">
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
                <div class="col-md-7 col-12 col-xl-7">
                    <div class="auth-section d-flex align-items-center">
                        <div class="auth-module">
                            <div class="auth-module-header">
                                <div class="d-flex align-items-center back-direction otp-top">
                                    <a href="{{ url('/') }}" class="d-inline-flex align-items-center back-btn">
                                        <svg class="position-relative" width="12" height="12" viewBox="0 0 12 12"
                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.47075 10.4709C8.7311 10.2105 8.7311 9.78842 8.47075 9.52807L4.94216 5.99947L8.47075 2.47087C8.7311 2.21053 8.7311 1.78842 8.47075 1.52807C8.2104 1.26772 7.78829 1.26772 7.52794 1.52807L3.52795 5.52807C3.2676 5.78842 3.2676 6.21053 3.52795 6.47088L7.52794 10.4709C7.78829 10.7312 8.2104 10.7312 8.47075 10.4709Z" fill="currentColor"></path>
                                        </svg>
                                        <span class="ms-1">{{ __('Back') }}</span>
                                    </a>
                                </div>
                                <p class="mb-0 text-start auth-title mt-20">{{ __('Two Factor Authentication (2FA) via :x', ['x' => auth()->user()->user_detail->two_step_verification_type]) }} </p>
                                <p class="mb-0 auth-text text-start mt-12 leading-24 pe-3">{{ __('A text message with a 6-digit
                                    verification code was just sent to') }} 
                                    <span class="text-primary">
                                        @if (auth()->user()->user_detail->two_step_verification_type == 'phone')
                                            {{ str_pad(substr(auth()->user()->phone, -2), strlen(auth()->user()->phone), '*', STR_PAD_LEFT) }}
                                        @elseif (auth()->user()->user_detail->two_step_verification_type == 'email')
                                            {{ auth()->user()->email }}
                                        @endif
                                    </span>
                                </p>
                                <p class="mb-0 auth-text text-danger mt-12 leading-24 pe-3"><span id="message"></span></p>
                            </div>
                            <form method="post" id="2faVerificationForm" class="mt-4">
                                @csrf
                                <input type="hidden" name="fingerprint" id="fingerprint">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label verification"><span>{{ __('Verification code') }}</span>
                                                <a href="#" class="btn resend-code">
                                                    <img src="{{ asset('public/frontend/templates/images/2fa/refresh.svg') }}" alt="refresh" class="img-fluid ref-light">
                                                    <img src="{{ asset('public/frontend/templates/images/2fa/refresh-dark.svg') }}" alt="refresh" class="img-fluid ref-dark d-none">
                                                    <span class="px-1">{{ __('Resend code') }}</span>
                                                </a>
                                            </label>
                                            <input type="number" class="form-control input-form-control" maxlength="6" id="two_step_verification_code" placeholder="Enter the 6-digit code" name="two_step_verification_code" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required autofocus/>
                                            @error('two_step_verification_code')
                                                <div class="custom-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @if (empty($checkDeviceLog))
                                            <div class="form-check custom-form-check mb-3">
                                                <input class="form-check-input" type="checkbox" value="" id="remember_me" name="remember_me">
                                                <label class="form-check-label" for="remember_me">{{ __('Remember me on this browser') }}</label>
                                            </div>
                                        @endif

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-lg btn-primary mt-2 2faVerifyCode" id="2faVerifyCode">
                                                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                                    <span class="visually-hidden"></span>
                                                </div>
                                                <span id="2faVerifyCode_text">{{ __('Verify') }}</span>
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
    <script src="{{ asset('public/dist/libraries/fingerprintjs2/fingerprintjs2.min.js') }}" type="text/javascript">
    </script>
    <script>
        'use strict';
        var token = $('[name="_token"]').val();
        var twofaVerifyUrl = "{{ route('2fa-verify.store') }}";
        var dashboardUrl = "{{ route('user.dashboard') }}";
        var submitBtnText = "{{ __('Submitting...') }}";
        var btnText = "{{ __('Verify') }}";
    </script>
    <script src="{{ asset('public/user/customs/js/settings.min.js') }}"></script>
@endsection
