@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Send Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 3 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Money transfer complete') }}</p>
    <div class="text-center">{!! svgIcons('stepper_success') !!}</div>
    <div class="mt-36 d-flex justify-content-center position-relative h-44">
        <lottie-player class="position-absolute success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}" background="transparent" speed="1" autoplay></lottie-player>
    </div>
    <p class="mb-0 gilroy-medium f-20 success-text text-dark mt-20 text-center dark-5B r-mt-16">{{ __('Success!') }}</p>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-CDO mt-6 r-mt-8 leading-25">{{ __('Money has been transferred successfully.') }}</p>
    <div class="print-mail mt-4">
        <div class="d-flex gap-18 justify-content-center">
            <div class="d-flex align-items-center justify-content-center user-mail mt-20">
                <img src="{{ image($userPic, 'profile') }}" class="img-fluid">
            </div>                                            
            <div class="d-flex">
                <div class="mt-26">
                    <p class="mb-0 text-dark gilroy-medium f-16 theme-font">{{ $receiverName }}</p>
                    <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2 leading-20 theme-amount">{{ __('Transferred Amount') }}</p>
                    <p class="mb-0 text-primary dark-B87 gilroy-medium mt-2p f-16 theme-usd">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['amount'], $transInfo['currency_id'])) }}</p>
                </div>
            </div>
        </div>
    </div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p trnsfer-details mt-4 r-mt-8 leading-26">
      {{ __('The recipient will be notified via an email or phone number after money has been successfully transferred to their account.') }}              
    </p>
    <div class="d-flex justify-content-center mt-28 r-mt-20">
        <a href="{{ route('user.send_money.print', $transInfo['trans_id']) }}" class="print-btn d-flex justify-content-center align-items-center gap-10 b-none" target="_blank">
            {!! svgIcons('printer') !!}
            <span>{{ __('Print') }}</span>
        </a>

        <a href="{{ route('user.send_money.create') }}" class="bg-white repeat-btn d-flex justify-content-center align-items-center ml-20">
            <span class="gilroy-medium">{{ __('Send Again') }}</span>
        </a>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/dist/js/lottie-player.min.js') }}"></script>
@endpush