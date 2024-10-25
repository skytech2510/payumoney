@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="crypto-send-success">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Send :x', ['x' => $walletCurrencyCode]) }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 3 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Send :x', ['x' => $walletCurrencyCode]) }}</p>
    <div class="text-center">{!! svgIcons('stepper_success') !!}</div>

    <div class="mt-36 d-flex justify-content-center position-relative h-44">
        <lottie-player class="position-absolute success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}" background="transparent" speed="1" autoplay></lottie-player>
    </div>

    <!-- Crypto Send Success -->
    <p class="mb-0 gilroy-medium f-20 success-text text-dark mt-20 text-center dark-5B r-mt-16">{{ __('Success!') }}!</p>

    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-CDO mt-6 r-mt-8 leading-25">{{ __('Coin has been transferred to the address. Amount will be added to the user wallet after 1 confirmation.') }}</p>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-CDO mt-6 r-mt-8 leading-25">{{ __(':x Sent Successfully.', ['x' => $walletCurrencyCode]) }}</p>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-CDO mt-6 r-mt-8 leading-25">{{ __('Amount will be added after :x confirmations.', ['x' => $confirmations]) }}</p>

    <!-- Crypto Send Address -->
    <div class="success-amount-box mt-4">
        <P class="mb-0 gilroy-medium text-primary dark-A0 text-center mt-29 r-text-12 f-16">{{ __('Address') }}</P>
        <p class="mb-0 text-dark dark-5B gilroy-Semibold f-18 text-center r-text-24 pb-23 mt-2">{!! $receiverAddress !!}</p>
    </div>

    <!-- Crypto Send Amount -->
    <div class="success-amount-box mt-4">
        <P class="mb-0 gilroy-medium text-primary dark-A0 text-center mt-29 r-text-12 f-16">{{ __('Send Amount') }}</P>
        <p class="mb-0 text-dark dark-5B gilroy-Semibold f-32 text-center r-text-24 pb-23 mt-2">{{ moneyFormat($currencySymbol, formatNumber($amount, $currencyId)) }}</p>
    </div>

    <!-- Button -->
    <div class="d-flex justify-content-center mt-28 r-mt-20">
        <a href="{{ route('user.crypto_send_receive.print', [encrypt($transactionId)]) }}" class="print-btn d-flex justify-content-center align-items-center gap-10" target="__blank">{!! svgIcons('printer') !!}<span>{{ __('Print') }}</span>
        </a>
        <a href="{{ route('tatumio.user.crypto_send.create', [encrypt($walletCurrencyCode), encrypt($walletId)]) }}" class="bg-white repeat-btn d-flex justify-content-center align-items-center ml-20">
            <span class="gilroy-medium">{{ __('Send :x Again', ['x' => $walletCurrencyCode]) }}</span>
        </a>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/user/templates/animation/lottie-player.min.js') }}"></script>
    <script type="text/javascript">
        'use strict';
        var token = $('[name="_token"]').val();
    </script>
    <script src="{{ asset('Modules/TatumIo/Resources/assets/user/js/crypto_send_receive.min.js') }}" type="text/javascript"></script>
@endpush
