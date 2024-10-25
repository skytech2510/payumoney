@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="depositCreate">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Deposit Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Create Deposit') }}</p>
    <div class="text-center">{!! svgIcons('stepper_create') !!}</div>

    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('You can deposit to your wallets using our popular payment methods. Fill the details correctly & the amount you want to deposit.') }}</p>

    @include('user.common.alert')

    <form method="post" action="{{ route('user.deposit.confirm') }}" id="depositCreateForm">
        @csrf
        <input type="hidden" name="percentage_fee" id="percentage_fee" value="">
        <input type="hidden" name="fixed_fee" id="fixed_fee" value="">
        <input type="hidden" name="total_fee" id="total_fee" value="">

        <!-- Currency -->
        <div class="mt-28 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="currency_id">{{ __('Currency') }}</label>
            <div class="avoid-blink">
                <select class="select2" data-minimum-results-for-search="Infinity" name="currency_id" id="currency_id">
                    @foreach ($activeCurrencyList as $currency)
                        <option data-type="{{ $currency['type'] }}" value="{{ $currency['id'] }}"
                            @if (old('currency_id') && old('currency_id') == $currency['id']) {{ 'selected="selected"' }}
                            @elseif (!empty(session('paymentData')) && session('paymentData')['currencyId'] == $currency['id']) {{ 'selected="selected"' }}
                            @elseif (empty(old('currency_id')) && empty(session('paymentData')) && $defaultWallet == $currency['id']) {{ 'selected="selected"' }}
                            @endif
                            >{{ $currency['code'] }}</option>
                    @endforeach
                </select>
            </div>
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">{{ __('Fee') }} (<span class="pFees">0%</span>+<span class="fFees"> 0</span>) {{ __('Total Fee') }}: <span class="total_fees">0.00</span></p>
        </div>

        <!-- Amount -->
        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="amount">{{ __('Amount') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2" name="amount" id="amount" placeholder="{{ __('Enter amount') }}" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" value="{{ session('paymentData')['amount'] ?? old('amount') }}" required data-value-missing="{{ __('This field is required.') }}">
            <span class="amountLimit custom-error"></span>
        </div>

        <!-- Payment Methods Empty -->
        <div class="row">
            <div class="col-12">
                <div class="mt-20 param-ref d-none" id="paymentMethodEmpty">
                    <label class="gilroy-medium text-warning mb-2 f-15">{{ __('Fees Limit or Payment Method are currently inactive.') }}</label>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="row">
            <div class="col-12">
                <div class="mt-20 param-ref" id="paymentMethodSection">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15" for="payment_method">{{ __('Payment Method') }}</label>
                    <div class="avoid-blink">
                        <select class="select2" data-minimum-results-for-search="Infinity" name="payment_method" id="payment_method"></select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="depositCreateSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="depositCreateSubmitBtnText">{{ __('Proceed') }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
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


    <script type="text/javascript">
        'use strict';
        var token = $('[name="_token"]').val();
        var paymentMethodListUrl = "{{ route('user.deposit.payment_method') }}";
        var feesLimitUrl = "{{ route('user.deposit.fees_limit') }}";
        var transactionTypeId = "{{ Deposit }}";
        var selectedPaymentMethod = "{{ session('paymentData')['payment_method'] ?? '' }}";
        var submitBtnText = "{{ __('Processing...') }}";
    </script>

    <script src="{{ asset('public/user/customs/js/deposit.min.js') }}"></script>
@endpush
