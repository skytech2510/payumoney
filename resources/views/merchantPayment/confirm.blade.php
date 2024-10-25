@extends('merchantPayment.layouts.app')

@section('content')
    
<div class="section-payment">
    <div class="transaction-details-module">
        <div class="transaction-order-quantity">
            <h2>{{ getColumnValue($transInfo?->app?->merchant?->user) }}'s {{ getColumnValue($transInfo?->app?->merchant, 'business_name') }}</h2>
            <p>{{ __('You are about to make payment via :x', ['x' => $transInfo->payment_method]) }}</p>
        </div>
        <div class="transaction-order-quantity">
            <h2>{{ __('Transaction Details') }}</h2>
            <div class="d-flex justify-content-between">
                <h3>{{ __('Subtotal') }}</h3>
                <span>
                    {{ moneyFormat($transInfo->currency, formatNumber($transInfo->amount, $currencyId)) }}
                </span>
            </div>
            @if ($transInfo->app?->merchant?->merchant_group?->fee_bearer == 'User')
                <div class="d-flex justify-content-between">
                    <h3>{{ __('Fees') }}</h3>
                    <span>
                        {{ moneyFormat($transInfo->currency, formatNumber($fees, $currencyId)) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <h3>{{ __('Fee Beared') }}</h3>
                    <span>
                        {{ $transInfo->app?->merchant?->merchant_group?->fee_bearer }}
                    </span>
                </div>
            @endif
        </div>
        <div class="transaction-total d-flex justify-content-between">
            <h3>
                {{ __('Total') }} 
                @if ($transInfo->app?->merchant?->merchant_group?->fee_bearer == 'User')
                    <small>({{ __('With Fees') }})</small> 
                @endif
            </h3>
            <span>{{ $transInfo->app?->merchant?->merchant_group?->fee_bearer == 'Merchant' ? moneyFormat($transInfo->currency, formatNumber($transInfo->amount, $currencyId)) : moneyFormat($transInfo->currency, formatNumber($transInfo->amount + $fees, $currencyId)) }}</span>
        </div>
        
        <form action="{{ url('merchant/payment/confirm') }}" method="get" id="expressPaymentConfirmForm">
            <div class="d-grid">
                <button class="btn btn-lg btn-primary" type="submit" id="expressPaymentSubmitBtn">
                    <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                        <span class="visually-hidden"></span>
                    </div>
                    <span id="expressPaymentSubmitBtnText" class="px-1">{{ __('Confirm') }}</span>
                </button>
            </div>
        </form>

        <!-- Cancel Button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ url('merchant/payment/cancel') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn">
                {!! svgIcons('left_angle') !!}
                <span class="px-1">{{ __('Cancel') }}</span>
            </a>
        </div>
    </div>
</div>
@endsection

@section('js')

    <script>
        'use strict';
        let expressPaymentSubmitBtnText = "{{ __('Confirming...') }}"; 
    </script>

    <script src="{{ asset('public/frontend/customs/js/merchant-payments/expressMerchantPayment.min.js') }}"></script>
@endsection