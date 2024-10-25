@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Withdraw Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 3 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Withdrawal Complete') }}</p>
    <div class="text-center">
        {!! svgIcons('stepper_success') !!}
    </div>

    <div class="mt-36 d-flex justify-content-center position-relative h-44">
        <lottie-player class="position-absolute success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}"
        background="transparent" speed="1" autoplay></lottie-player>
    </div>

    <p class="mb-0 gilroy-medium f-20 success-text text-dark mt-20 text-center dark-5B r-mt-16">{{ __('Success!') }}</p>

    <p class="mb-0 text-center f-14 gilroy-medium text-gray-100 dark-CDO mt-6 r-mt-8 leading-25">
        {{ __('Your withdrawal process is successfully complete.') }}
    </p>

    @include('user.common.alert')

    <div class="amount-withdraw border-light-mode r-mt-24">
        <p class="mb-0 text-center gilroy-medium text-primary dark-5B f-16">{{ __('Amount Withdrawn') }}</p>
        <p class="mb-0 text-center gilroy-Semibold text-dark dark-A0 f-32 mt-8">{{ moneyFormat($currencyCode, formatNumber($amount, $currency_id)) }}</p>
    </div>

    <div class="d-flex justify-content-center mt-28 r-mt-20">
        <a href="{{ route('user.withdrawal.print', $transactionId) }}" class="print-btn d-flex justify-content-center align-items-center gap-10" target="_blank">
            {!! svgIcons('printer') !!}
            <span>{{ __('Print') }}</span>
        </a>

        <a href="{{ route('user.withdrawal.create') }}" class="repeat-btn d-flex justify-content-center align-items-center ml-20">
        <span class="gilroy-medium">{{ __('Withdraw Again') }}</span>
        </a> 
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('public/user/templates/animation/lottie-player.min.js') }}"></script>
<script src="{{ asset('public/user/customs/js/withdrawal.min.js') }}"></script> 
@endpush