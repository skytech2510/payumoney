@extends('user.layouts.app')

@push('css')
    <!-- sweetalert -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/dist/libraries/sweetalert/sweetalert.min.css')}}">
@endpush

@section('content')
<div class="bg-white pxy-62 exchange pt-62 shadow" id="twofaVerification">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Settings') }}</p>
    <div class="row">
        <div class="col-12">
            <nav>
                <div class="nav-tab-parent d-flex justify-content-center mt-4">
                    <div class="d-flex p-2 border-1p rounded-pill gap-1 bg-white nav-tab-child">
                        <a href="{{ route('user.setting.identitiy_verify') }}" class="tablink-edit text-gray-100">{{ __('Identity Verification') }}</a>
                        <a href="{{ route('user.setting.address_verify') }}" class="tablink-edit text-gray-100">{{ __('Address Verfication') }}</a>
                        @if ($two_step_verification != 'disabled')
                            <a href="{{ route('user.setting.twoFa') }}" class="tablink-edit text-gray-100 tabactive">{{ __('TwoFa') }}</a>
                        @endif
                    </div>
                </div>
            </nav>
            <!-- 1st step start-->
            <div class="mt-32 responsive-size" id="section_2fa_form">
                <p class="mb-2 f-20 gilroy-Semibold">{{ __('2-factor Authentication') }}</p>
                <form method="post" class="form-horizontal mt-2" id="2fa_update">
                    @csrf
                    <input type="hidden" value="{{ $user->id }}" name="id" id="id" />
                    <input type="hidden" name="gotResonponseFromSubmit" id="gotResonponseFromSubmit" />
                    <input type="hidden" name="is_demo" id="is_demo" value="{{ $is_demo }}" />

                    <div class="mt-28 param-ref">
                        <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Email') }}</label>
                        <div class="avoid-blink">
                            <select class="select2" data-minimum-results-for-search="Infinity" name="two_step_verification_type" id="two_step_verification_type">
                                <option value='disabled' {{ $user->user_detail->two_step_verification_type == 'disabled' ? 'selected':"" }}>{{ __('Disabled') }}</option>
                                @if ($two_step_verification == 'by_email')
                                    <option value='email' {{ $user->user_detail->two_step_verification_type == 'email' ? 'selected':"" }}>{{ __('By email') }}</option>
                                @elseif ($two_step_verification == 'by_phone')
                                    <option value='phone' {{ $user->user_detail->two_step_verification_type == 'phone' ? 'selected':"" }}>{{ __('By phone') }}</option>
                                @elseif ($two_step_verification == 'by_google_authenticator')
                                    <option value='google_authenticator' {{ $user->user_detail->two_step_verification_type == 'google_authenticator' ? 'selected':"" }}>{{ __('By Google Authenticator') }}</option>
                                @elseif ($two_step_verification == 'by_email_phone')
                                    <option value='email' {{ $user->user_detail->two_step_verification_type == 'email' ? 'selected':"" }}>{{ __('By email') }}</option>
                                    <option value='phone' {{ $user->user_detail->two_step_verification_type == 'phone' ? 'selected':"" }}>{{ __('By phone') }}</option>
                                @endif
                            </select>
                            <span class="custom-error" id="2fa-error"></span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary px-4 py-2 mt-3" id="2fa_submit">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="2fa_submit_text">{{ __('Submit') }}</span>
                        </button>
                    </div>
                </form>
            </div>
            <!-- 1st step end-->

            <!-- 2nd-step start-->
            <div class="mt-32 responsive-size d-none" id="section_2fa_verify">
                <form class="form-horizontal" method="POST" id="2fa_verify_form"><!--submitting via ajax-->
                    @csrf
                    <input type="hidden" name="twoFaVerificationType" id="twoFaVerificationType"/>
                
                    <p class="mb-0 f-16 leading-25 gilroy-medium text-warning ">{{ __('A text message with a 6-digit verification code was just sent to ') }}<span id="type"></span></p>

                    <div class="label-top mt-4">
                        <input id="two_step_verification_code" class="form-control input-form-control apply-bg focus-bgcolor" placeholder="Enter the 6-digit code" name="two_step_verification_code" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type = "number" maxlength = "6" required autofocus/>
                        @error('two_step_verification_code')
                            <div class="custom-error">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (empty($checkDeviceLog))
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" value="" name="remember_me" id="remember_me">
                            <label class="form-check-label" for="remember_me">{{ __('Remember me on this browser') }}</label>
                        </div>
                    @endif

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary px-4 py-2 mt-4 verify_code" id="verify_code">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="verify_code_text">{{ __('Verify') }}</span>
                        </button>
                    </div>
                </form>
            </div>
            <!-- 2nd-step end-->

            <!-- 3rd section start -->
            <div class="qr-section mt-32 border-1p p-5 responsive-size d-none" id="section_google2fa">
                <div class="d-flex justify-content-center">
                    <div class="text-center">
                        <p class="mb-2 f-20 gilroy-Semibold w-270">{{ __('Scan QR Code with Google Authenticator App.') }}</p>
                        <span id="qrsecret"></span>
                        <div class="text-center mt-3">
                            <img id="qr_image" class="img-fluid img-responsive">
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="mb-0 f-14 gilroy-regular text-dark mt-4">{{ __('Set up your Google Authenticator app before continuing.') }}</p>
                    <p class="mb-0 f-14 gilroy-regular text-dark mt-2">{{ __('You will be unable to verify otherwise.') }}</p>
                </div>

                <div class="d-grid">
                    <button class="btn btn-primary px-4 py-2 mt-4 completeVerification" id="completeVerificationBtn">
                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                            <span class="visually-hidden"></span>
                        </div>
                        <span id="completeVerification_text">{{ __('Proceed To Verification') }}</span>
                    </button>
                </div>
            </div>
            <!-- 3rd section start -->

            <!-- 4th section start -->
            <div class="mt-32 responsive-size d-none" id="section_2fa_otp">
                <form class="form-horizontal" method="post" id="otp_form">
                    @csrf

                    <p class="mb-0 f-20 gilroy-Semibold text-dark text-center">{{ __('Enter the 6-digit OTP from Google Authenticator App') }}</p>

                    <div class="label-top mt-4">
                        <input id="one_time_password" type="number" maxlength="6" class="form-control input-form-control apply-bg focus-bgcolor" name="one_time_password" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required autofocus>
                        @error('one_time_password')
                            <div class="custom-error">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (empty($checkDeviceLog))
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="remember_otp" id="remember_otp">
                            <label class="form-check-label" for="remember_otp">{{ __('Remember me on this browser') }}</label>
                        </div>
                    @endif

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary px-4 py-2 mt-4" id="verify_otp">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="verify_otp_text">{{ __('Verify') }}</span>
                        </button>
                    </div>
                </form>
            </div>
            <!-- 4th section start -->
        </div>
    </div>
