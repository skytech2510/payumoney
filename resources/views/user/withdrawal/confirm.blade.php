@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="withdrawalConfirm">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Withdraw Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm Withdrawal') }}</p>
    <div class="text-center">
        {!! svgIcons('stepper_confirm') !!}
    </div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">
        {{ __('Please take a look before you confirm. After the confirmation the administrator review the withdrawal and fund amount to your Paypal or Bank account.') }}
    </p>

    @include('user.common.alert')

    <div class="mt-32 param-ref text-center">
        <img class="mt-20 via-paypal" src="{{ image(null, $transInfo['payout_setting']->paymentMethod?->name) }}" alt="{{ $transInfo['payout_setting']->paymentMethod?->name }}">
        @if (isset($transInfo['payout_setting']->paymentMethod) && $transInfo['payout_setting']->paymentMethod->name == 'Bank')
            <p class="mb-18 f-18 leading-22 text-dark gilroy-Semibold text-start">{{ __('Details') }}</p>
        
            <div class="transaction-box">

                <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                    <p class="mb-0 gilroy-regular text-gray-100">{{ __("Bank Account Holder's Name") }}</p>
                    <p class="mb-0 gilroy-regular text-gray-100">{{ $transInfo['payout_setting']->account_name }}</p>
                </div>

                <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                    <p class="mb-0 gilroy-regular text-gray-100">{{ __('Bank Account Number/IBAN') }}</p>
                    <p class="mb-0 gilroy-regular text-gray-100">{{ $transInfo['payout_setting']->account_number }}</p>
                </div>
                <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                <p class="mb-0 gilroy-regular text-gray-100">{{ __('SWIFT Code') }}</p>
                <p class="mb-0 gilroy-regular text-gray-100">{{ $transInfo['payout_setting']->swift_code }}</p>
                </div>
            </div>
        @endif
        <div class="mt-32 transaction-box">
            <p class="mb-18 f-18 leading-22 text-dark gilroy-Semibold text-start">{{ __('Currency') }}</p>
            <div class="d-flex justify-content-between border-b-EF pb-13">
            <p class="mb-0 gilroy-regular text-gray-100">{{ __('Withdrawal Amount') }}</p>
            <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['amount'], $transInfo['currency_id'])) }}</p>
            </div>
            <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
            <p class="mb-0 gilroy-regular text-gray-100">{{ __('Fee') }}</p>
            <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['fee'], $transInfo['currency_id'])) }}</p>
            </div>
            <div class="d-flex justify-content-between mt-3 total mob-mb-12">
            <p class="mb-0 gilroy-medium text-dark">{{ __('Total') }}</p>
            <p class="mb-0 gilroy-medium text-dark">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['totalAmount'], $transInfo['currency_id'])) }}</p>
            </div>
        </div>
    </div>
    <div class="d-grid">
        <a href="{{ route('user.withdrawal.success') }}" class="btn btn-lg btn-primary mt-4" id="withdrawalConfirmSubmitBtn">
            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                <span class="visually-hidden"></span>
            </div>
            <span id="withdrawalConfirmSubmitBtnText">{{ __('Confirm & Withdraw') }}</span>
        </a>
    </div> 
    <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
        <a href="{{ route('user.withdrawal.create') }}"
            class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="withdrawalConfirmBackBtn">
            {!! svgIcons('left_angle') !!}
            <span class="ms-1 back-btn" id="withdrawalConfirmBackBtnText" >{{ __('Back') }}</span>
        </a>
    </div>
</div>
@endsection

@push('js')
<script type="text/javascript">
    'use strict';
    var submitButtonText = "{{ __('Submitting...') }}";
</script>
<script src="{{ asset('public/user/customs/js/withdrawal.min.js') }}"></script>
@endpush