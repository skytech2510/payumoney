@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="exchangeMoneyConfirm">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Exchange Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm Exchange Money') }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>

    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Save time and exchange your currency at an attractive rate. You are just one click away to exchange your currency.') }}</p>

    <div class="mt-32 param-ref text-center">
        <!-- Exchanged Amount -->
        <p class="mb-0 gilroy-medium text-primary dark-A0 f-16 sm-font-14 recipent-top-margin">{{ __('Exchanged Amount') }}</p>
        <p class="mb-0 text-center f-20 gilroy-medium text-dark dark-5B mt-10">{{ moneyFormat($fromCurrency->code, formatNumber($transInfo['defaultAmnt'], $transInfo['currency_id'])) }}</p>
        <div class="mt-40 transaction-box">

            <!-- Rate -->
            <div class="d-flex justify-content-between border-b-EF pb-13">
                <p class="mb-0 gilroy-regular text-gray-100">{{ __('Rate') }}</p>
                <p class="mb-0 gilroy-regular text-gray-100"><span>{{ moneyFormat($fromCurrency->code, formatNumber(1)) }} = </span><span>{{ moneyFormat($transInfo['currCode'], formatNumber($transInfo['dCurrencyRate'])) }}</span></p>
            </div>

            <!-- Fee -->
            <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                <p class="mb-0 gilroy-regular text-gray-100">{{ __('Fee') }}</p>
                <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($fromCurrency->code, formatNumber($transInfo['fee'], $transInfo['currency_id'])) }}</p>
            </div>

            <!-- Total -->
            <div class="d-flex justify-content-between mt-3 total">
                <p class="mb-0 gilroy-medium text-dark">{{ __('Total') }}</p>
                <p class="mb-0 gilroy-medium text-dark">{{ moneyFormat($fromCurrency->code, formatNumber($transInfo['totalAmount'], $transInfo['currency_id'])) }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('user.exchange_money.confirm') }}" method="POST" id="exchangeMoneyConfirmForm">
        @csrf
        <!-- submit button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="exchangeMoneyConfirmBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span id="exchangeMoneyConfirmBtnText">{{ __('Confirm & Exchange') }}</span>
            </button>
        </div>
        
        <!-- Back button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.exchange_money.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="exchangeMoneyBackButton">
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
    <script src="{{ asset('public/user/customs/js/exchange-currency.min.js') }}"></script>
@endpush