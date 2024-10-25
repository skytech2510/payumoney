@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="withdrawalCreate">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Withdraw Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Create Withdrawal') }}</p>
    <div class="text-center">
        {!! svgIcons('stepper_create') !!}
    </div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">
        {{ __('Accumulated wallet funds can simply be withdrawn at any time, to your paypal ID or bank account. Setting up the withdrawal settings is must before proceeding to make a withdraw.') }}
    </p>
    @include('user.common.alert')
    <form method="post" action="{{ route('user.withdrawal.confirm') }}" id="withdrawalCreateForm">
        @csrf
        <input type="hidden" name="payment_method_id" id="payment_method_id">

        <!-- Payment Methods -->
        <div class="mt-28 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Payment Method') }}</label>
            <div class="avoid-blink">
                <select class="select2" data-minimum-results-for-search="Infinity" name="withdrawal_method_id" id="withdrawal_method_id">
                    @foreach ($withdrawalMethods as $method)
                        {!! payment_option($method) !!}
                    @endforeach
                </select>
            </div>
            @error('withdrawal_method_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Currency -->
        <div class="mt-20 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Currency') }}</label>
            <div class="avoid-blink">
                <select class="select2" data-minimum-results-for-search="Infinity" name="currency_id" id="currency_id">
                </select>
            </div>
            @error('currency_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div> 

        <p class="mb-0 gilroy-medium f-12 mob-f-12 mt-select-to-p text-gray-100" id="walletHelp">{{ __('Fee') }} &nbsp;<span class="pFees">0</span>%+<span class="fFees">0</span>&nbsp;{{ __('Total Fee') }}: &nbsp;<span class="total_fees">0.00</span></p>

        <!-- Amount -->
        <div class="label-top mt-20">
            <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Amount') }}</label>
            <input class="form-control input-form-control apply-bg l-s2"  name="amount" id="amount" onkeypress="return isNumberOrDecimalPointKey(this, event);" placeholder="0.00" type="text" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="{{ __('This field is required') }}" value="{{ session('withdrawalData')['amount'] ?? old('amount') }}">
        </div>
        <span class="amountLimit text-danger" id="amountLimit"></span>
        @error('amount')
            <div class="error">{{ $message }}</div>
        @enderror
        <div class="form-group d-none" id="bank">
            <span id="withdrawalMethodInfo"></span>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="withdrawalCreateSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="withdrawalCreateSubmitBtnText">{{ __('Proceed') }}</span>
                <span id="withdrawalSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div> 
    </form>
</div>
@endsection

@push('js')

@include('common.restrict_number_to_pref_decimal')
@include('common.restrict_character_decimal_point')

<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
<script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}"></script>
<script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js') }}"></script>

<script type="text/javascript">
    'use strict';
    var csrfToken = $('[name="_token"]').val();
    var amount = $('#amount').val();
    var currencyId = $('#currency_id').val();
    var paymentMethodId = $('#payment_method').val();
    var transactionTypeId = "{{ Withdrawal }}";
    var isActiveMobileMoney = "{!! config('mobilemoney.is_active') !!}";
    var confirmationUrl = "{{ route('user.withdrawal.confirm') }}";
    var withdrawalActiveCurrency = "{{ route('user.withdrawal.active_currencies') }}";
    var withdrawalAmountLimit = "{{ route('user.withdrawal.amount_limit_check') }}";
    var pleaseWaitText = "{{ __('Please Wait') }}";
    var loadingText = "{{ __('Loading...') }}";
    var notEnoughBalanceText = "{{ __('Not have enough balance!') }}";
    var submitButtonText = "{{ __('Submitting...') }}";
    var sessionCurrencyId = "{{ !empty(session('withdrawalData')) ? session('withdrawalData')['currency_id'] : '' }}";
</script>
<script src="{{ asset('public/user/customs/js/withdrawal.min.js') }}"></script>
@endpush