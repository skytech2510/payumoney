<!-- Payeer - Merchant Id -->
<div class="form-group row">
    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end" for="payeer_merchant_id">{{ __('Merchant Id') }} </label>
    <div class="col-sm-6">
        <input class="form-control f-14 payeer[merchant_id]" name="payeer[merchant_id]" type="text" placeholder="{{ __('Payeer Merchant Id') }}"
        value="{{ isset($currencyPaymentMethod->method_data) ? json_decode($currencyPaymentMethod->method_data)->merchant_id : '' }}" id="payeer_merchant_id">
        @if ($errors->has('payeer[merchant_id]'))
            <span class="help-block">
                <strong>{{ $errors->first('payeer[merchant_id]') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="clearfix"></div>

<!-- Payeer - Secret Key -->
<div class="form-group row">
    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end" for="payeer_secret_key">{{ __('Secret Key') }}</label>
    <div class="col-sm-6">
        <input class="form-control f-14 payeer[secret_key]" name="payeer[secret_key]" type="text" placeholder="{{ __('Payeer Merchant Secret Key') }}"
        value="{{ isset($currencyPaymentMethod->method_data) ? json_decode($currencyPaymentMethod->method_data)->secret_key : '' }}" id="payeer_secret_key">
        @if ($errors->has('payeer[secret_key]'))
            <span class="help-block">
                <strong>{{ $errors->first('payeer[secret_key]') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="clearfix"></div>

<!-- Payeer - Encryption Key -->
<div class="form-group row">
    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end" for="payeer_encryption_key">{{ __('Encryption Key') }}</label>
    <div class="col-sm-6">
        <input class="form-control f-14 payeer[encryption_key]" name="payeer[encryption_key]" type="text" placeholder="{{ __('Payeer Merchant Encryption Key') }}"
        value="{{ isset($currencyPaymentMethod->method_data) ? json_decode($currencyPaymentMethod->method_data)->encryption_key : '' }}" id="payeer_encryption_key">
        @if ($errors->has('payeer[encryption_key]'))
            <span class="help-block">
                <strong>{{ $errors->first('payeer[encryption_key]') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="clearfix"></div>

<!-- Payeer - Domain -->
<div class="form-group row">
    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end" for="payeer_merchant_domain">{{ __('Merchant Domain') }}</label>
    <div class="col-sm-6">
        <input class="form-control f-14 payeer[merchant_domain]" name="payeer[merchant_domain]" type="text" placeholder="{{ __('Payeer Merchant Domain') }}" value="" id="payeer_merchant_domain">
        @if ($errors->has('payeer[merchant_domain]'))
            <span class="help-block">
                <strong>{{ $errors->first('payeer[merchant_domain]') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="clearfix"></div>
