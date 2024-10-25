@extends('user.layouts.app')

@section('title', __('Request Money Confirm'))

@section('content')
<div class="bg-white pxy-62 shadow" id="requestMoneyConfirm">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Request Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Confirm Your Request') }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Take a look before you send. Do not worry, if the recipient does not have an account, we will get them set up for free.') }}</p>
    
    <div class="mt-32 param-ref text-center">
        <p class="mb-0 gilroy-medium text-primary dark-A0 f-16 sm-font-14 recipent-top-margin">{{ __('You are requesting money from') }}</p>
        <p class="mb-0 text-center f-20 gilroy-medium text-dark dark-5B mt-10">{{ $transInfo['userName'] ?? $transInfo['email'] }}</p>
        <div class="mt-40 transaction-box">
            <p class="mb-0 gilroy-medium leading-20 text-center text-primary dark-A0 mt-32">{{ __('Amount') }}</p>
            <p class="mb-0 f-20 gilroy-medium text-center text-dark dark-5B mt-10">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['amount'], $transInfo['currency_id'])) }}</p>
        </div>
    
    </div>
    
    <form action="{{ route('user.request_money.confirm') }}" method="post" id="requestMoneyConfirmForm">
        @csrf
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="requestMoneyConfirmBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <span id="requestMoneyConfirmBtnText">{{ __('Confirm & Send') }}</span>
            </button>
        </div>

        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.request_money.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')

<script>
    "use strict";
    var csrfToken = $('[name="_token"]').val();
    var confirmBtnText = "{{ __('Confirming...') }}";
</script>
<script src="{{ asset('public/user/customs/js/request-money.min.js') }}"></script>

@endpush