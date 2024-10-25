@extends('gateways.layouts.master')

@section('content')

<form id="payment-form" method="POST">
    {{ csrf_field() }}

    <div id="payment-element">
        <!-- Elements will create form elements here -->
    </div>

    <input type="hidden" name="currency_id" id="currency_id" value="{{ $currency_id }}">
    <input type="hidden" name="amount" id="amount" value="{{ $total }}">
    <input type="hidden" name="payment_method_id" id="payment_method_id" value="{{ $payment_method }}">
    <input type="hidden" name="transaction_type" id="transaction_type" value="{{ $transaction_type }}">
    <input type="hidden" name="payment_type" id="payment_type" value="{{ $payment_type }}">

    <div class="row">
        <div class="col-12">
            <div class="d-grid mt-2">
                <button type="submit" class="btn btn-lg btn-primary" type="submit" id="stripeSubmitBtn">
                    <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                        <span class="visually-hidden"></span>
                    </div>
                    <span id="stripeSubmitBtnText" class="px-1">{{ __('Confirm Payment') }}</span>
                </button>
            </div>
        </div>
    </div>


</form>

@endsection

@section('js')
    <script src="https://js.stripe.com/v3/"></script>




<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    'use strict';
    var pretext = $("#deposit-stripe-submit-btn-txt").text();
    var paymentIntendId = null;
    var paymentMethodId = null;
    var redirect_url = "{{ $redirectUrl }}";
    var verifyUrl = "{{ url('gateway/payment-verify/stripe') }}";
    var csrfToken = "{{ csrf_token() }}";
    var stripeUrl = "{{ route('gateway.confirm_payment')}}";
    var submitting = "{{ __('Submitting...') }}";
    let stripeSubmitBtnText = "{{ __('Confirming...') }}";
    var params = '{{ $params }}';
    var uuid = "{{ $uuid }}";
    var confirmText = "{{ __('Confirm Payment') }}";
    var paymentIntendKey = "{{ $paymentIntent }}";
    var publishableKey = "{{ $publishableKey }}";
    var lang = '{{ app()->getLocale() }}';

</script>

<script src="{{ asset('public/frontend/customs/js/gateways/stripe.min.js') }}"></script>

@endsection



