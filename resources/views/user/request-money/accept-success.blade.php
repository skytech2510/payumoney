@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="requestMoneySuccess">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Accept Request Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 3 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Success Accept Request Money') }}</p>
    <div class="text-center">{!! svgIcons('stepper_success') !!}</div>

    <div class="mt-36 d-flex justify-content-center position-relative h-44">
        <lottie-player class="position-absolute success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}" background="transparent" speed="1" autoplay></lottie-player>
    </div>

    <p class="mb-0 gilroy-medium f-20 success-text text-dark mt-20 text-center dark-5B r-mt-16">{{ __('Success!') }}</p>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-CDO mt-6 r-mt-8 leading-25">{{ __('Requested Money Accepted Successfully.') }}</p>
    
    <!-- Accept Money Details -->
    <div class="print-mail mt-4">
        <div class="d-flex gap-18 justify-content-center">
            <div class="d-flex align-items-center justify-content-center user-mail mt-20">
                <img src="{{ image($requestCreator['picture'], 'profile') }}" class="img-fluid">
            </div>
            <div class="d-flex">
                <div class="mt-26">
                    <p class="mb-0 text-dark gilroy-medium f-16 theme-font">{{ $requestCreator['first_name'] .' '. $requestCreator['last_name'] }}</p>
                    <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2 leading-20 theme-amount">{{ __('Requested Amount') }}</p>
                    <p class="mb-0 text-primary dark-B87 gilroy-medium mt-2p f-16 theme-usd">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['amount'], $transInfo['currency_id'])) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Button -->
    <div class="d-flex justify-content-center mt-32 r-mt-20">
        <a href="{{ route('user.request_money.print', $transInfo['trans_id']) }}" class="print-btn d-flex justify-content-center align-items-center gap-10 b-none" target="_blank">
            {!! svgIcons('printer') !!}
            <span>{{ __('Print') }}</span>
        </a>
        <a href="{{ route('user.transactions.index') }}" class="bg-white repeat-btn d-flex justify-content-center align-items-center ml-20">
            <span class="gilroy-medium">{{ __('Transactions') }}</span>
        </a>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('public/user/templates/animation/lottie-player.min.js') }}"></script>
@endpush