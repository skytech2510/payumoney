@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Exchange Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 3 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Exchange Complete') }}</p>
    <div class="text-center">{!! svgIcons('stepper_success') !!}</div>

    <div class="mt-36 d-flex justify-content-center position-relative h-44">
        <lottie-player class="position-absolute success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}" background="transparent" speed="1" autoplay></lottie-player>
    </div>
    
    <p class="mb-0 gilroy-medium f-20 success-text text-dark mt-20 text-center dark-5B r-mt-16">{{ __('Success') }}!</p>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray-100 dark-CDO mt-6 r-mt-8 leading-25">{{ __('Currency exchange has been completed successfully.') }}</p>

    <!-- Exchange Details -->
    <div class="exchange-box border-light-mode border-dark-mode r-mt-24">
        <div class="mobile-p responsive-font d-flex justify-content-center align-items-center">
            <p class="extra mb-0 gilroy-Semibold text-dark dark-5B f-26">{{ moneyFormat($fromWallet->currency->code, formatNumber($transInfo['defaultAmnt'], $fromWallet->currency_id)) }}</p>
            <div class="h-32 px-20 r-px-10 d-flex align-items-center direction">{!! svgIcons('arrow_right_left_lg') !!}</div>
            <p class="mb-0 gilroy-Semibold text-dark dark-5B f-26">{{ moneyFormat($transInfo['currCode'], formatNumber($transInfo['finalAmount'], $transInfo['currency_id'])) }}</p>
        </div>
        <p class="r-exchange-mb gilroy-medium text-center font-14p text-gray-100">{{ __('Exchange rate') }}: {{ moneyFormat($fromWallet->currency?->code, formatNumber('1', $fromWallet->currency_id)) }} = {{ moneyFormat($transInfo['currCode'], formatNumber($transInfo['dCurrencyRate'], $transInfo['currency_id'])) }}</p>
    </div>

    <!-- New Balance Details -->
    <div class="d-flex justify-content-center">
        <div class="border-hr d-flex justify-content-center"></div>
    </div>

    <!-- New Balance -->
    <div class="new-balace border-light-mode">
        <p class="mb-0 text-center gilroy-medium text-dark dark-5B f-16">{{ __('New Balance') }}</p>
        <p class="mb-0 text-center gilroy-Semibold text-primary dark-A0 f-24 mt-9">{{ moneyFormat($fromWallet->currency?->code, formatNumber($fromWallet->balance, $fromWallet->currency_id)) }}</p>
    </div>

    <!-- button -->
    <div class="d-flex justify-content-center mt-24 r-mt-20">
        <a href="{{ route('user.exchange_money.print', $transInfo['trans_ref_id']) }}" class="print-btn d-flex justify-content-center align-items-center gap-10" target="_blank">
            {!! svgIcons('printer') !!}
            <span>{{ __('Print') }}</span>
        </a>
        <a href="{{ route('user.exchange_money.create') }}" class="repeat-btn d-flex justify-content-center align-items-center ml-20">
            <span class="gilroy-medium">{{ __('Exchange Again') }}</span>
        </a>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/dist/js/lottie-player.min.js') }}"></script>
@endpush