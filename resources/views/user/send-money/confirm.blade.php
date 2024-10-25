@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="sendMoneyConfirm">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Send Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm Your Transfer') }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Take a look before you send. Do not worry, if the recipient does not have an account, we will get them set up for free.') }}</p>
    <div class="mt-32 param-ref text-center">
        <p class="mb-0 gilroy-medium text-primary dark-A0 f-16 sm-font-14 recipent-top-margin">{{ __('Recipient') }}</p>
        <p class="mb-0 text-center f-20 gilroy-medium text-dark dark-5B mt-10">{{ $transInfo['userName'] ?? $transInfo['receiver'] }}</p>
        <div class="mt-40 transaction-box">
            <div class="d-flex justify-content-between border-b-EF pb-13">
                <p class="mb-0 gilroy-regular text-gray-100">{{ __('Transfer Amount') }}</p>
                <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['amount'], $transInfo['currency_id'])) }}</p>
            </div>
            <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                <p class="mb-0 gilroy-regular text-gray-100">{{ __('Fee') }}</p>
                <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['fee'], $transInfo['currency_id'])) }}</p>
            </div>
            <div class="d-flex justify-content-between mt-3 total">
                <p class="mb-0 gilroy-medium text-dark">{{ __('Total') }}</p>
                <p class="mb-0 gilroy-medium text-dark">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['totalAmount'], $transInfo['currency_id'])) }}</p>
            </div>
        </div>
    </div>
    <form action="{{ route('user.send_money.confirm') }}" method="post" id="sendMoneyConfirmForm">
        @csrf
        <div class="d-grid">
            <button class="btn btn-lg btn-primary mt-4" id="sendMoneyConfirmBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <span id="sendMoneyConfirmBtnText">{{ __('Confirm & Send') }}</span>
            </button>
        </div>
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.send_money.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')
    <script type="text/javascript">
        'use strict';
        let confirmBtnText = "{{ __('Confirming...') }}";
    </script>
    <script src="{{ asset('public/user/customs/js/send-money.min.js') }}"></script>
@endpush