@extends('user.layouts.app')  

@section('content')
<div class="bg-white pxy-62 shadow" id="crypto-send-create">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Send :x', ['x' => $walletCurrencyCode]) }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Send :x', ['x' => $walletCurrencyCode]) }}</p>
    <div class="text-center">{!! svgIcons('stepper_create') !!}</div>

    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Enter recipient address and amount.') }}</p>

    @include('user.common.alert')
    
    <form method="post" action="{{ route('user.crypto_send.confirm') }}" id="crypto-send-form">
        <input name="_token" type="hidden" value="{{ csrf_token() }}" id="token"/>
        <input name="walletCurrencyCode" type="hidden" data-type="{{ $currencyType }}" value="{{ encrypt($walletCurrencyCode) }}" id="network"/>
        <input name="walletId" type="hidden" value="{{ encrypt($walletId) }}"/>
        <input name="senderAddress" type="hidden" value="{{ encrypt($senderAddress) }}"/>

        <!-- Recipient Address -->
        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="receiverAddress">{{ __('Recipient Address') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 receiverAddress" name="receiverAddress" id="receiverAddress" placeholder="{{ __('Enter valid recipient :x address', ['x' => $walletCurrencyCode]) }}" value="{{ session('transInfo')['receiverAddress'] ?? old('receiverAddress') }}" required data-value-missing="{{ __("Please provide a :x address.", ['x' => $walletCurrencyCode]) }}">
            <span class="amountLimit custom-error"></span>
        </div>

        <div class="mt-20 label-top">
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">*{{ __('Crypto transactions might take few moments to complete.') }}</p>
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">*{{ __('Only send :x to this address, receiving any other coin will result in permanent loss.', ['x' => $walletCurrencyCode]) }}</p>
        </div>
        <!-- End Recipient Address -->


        <!-- Amount -->
        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="amount">{{ __('Amount') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 amount" name="amount" id="amount" placeholder="{{ __('Enter amount') }}" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" value="{{ session('transInfo')['amount'] ?? old('amount') }}" required data-value-missing="{{ __('This field is required.') }}">
            <span class="amountLimit custom-error"></span>
        </div>

        <div class="mt-20 label-top">
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">*{{ __('The amount withdrawn/sent must at least be :x :y.', ['x' => getBlockIoMinLimit('amount', $walletCurrencyCode), 'y' => $walletCurrencyCode]) }}</p>
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">*{{ __('Please keep at least :x :y for network fees.', ['x' => getBlockIoMinLimit('networkFee', $walletCurrencyCode), 'y' => $walletCurrencyCode]) }}</p>
        </div>
        <!-- End Amount -->

        <!-- Priority -->
        <div class="mt-28 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="priority">{{ __('Priority') }}</label>
            <div class="avoid-blink">
                <select class="select2 priority" data-minimum-results-for-search="Infinity" id="priority" name="priority">
                    <option {{ old('priority') == 'low' ? 'selected' : '' }} value="low">{{ __('Low') }}</option>
                    <option {{ old('priority') == 'medium' ? 'selected' : '' }} value="medium">{{ __('Medium') }}</option>
                    <option {{ old('priority') == 'high' ? 'selected' : '' }} value="high">{{ __('High') }}</option>
                </select>
            </div>
        </div>

        <div class="mt-20 label-top">
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">*{{ __('Larger transactions incur higher network fees.') }}</p>
            <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">*{{ __('You can specify the priority for your transactions to adjust the network fee you wish to pay.') }}</p>
        </div>
        <!-- Priority -->

        <!-- Submit -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="crypto-send-submit-btn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="crypto-send-submit-btn-txt">{{ __('Proceed') }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"  type="text/javascript" ></script>

    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')

    <script type="text/javascript">
        'use strict';
        var walletCurrencyCode = '{{ $walletCurrencyCode }}';
        var senderAddress = '{{ $senderAddress }}';
        var validateAddressUrl = '{{ route("user.crypto_send.validate_address") }}';
        var validateBalanceUrl = '{{ route("user.crypto_send.validate_balance") }}';
        var cryptoSendConfirmUrl = '{{ route("user.crypto_send.confirm") }}';
        var pleaseWait = '{{ __("Please Wait") }}';
        var loading = '{{ __("Loading...") }}';
        var minCryptoAmount = '{{ __("The minimum amount must be :x.") }}';
        var sendBtn = '{{ __("Send") }}';
        var sending = '{{ __("Sending...") }}';
        var submitBtnText = '{{ __("Processing...") }}';
    </script>

    <script src="{{ asset('Modules/BlockIo/Resources/assets/user/js/crypto_send_receive.min.js') }}" type="text/javascript"></script>
@endpush
