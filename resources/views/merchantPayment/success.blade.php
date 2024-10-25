@extends('merchantPayment.layouts.app')

@section('content')
<div class="section-payment">
    <div class="payment-main-module">
        <div class="d-flex justify-content-center position-relative h-44 mb-30">
        <lottie-player class="success-anim" src="{{ asset('public/user/templates/animation/confirm.json') }}" background="transparent" speed="1" autoplay></lottie-player>
        </div>
        <h3>{{ __('Success!') }}</h3>
        <p>{{ __('Merchant Payment Successfull') }}</p>

        <div class="btn-tryagin d-flex justify-content-center align-items-center">
            <a href="{{ url('dashboard') }}" class="btn btn-lg btn-light">{{ __('Home') }}</a>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script src="{{ asset('public/user/templates/animation/lottie-player.min.js') }}"></script>
@endsection
