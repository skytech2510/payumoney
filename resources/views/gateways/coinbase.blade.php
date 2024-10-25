

@extends('gateways.layouts.master')

@section('content')

<div class="col-md-12 text-center">
    <form  id="payment-form" method="POST" action="{{ route('gateway.confirm_payment')}}">
        @csrf
        <input type="hidden" name="currency_id" id="currency_id" value="{{ $currency_id }}">
        <input type="hidden" name="amount" id="amount" value="{{ $total }}">
        <input type="hidden" name="payment_method_id" id="payment_method_id" value="{{ $payment_method }}">
        <input type="hidden" name="transaction_type" id="transaction_type" value="{{ $transaction_type }}">
        <input type="hidden" name="payment_type" id="payment_type" value="{{ $payment_type }}">
        <input type="hidden" name="verify_url" id="verify_url" value="{{url('gateway/payment-verify/paypal') }}">
        <input type="hidden" name="redirect_url" id="redirect_url" value="{{ $redirectUrl }}">
        <input type="hidden" name="params"  value="{{ $params }}">
        <input type="hidden" name="uuid"  value="{{ $uuid }}">
        <input type="hidden" name="gateway" value="coinbase">


        <div class="col-md-12">
            <p class="text-danger" id="coinbaseError"></p>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="d-grid mt-3p">
                    <button type="submit" class="btn btn-lg btn-primary" type="submit" id="coinbase-button-submit">
                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                            <span class="visually-hidden"></span>
                        </div>
                        <span id="coinbaseSubmitBtnText" class="px-1">{{ __('Pay with :x', ['x' => ucfirst($gateway)]) }}</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>


@endsection

@section('js')

<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    'use strict';
    var submitting = "{{ __('Submitting...') }}";

</script>

<script src="{{ asset('public/frontend/customs/js/gateways/coinbase.min.js') }}"></script>

@endsection




