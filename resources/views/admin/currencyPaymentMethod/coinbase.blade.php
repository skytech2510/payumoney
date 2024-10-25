<!-- coinPayments - Merchant Id -->
<div class="form-group row">
    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end mt-11 f-14 fw-bold text-md-end" for="coinpayments[merchant_id]">{{ __('API Key') }}</label>
    <div class="col-sm-6">
        <input class="form-control f-14 coinbase[api_key]" name="coinbase[api_key]" type="text" placeholder="{{ __('Coinbase API Key') }}" value="{{ isset($currencyPaymentMethod->method_data) ? json_decode($currencyPaymentMethod->method_data)->api_key : '' }}" id="coinbase_api_key">
        @if ($errors->has('coinbase[api_key]'))
        <span class="help-block">
            <strong>{{ $errors->first('coinbase[api_key]') }}</strong>
        </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end mt-11" for="webhook_url">{{ __('Webhook URL') }}</label>
    <div class="col-sm-6">
        <div class="d-flex justify-content-between">
            <input name="webhook_url" class="form-control f-14 coinpayments_ipn_url" type="text" readonly value="{{ url('gateway/payment-verify/coinbase') }}" id="webhook_url">
            <button class="btn btn-md btn-primary coin-copy f-14" id="coinbase_commerce_copy_button">
                {{ __('Copy') }}
            </button>
        </div>

        <small class="text-color f-12"><strong>{!! __('Copy the above url and set it in :x field in webook subsciption panel.', ['x' => '<a href="https://commerce.coinbase.com/settings/notifications">'. __('Coinbase Commerces settings notification page') .'</a>']) !!}</strong></small>

    </div>
</div>

<div class="clearfix"></div>
<script>
    "use strict";
    var coinbaseCopyText = "{{ __('Copied!') }}";
    var linkCopiedText = "{{ __('Webhook url link Copied.') }}";
</script>
