@extends('merchantPayment.layouts.app')

@section('content')
    <div class="section-payment">
        <div class="payment-main-module">
            <h3>{{ __('Suspended!') }}</h3>
            <p>{{ $message }}</p>
            <div class="btn-tryagin d-flex justify-content-center align-items-center">
                <a href="{{ url('login') }}" class="btn btn-lg btn-light">{{ __('Login') }}</a>
            </div>
        </div>
    </div>
@endsection