
@extends('gateways.layouts.master')

@section('content')


    <form action="" id="payuform" method="POST" name="payuform">
        <input name="key" type="hidden" value=""/>
        <input name="hash" type="hidden" value=""/>
        <input name="txnid" type="hidden" value=""/>
        <input name="amount" type="hidden" value=""/>
        <input id="email" name="email" type="hidden" value=""/>
        <input id="firstname" name="firstname" type="hidden" value=""/>
        <input name="productinfo" type="hidden" value=""/>
        <input name="surl" size="64" type="hidden" value=""/>
        <input name="furl" size="64" type="hidden" value=""/>
        <input name="service_provider" type="hidden" value=""/>
    </form>

    <div class="col-md-12">
        <p class="text-danger" id="payuMoneyError"></p>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-grid mt-3p">
                <button type="submit" class="btn btn-lg btn-primary" type="submit" id="payumoney-button">
                    <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                        <span class="visually-hidden"></span>
                    </div>
                    <span id="payumoneySubmitBtnText" class="px-1">{{ __('Pay with :x', ['x' => ucfirst($gateway)]) }}</span>
                </button>
            </div>
        </div>
    </div>


@endsection

@section('js')

<script src="{{ asset('public/dist/libraries/jquery-3.6.1/jquery-3.6.1.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<script>
    "use strict";
    var submitText = "{{ __('Submitting...') }}";
    var paymentUrl = "{{ route('gateway.confirm_payment')}}";
    var token = "{{ csrf_token() }}";
    var requiredText = "{{ __('This field is required.') }}";
    var redirect_url = "{{ $redirectUrl }}";
    var cancel_url = "{{ $cancel_url }}";
    var amount ="{{ $total }}";
    var currency_id = "{{ $currency_id }}";
    var payment_type = "{{ $payment_type }}";
    var payment_method_id ="{{ $payment_method }}";
    var transaction_type = "{{ $transaction_type }}";
    var params = '{{ $params }}'

</script>

<script src="{{ asset('public/frontend/customs/js/gateways/payumoney.min.js') }}"></script>

@endsection










