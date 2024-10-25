@extends('frontend.layouts.app')
@section('content')
<!-- Login page start -->
<div class="container-fluid container-layout px-0">
    <div class="main-auth-div">
        <div class="row">
            <div class="col-md-6 col-xl-5 hide-small-device">
                <div class="bg-pattern">
                     <div class="bg-content">
                        <div class="d-flex justify-content-start"> 
                            <div class="logo-div">
                                <a href="{{ url('/') }}"><img src="{{ image(settings('logo'), 'logo') }}" alt="{{ __('Brand Logo') }}"></a>
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
                        <form action="{{ url('forget-password') }}" method="post" id="forget-password-form">
                            @csrf
                            <div class="auth-module-header">
                                <div class="d-flex align-items-center back-direction forgot-top">
                                    <a href="{{ url('login') }}" class="d-inline-flex align-items-center back-btn">
                                      {!! svgIcons('left_angle') !!}
                                      <span class="ms-1">{{ __('Back') }}</span>
                                    </a>
                                </div>
                                <p class="mb-0 text-start auth-title mt-20">{{ __('Forgot Password?') }}</p>
                                <p class="mb-0 auth-text text-start mt-12 leading-24"> {{ __('Please enter your email address associated with your account to receive a verification code.') }}</p>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    @include('frontend.layouts.common.alert')
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Email Address') }} <span class="star">*</span></label>
                                        <div class="email-box indent">
                                          <input type="email" class="form-control input-form-control" placeholder="{{ __('Enter your email') }}" name="email" id="email" required="" data-value-missing="{{ __('This field is required.') }}"> 
                                          <svg class="email-icon" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.19539 2.75H15.8044C16.5423 2.74999 17.1513 2.74998 17.6474 2.79051C18.1627 2.83261 18.6363 2.92296 19.0812 3.14964C19.7711 3.50118 20.3321 4.06211 20.6836 4.75204C20.9024 5.18135 20.9942 5.63744 21.0381 6.13187C21.0871 6.28165 21.0957 6.43963 21.0666 6.59046C21.0833 7.00307 21.0833 7.48032 21.0833 8.02879V13.9712C21.0833 14.7091 21.0833 15.3181 21.0427 15.8142C21.0006 16.3294 20.9103 16.8031 20.6836 17.248C20.3321 17.9379 19.7711 18.4988 19.0812 18.8504C18.6363 19.077 18.1627 19.1674 17.6474 19.2095C17.1513 19.25 16.5423 19.25 15.8045 19.25H6.19537C5.45749 19.25 4.8485 19.25 4.35241 19.2095C3.83715 19.1674 3.36351 19.077 2.91862 18.8504C2.22869 18.4988 1.66776 17.9379 1.31623 17.248C1.08954 16.8031 0.999191 16.3294 0.957093 15.8142C0.916561 15.3181 0.91657 14.7091 0.916582 13.9712V8.02881C0.916573 7.48034 0.916566 7.00308 0.933208 6.59046C0.904098 6.43963 0.91269 6.28163 0.961697 6.13185C1.00569 5.63744 1.09748 5.18135 1.31623 4.75204C1.66776 4.06211 2.22869 3.50118 2.91862 3.14964C3.36351 2.92296 3.83715 2.83261 4.35241 2.79051C4.8485 2.74998 5.4575 2.74999 6.19539 2.75ZM2.74992 8.17727V13.9333C2.74992 14.7185 2.75063 15.2523 2.78434 15.6649C2.81717 16.0668 2.87669 16.2723 2.94974 16.4157C3.1255 16.7606 3.40597 17.0411 3.75093 17.2168C3.8943 17.2899 4.09982 17.3494 4.5017 17.3822C4.91428 17.416 5.44805 17.4167 6.23325 17.4167H15.7666C16.5518 17.4167 17.0855 17.416 17.4981 17.3822C17.9 17.3494 18.1055 17.2899 18.2489 17.2168C18.5939 17.0411 18.8743 16.7606 19.0501 16.4157C19.1231 16.2723 19.1827 16.0668 19.2155 15.6649C19.2492 15.2523 19.2499 14.7185 19.2499 13.9333V8.17727L13.2077 12.4068C13.1722 12.4317 13.137 12.4564 13.102 12.4809C12.6027 12.8315 12.1644 13.1394 11.6651 13.2638C11.2283 13.3727 10.7715 13.3727 10.3348 13.2638C9.83548 13.1394 9.39709 12.8315 8.89782 12.481C8.86289 12.4564 8.82766 12.4317 8.79208 12.4068L2.74992 8.17727ZM19.174 5.99251L12.1564 10.9049C11.4882 11.3726 11.3466 11.4538 11.2216 11.4849C11.076 11.5212 10.9238 11.5212 10.7782 11.4849C10.6532 11.4538 10.5116 11.3726 9.84343 10.9049L2.82579 5.99251C2.85853 5.80261 2.90114 5.67973 2.94974 5.58435C3.1255 5.23939 3.40597 4.95892 3.75093 4.78316C3.8943 4.71011 4.09982 4.65059 4.5017 4.61776C4.91428 4.58405 5.44805 4.58333 6.23325 4.58333H15.7666C16.5518 4.58333 17.0855 4.58405 17.4981 4.61776C17.9 4.65059 18.1055 4.71011 18.2489 4.78316C18.5939 4.95892 18.8743 5.23939 19.0501 5.58435C19.0987 5.67973 19.1413 5.80261 19.174 5.99251Z" fill="#9998A0"/>
                                          </svg>
                                        </div>
                                    </div> 
                                    <div class="d-grid mb-3 mb-3p">
                                        <button class="btn btn-lg btn-primary" type="submit" id="forget-password-submit-btn">
                                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                                                <span class="visually-hidden"></span>
                                            </div>

                                            <span class="px-1" id="forget-password-submit-btn-text">{{ __('Send') }}</span>
                                            {!! svgIcons('right_angle') !!}  
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
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
    <script>
        'use strict';
        let submitBtnText = "{{ __('Sending...') }}"
    </script>

    <script src="{{ asset('public/frontend/customs/js/forgot-password/forgot-password.min.js') }}" type="text/javascript"></script>
@endsection
