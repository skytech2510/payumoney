
@extends('gateways.layouts.master')

@section('content')

    <form method="post" action="https://payeer.com/merchant/" id="payeer" style="display: none" accept-charset="UTF-8">
        <input type="hidden" name="m_shop" value="">
        <input type="hidden" name="m_orderid" value="">
        <input type="hidden" name="m_amount" value="">
        <input type="hidden" name="m_curr" value="">
        <input type="hidden" name="m_desc" value="">
        <input type="hidden" name="m_sign" value="">
        <input type="hidden" name="form[ps]" value="">
        <input type="hidden" name="form[curr[2609]]" value="">
        <input type="hidden" name="m_params" value="">
        <input type="hidden" name="m_cipher_method" value="">
        <input type="submit" name="m_process" id="payeer-submit-button" value="{{ __('Click here if you are not redirected automatically') }}" />
    </form>

    <div class="row">
        <div class="col-12">
            <div class="d-grid mt-3p">
                <button type="submit" class="btn btn-lg btn-primary" type="submit" id="payeer-button">
                    <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                        <span class="visually-hidden"></span>
                    </div>
                    <span id="payeerSubmitBtnText" class="px-1">{{ __('Pay with :x', ['x' => ucfirst($gateway)]) }}</span>
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
    var uuid = "{{ $uuid }}";
    var requiredText = "{{ __('This field is required.') }}";
    var redirect_url = "{{ $redirectUrl }}";
    var amount ="{{ $total }}";
    var currency_id = "{{ $currency_id }}";
    var payment_type = "{{ $payment_type }}";
    var payment_method_id ="{{ $payment_method }}";
    var transaction_type = "{{ $transaction_type }}";
    var params = '{{ $params }}'

</script>

<script src="{{ asset('public/frontend/customs/js/gateways/payeer.min.js') }}"></script>


@endsection









