@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="depositConfirm">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Deposit Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm Your Deposit') }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Check your deposit information before confirmation.') }}</p>

    <form method="post" action="{{ route('user.deposit.gateway') }}" id="depositConfirmForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="method" id="method" value="{{ $payment_method }}" >
        <input type="hidden" name="amount" id="amount" value="{{ $totalAmount }}" >

        <!-- Confirm Details -->
        <div class="mt-32 param-ref text-center">
            <p class="mb-0 gilroy-medium text-gray-100 sm-font-14">{{ __('Through') }}</p>

            <!-- Logo Image -->
            <img src="{{ image(null, $paymentMethodName) }}" alt="{{ __('Paymethod') }}" class="mt-20 via-paypal img-fluid">

            <div class="mt-40 transaction-box">
                <!-- Amount -->
                <div class="d-flex justify-content-between border-b-EF pb-13">
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ __('Deposit Amount') }} {{  $paymentMethodName }}</p>
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ moneyFormat($currencyCode, formatNumber($amount, $currencyId)) }}</p>
                </div>

                <!-- Fee -->
                <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ __('Fee') }}</p>
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ moneyFormat($currencyCode, formatNumber($totalFees, $currencyId)) }}</p>
                </div>

                <!-- Total -->
                <div class="d-flex justify-content-between mt-3 total">
                    <p class="mb-0 f-16 gilroy-medium text-dark">{{ __('Total') }}</p>
                    <p class="mb-0 f-16 gilroy-medium text-dark">{{ moneyFormat($currencyCode, formatNumber($totalAmount, $currencyId)) }}</p>
                </div>
            </div>
        </div>

        <!-- Confirm Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="depositConfirmBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="depositConfirmBtnText">{{ __('Confirm & Deposit') }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>

        <!-- Back Button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.deposit.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="depositConfirmBackBtn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn ns depositConfirmBackBtnText">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')
    <script type="text/javascript">
        'use strict';
        let confirmBtnText = "{{ __('Confirming...') }}";
        let pretext "{{ __('Confirm & Deposit') }}";
    </script>
    <script src="{{ asset('public/user/customs/js/deposit.min.js') }}"></script>
@endpush
