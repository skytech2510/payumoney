@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="crypto-send-confirm">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Send :x', ['x' => $walletCurrencyCode]) }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Send :x', ['x' => $walletCurrencyCode]) }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Take a look before you send. Once the coin sent to this address, its never be undone.') }}</p>

    <form action="{{ route('tatumio.user.crypto_send.success') }}" method="post" id="cryptoSendConfirmForm">
        @csrf
        <!-- Confirm Details -->
        <div class="mt-32 param-ref text-center">
            <p class="mb-0 gilroy-medium text-gray-100 sm-font-14">{{ __('You are about to send :x to', ['x' => $walletCurrencyCode]) }}</p>
            <p class="mb-0 gilroy-medium text-primary sm-font-14">{{ $cryptoTrx['receiverAddress'] }}</p>

            <div class="mt-40 transaction-box">
                <!-- Amount -->
                <div class="d-flex justify-content-between border-b-EF pb-13">
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ __('Send Amount') }}</p>
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ moneyFormat($cryptoTrx['currencySymbol'], formatNumber($cryptoTrx['amount'], $currencyId)) }}</p>
                </div>

                <!-- Fee -->
                <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ __('Estimate Network Fee') }}</p>
                    <p class="mb-0 f-16 gilroy-regular text-gray-100">{{ moneyFormat($cryptoTrx['currencySymbol'], formatNumber($cryptoTrx['networkFee'], $currencyId)) }}</p>
                </div>

                <!-- Total -->
                <div class="d-flex justify-content-between mt-3 total">
                    <p class="mb-0 f-16 gilroy-medium text-dark">{{ __('Total') }}</p>
                    <p class="mb-0 f-16 gilroy-medium text-dark">{{ moneyFormat($cryptoTrx['currencySymbol'], formatNumber($cryptoTrx['amount'] + $cryptoTrx['networkFee'], $currencyId)) }}</p>
                </div>
            </div>
        </div>

        <!-- Confirm Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4 crypto-send-confirm-link" id="cryptoSendConfirmBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="cryptoSendConfirmBtnText">{{ __('Confirm & Send') }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>

        <!-- Back Button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('tatumio.user.crypto_send.create', [encrypt($walletCurrencyCode), encrypt($walletId)]) }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="cryptoSendBackBtn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn ns cryptoSendBackBtnText">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')
	<script type="text/javascript">
		'use strict';
		var cryptoSentCreateUrl = '{{ route("tatumio.user.crypto_send.create", [encrypt(strtolower($walletCurrencyCode)), encrypt($walletId)]) }}';
		var confirmBtnText = '{{ __("Confirming...") }}';
	</script>
	<script src="{{ asset('Modules/TatumIo/Resources/assets/user/js/crypto_send_receive.min.js') }}" type="text/javascript"></script>

@endpush
