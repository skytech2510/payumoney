@extends('gateways.layouts.payment')

<link rel="stylesheet" href="{{ asset('public/frontend/customs/css/gateway/coinpayments.min.css') }}">

@section('content')
    <div>
        <div class="text-center">
            <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-capitalize">{{ __('CoinPayments Deposit Fund') }}</p>
        </div>
        <div class="mt-32 px-5">
            <div class="row gy-4 gy-lg-0 px-5 col-reverse-sm">
                <div class="col-12 col-xl-8 col-lg-7">
                    <div class="shadow p-24 bg-white rounded-8">
                        <form class="d-flex gap-3">
                            <div class="w-100 deposit-search_area">
                                <input type="text" class="form-control deposit-search_input apply-bg l-s2" placeholder="Search" id="search-coin">

                            </div>
                        </form>
                        <div class="deposit-search_item-wrap mt-32" id="coin-list">
                            @foreach ($coin_accept as $coin)
                                <button class="deposit-search_item coin-div" coin-iso="{{ $coin['iso'] }}">
                                    <img class="deposit-search_item-img" src="{{ $coin['icon'] }}" alt="{{ $coin['name'] }}">
                                    <div class="deposit-search_item-content">
                                        <p class="mb-0 f-16 gilroy-Semibold text-dark">{{ $coin['name'] }}</p>
                                        <p class="mb-0 f-14 gilroy-medium text-gray-100" coin-rate="{{ $coin['rate'] }}">{{ $coin['rate'] }}</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4 col-lg-5">
                    <form action="{{ route('gateway.confirm_payment') }}" method="POST">
                        @csrf
                        <input type="hidden" name="selected_coin" id="input-selected-coin">
                        <input type="hidden" name="gateway" value="coinpayments" >
                        <input type="hidden" name="payment_type" value="{{ $payment_type }}">
                        <input type="hidden" name="amount" value="{{ $total }}">
                        <input type="hidden" name="total" value="{{ $total }}">
                        <input type="hidden" name="currency_id" value="{{ $currency_id }}">
                        <input type="hidden" name="payment_method" value="{{ $payment_method }}">
                        <input type="hidden" name="transaction_type" value="{{ $transaction_type }}">
                        <input type="hidden" name="params" value="{{ $params }}">
                        <input type="hidden" name="uuid" value="{{ $uuid }}">
                        <input type="hidden" name="redirectUrl" value="{{ $redirectUrl }}">
                        <input type="hidden" name="totalAmount" value="{{ $totalAmount }}">
                        <input type="hidden" name="currencyType" value="{{ $currencyType }}">

                        <!-- Transaction Summery -->
                        <div class="bg-white p-24 shadow rounded-8">
                            <h3 class="f-24 gilroy-Semibold text-dark">{{ __('Transaction Summery')}}</h3>
                            <div class="deposit-fund_transaction">
                                <p class="mb-0 fs-16 gilroy-medium text-dark">{{ __('Total Amount') }} ( {{ $currencyCode }} )</p>
                                <p class="mb-0 fs-16 gilroy-medium text-dark">{{ $total }} {{ $currencyCode }}</p>
                            </div>
                            <div class="deposit-fund_transaction">
                                <p class="mb-0 fs-16 gilroy-medium text-dark">{{ __('Pay with') }}</p>
                                <p class="mb-0 fs-16 gilroy-medium text-dark" id="selected-coin"></p>
                            </div>
                            <div class="deposit-fund_transaction">
                                <p class="mb-0 fs-16 gilroy-medium text-dark">{{ __('Total Amount') }} <span id="selected-iso"></span></p>
                                <p class="mb-0 fs-16 gilroy-medium text-dark" id="selected-coin-rate"></p>
                            </div>



                            <button class="deposit-fund_transaction-btn btn btn-lg btn-primary text-white f-16 gilroy-Semibold coinpayment-submit-button d-none">
                                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                                    <span class="visually-hidden"></span>
                                </div>
                                <span class="px-1" id="coinpaymentSubmitBtnText">{{ __('Pay Now') }}</span>
                            </button>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('js')

    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js')}}" type="text/javascript"></script>

    <script>
        "use strict";

        var encoded_coin_accept = @json($encoded_coin_accept);
        var coinList = JSON.parse(encoded_coin_accept);
        var csrfToken = "{{ csrf_token() }}";
        var submitText = "{{ __('Submitting...') }}";
        var coinIcon = "{{ __('Coin Icon') }}";
        var paymentUrl = "{{ route('gateway.confirm_payment')}}";
        var token = "{{ csrf_token() }}";
        var requiredText = "{{ __('This field is required.') }}";
        var redirect_url = "{{ $redirectUrl }}";
        var cancel_url = "{{ $cancel_url }}";
        var amount ="{{ $total }}";
        var currency_id = "{{ $currency_id }}";
        var payment_type = "{{ $payment_type }}";
        var payment_method_id ="{{ $payment_method }}";
        var transaction_type = "{{ $transaction_type }}";
        var params = '{{ $params }}';
    </script>

    <script src="{{ asset('public/frontend/customs/js/gateways/coinpayments.min.js') }}"></script>


@endpush
