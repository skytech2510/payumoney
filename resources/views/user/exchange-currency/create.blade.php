@extends('user.layouts.app')

@section('content')
@include('user.common.alert')
<div class="bg-white pxy-62 pt-62 shadow" id="exchangeMoneyCreate">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Exchange Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Setup Money') }}</p>
    <div class="text-center">{!! svgIcons('stepper_create') !!}</div>

    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Exchange currencies from the comfort of your home, quickly, safely with a minimal fees.Select the wallet & put the amount you want to exchange.') }}</p>
    
    <form method="post" action="{{ route('user.exchange_money.store') }}" id="exchangeMoneyCreateForm">
        @csrf

        <input type="hidden" name="percentage_fee" id="feesPercentage">
        <input type="hidden" name="fixed_fee" id="feesFixed">
        <input type="hidden" name="total_fee" id="totalFees">
        <input type="hidden" name="final_amount" id="finalAmount">
        <input type="hidden" name="sessionToWalletCode" id="sessionToWalletCode" >
        <input type="hidden" name="sessionFromWalletCode" id="sessionFromWalletCode">
        <input type="hidden" name="destinationCurrencyRate" id="destinationCurrencyRate" >
        <input type="hidden" name="destinationCurrencyCode" id="destinationCurrencyCode" >

        <div class="row my-auto">

            <!-- From Currency Wallet -->
            <div class="col-5 extra-col-width p-form">
                <div class="param-ref money-ref r-mt-11">
                    <label class="gilroy-medium text-gray-100 mb-2 f-16 mt-28 r-mt-0">{{ __('From') }}</label>
                    <span class="f-12 mob-f-12 gilroy-medium mtop-10 mb-0">
                        <span class="balance-text text-gray-100">{{ __('Balance') }}: </span><span class="d-none balance-color" id="fromCurrencyWalletBalanceDiv">( <span id="fromWalletCurrencyBalance"></span> <span id="fromWalletCurrencyCode"></span> )</span>
                    </span>
                    
                    <div class="avoid-blink">
                        <select class="select2" data-minimum-results-for-search="Infinity" id="fromCurrencyWallet" name="from_currency_id">
                            @foreach($activeHasTransactionUserCurrencyList as $currency)
                                <option data-type="{{ $currency['type'] }}" value="{{ $currency['id'] }}" 
                                    @if (!empty(session('transInfo')))
                                        @if (session('transInfo')['from_currency_id'] == $currency['id'] )
                                            {{ 'selected="selected"' }}
                                        @endif
                                    @else
                                        {{ $defaultWallet->currency_id == $currency['id'] ? 'selected="selected"' : '' }}
                                    @endif
                                >{{ $currency['code'] }}</option>
                            @endforeach
                        </select>
                        @error('from_currency_id')
                            <span class="custom-error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                </div>
            </div>
            <div class="col-1 position-relative p-form p-to">
                <div class="direction-icon d-flex justify-content-center align-items-end">
                    <div class="h-60 d-flex align-items-center justify-content-center direction">{!! svgIcons('arrow_right_left') !!}</div>
                </div>
            </div>

            <!-- To Currency Wallet -->
            <div class="col-5 extra-col-width p-to">
                <div class="param-ref money-ref r-mt-11">
                    <label class="gilroy-medium text-gray-100 mb-2 f-16 mt-28 r-mt-0">{{ __('To') }}</label>
                    <span class="f-12 mob-f-12 gilroy-medium mtop-10 mb-0 d-none" id="toCurrencyWalletBalanceDiv">
                        <span class="balance-text text-gray-100">{{ __('Balance') }}: </span><span class="balance-color">( <span id="toWalletCurrencyBalance"></span> <span id="toWalletCurrencyCode"></span> )</span>
                    </span>

                    <div class="avoid-blink">
                        <select class="select2" data-minimum-results-for-search="Infinity" id="toCurrencyWallet" name="currency_id"></select>
                    </div>
                    @error('currency_id')
                        <span class="custom-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Amount -->
        <div class="label-top mt-20">
            <label class="gilroy-medium text-gray-100 mb-2 f-16">{{ __('Your Amount') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2" id="amount" name="amount" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" placeholder="0.00" value="{{ session('transInfo')['amount'] ?? '' }}" required data-value-missing="{{ __('This field is required') }}">
            <span class="custom-error" id="amountLimitError">
            @error('amount')
                <span class="custom-error">{{ $message }}</span>
            @enderror
        </div>
        <small class="mb-0 gilroy-medium f-12 mob-f-12 mt-11 r-mt-10 text-gray" id="feesLimitDiv">
            {{ __('Fee') }}(<span id="formattedFeesPercentage">0</span>%+<span id="formattedFeesFixed">0</span>)
            {{ __('Total Fee') }}: <span id="formattedTotalFees">0.00</span>
        </small>

        <!-- Converted Amount -->
        <div class="mb-4 label-top mt-20">
            <label class="gilroy-medium text-gray-100 mb-2 f-16">{{ __('Converted Amount') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2" value="" id="convertedAmount" placeholder="0.00" readonly>
        </div>

        <!-- Exchange rate -->
        <div class="mt-2 mb-4" id="exchangeRateDiv">
            <p class="mb-0 dark-B87 gilroy-medium f-14 text-center">{{ __('Exchange rate') }}: 1 <span class="text-primary" id="exchangeRateFromWalletCode"></span> = <span class="text-primary" id="exchangeRate"></span> <span class="text-primary" id="exchangeRateToWalletCode"></span></p>
        </div>

        <!-- submit button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary" id="exchangeMoneyCreateSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <span id="exchangeMoneyCreateSubmitBtnText">{{ __('Proceed') }}</span>
                <span id="exchangeMoneySvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('js')

    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')

    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}"></script>
    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}"></script>
    <script>
        "use strict";
        var csrfToken = $('[name="_token"]').val();
        var convertedCurrency ="{{ session('transInfo')['currency_id'] ?? '' }}";
        var currencyListExceptSelectedUrl = "{{ route('user.exchange_money.currency_list_except_selected') }}";
        var walletBalanceUrl = "{{ route('user.exchange_money.wallet_balance') }}";
        var currentUrl = "{{ url()->current() }}";
        var exchangeRateUrl = "{{ route('user.exchange_money.exchange_rate') }}";
        var amountLimitCheckUrl = "{{ route('user.exchange_money.amount_limit_check') }}"
        var toWalletOptionText = "{{ __('Select Wallet')}}";
        var submitButtonText = "{{ __('Submiting...') }}";
        var swalTitleText = "{{ __('Please Wait...') }}";
        var swalBodyText = "{{ __('Loading...') }}";
        var transactionTypeId = '{{ Exchange_From }}';
        var lowBalanceText = "{{ __('Not have enough balance !') }}";
        var failedText = '{{ __("Error") }}';
        let submitBtnText = '{{ __("Processing...") }}';
    </script>
    <script src="{{ asset('public/user/customs/js/exchange-currency.min.js') }}"></script>
@endpush