</div>
@endsection


@push('js')
<script src="{{ asset('public/dist/libraries/fingerprintjs2/fingerprintjs2.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.js') }}" type="text/javascript"></script>

<script>
    'use strict';
    var token = $('[name="_token"]').val();
    var twofaVerifyPhoneUrl = "{{ route('user.setting.2fa-verify.phone') }}";
    var twofaVerifyGoogleUrl = "{{ route('user.setting.2fa-verify.google') }}";
    var twofaVerifyDisabledUrl = "{{ route('user.setting.2fa-verify.disabled') }}";
    var twofaVerifyCreateUrl = "{{ route('user.setting.2fa-verify.create') }}";
    var twofaVerifySettingsUrl = "{{ route('user.setting.2fa-verify.settings') }}";
    var twofaVerifyGoogleOtpUrl = "{{ route('user.setting.2fa-verify.google_otp') }}";
    var twofaVerifyCompleteGoogleUrl = "{{ route('user.setting.2fa-verify.google_complete') }}";

    var submitBtnText = "{{ __('Submitting...') }}";
    var errorText = "{{ __('Error') }}!";
    var successText = "{{ __('Success') }}!";
    var demoCheckText = "{{ __('2fa is disabled in demo application') }}";
    var successMessageText = "{{ __('2fa Setting updated successfully.') }}";
    var oneTimeMessageText = "{{ __('One time password is incorrect!') }}";
</script>
<script src="{{ asset('public/user/customs/js/settings.min.js') }}"></script>
@endpush
