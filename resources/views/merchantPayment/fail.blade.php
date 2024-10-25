@extends('merchantPayment.layouts.app')

@section('content')

@include('frontend.layouts.common.alert')
<div class="section-payment webview-fail">
    <div class="payment-main-module">
        <div class="d-flex justify-content-center align-items-center">
            <div class="status-logo">
                <img src="{{ asset(image('', 'fail')) }}">
            </div>
        </div>
        <h3>{{ __('Sorry!') }}</h3>
        <p>{{ __('Payment Unsuccessful') }}</p>
        @isset($message)
            <p class="text-danger"> {{ $message }}</p>
        @endisset
        <div class="btn-tryagin d-flex justify-content-center align-items-center">
            <a href="{{ url('/') }}" class="btn btn-lg btn-light">{{ __('Home') }}</a>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('public/user/customs/js/reactwebview.min.js') }}"></script>
@endpush


