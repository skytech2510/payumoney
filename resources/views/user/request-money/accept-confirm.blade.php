@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="requestMoneyAcceptConfirm">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Accept Request Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm accept request money') }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Take a look before sending request. Do not worry, if the payer does not have a account, we will get them set up for free.') }}</p>


    <div class="mt-32 param-ref text-center">
        <!-- Accept Money Details -->
        <p class="mb-0 gilroy-medium text-primary dark-A0 f-16 sm-font-14 recipent-top-margin">{{ __('Recipent') }}</p>
        <p class="mb-0 text-center f-20 gilroy-medium text-dark dark-5B mt-10">{{ $transInfo['userName'] ?? $transInfo['emailOrPhone'] }}</p>
        <div class="mt-40 transaction-box">
            <!-- Amount -->
            <div class="d-flex justify-content-between border-b-EF pb-13">
                <p class="mb-0 gilroy-regular text-gray-100">{{ __('Accepted Amount') }}</p>
                <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['amount'], $transInfo['currency_id'])) }}</p>
            </div>

            <!-- Fee -->
            <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                <p class="mb-0 gilroy-regular text-gray-100">{{ __('Fee') }}</p>
                <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['fee'], $transInfo['currency_id'])) }}</p>
            </div>
            
            <!-- Total -->
            <div class="d-flex justify-content-between mt-3 total">
                <p class="mb-0 gilroy-medium text-dark">{{ __('Total') }}</p>
                <p class="mb-0 gilroy-medium text-dark">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['totalAmount'], $transInfo['currency_id'])) }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('user.request_money.accept.success') }}" method="post" id="requestMoneyAcceptConfirmForm">
        @csrf

        <!-- Confirm button -->
        <div class="d-grid">
            <button class="btn btn-lg btn-primary mt-4" id="requestMoneyAcceptConfirmBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <span id="requestMoneyAcceptConfirmBtnText">{{ __('Confirm & Send') }}</span>
            </button>
        </div>

        <!-- Back button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.request_money.accept.create', $requestPaymentId) }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="requestMoneyAcceptBackButton">
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
<script src="{{ asset('public/user/customs/js/accept-money.min.js') }}"></script>
@endpush