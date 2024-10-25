@extends('merchantPayment.layouts.app')

@section('content')

<div class="section-payment">
    <div class="transaction-details-module">
        @if($isMerchantAvailable && $merchant->status == 'Approved')
            <div class="transaction-order-quantity">
                <h2>{{ __('Transaction Details') }}</h2>
            </div>
            <div class="transaction-order-quantity">
                <div class="d-flex justify-content-between">
                    <h3>{{ $paymentInfo['item_name'] ? $paymentInfo['item_name'] : "" }}</h3>
                    <span>
                        @php
                            $amount = isset($paymentInfo['amount']) ? $paymentInfo['amount'] : 0;
                        @endphp
                        {{ moneyFormat(optional($merchant->currency)->code, formatNumber($amount, optional($merchant->currency)->id)) }}
                    </span>
                </div>
                <p>{{ __('Merchant ID') }}: #{{ $paymentInfo['merchant'] ? $paymentInfo['merchant'] : "" }}</p>
                <p>{{ __('Order ID') }}: #{{ $paymentInfo['order'] ? $paymentInfo['order'] : "" }}</p>
            </div>
            <div class="transaction-total d-flex justify-content-between">
                <h3>{{ __('Total') }} ({{ optional($merchant->currency)->code }})</h3>
                <span>{{ formatNumber($amount, optional($merchant->currency)->id) }}</span>
            </div>

            <form action="{{ route('user.merchant.show_payment_form') }}" method="get" id="paymentMethodForm">

                <input name="merchant_id" value="{{ isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : '' }}" type="hidden">
                <input name="merchant" value="{{ isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : '' }}" type="hidden">
                <input name="amount" value="{{ $amount }}" type="hidden">
                <input name="currency" value="{{ optional($merchant->currency)->code}}" type="hidden">
                <input name="currency_id" value="{{ optional($merchant->currency)->id}}" type="hidden">
                <input name="order_no" value="{{ isset($paymentInfo['order']) ? $paymentInfo['order'] : '' }}" type="hidden">
                <input name="item_name" value="{{ isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : '' }}" type="hidden">

                <div class="transaction-payment-method">
                    <p>{{ __('Accepted payment methods') }}</p>
                    <div class="d-flex flex-wrap gap-18 mt-2 radio-hide">
                        @foreach($payment_methods as $value)
                            @if(!in_array($value['id'], [Bank, Crypto]) && in_array($value['id'], $cpm))
                                <input type="radio" name="method" value="{{ $value['name'] }}" id="{{ $value['id'] }}" {{ $value['name'] == 'Mts' ? 'checked' : '' }}>
                                <label for="{{ $value['id'] }}" class="gateway d-inline-flex flex-column justify-content-center align-items-center {{ $value['name'] == 'Mts' ? 'gateway-selected' : '' }}">
                                    <img src="{{ asset('public/dist/images/gateways/payments/'.strtolower($value['name']).'.png') }}" alt="{{ $value['name'] }}">
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="d-grid">
                    <button class="btn btn-lg btn-primary" type="submit" id="paymentMethodSubmitBtn">
                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                            <span class="visually-hidden"></span>
                        </div>
                        <span id="paymentMethodSubmitBtnText" class="px-1">{{ __('Continue') }}</span>
                    </button>
                </div>
            </form>
        @else
            <div class="transaction-payment-method text-center">
                <p class="text-danger fw-bold">{{ __('Merchant not found.') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('js')

    <script>
        'use strict';
        let paymentMethodSubmitBtnText = "{{ __('Continuing...') }}";
        let pretext = "{{ __('Continue') }}";
    </script>

    <script src="{{ asset('public/frontend/customs/js/merchant-payments/index.min.js') }}"></script>
@endsection
