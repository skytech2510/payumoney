<!-- paypal - Client ID -->
<div class="form-group row">
	<label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end" for="paypal_client_id">{{ __('Client ID') }}</label>
	<div class="col-sm-6">
		<input class="form-control f-14" name="paypal[client_id]" type="text" placeholder="{{ __('PayPal Client ID') }}"
		value="{{ isset($currencyPaymentMethod->method_data) ? json_decode($currencyPaymentMethod->method_data)->client_id : '' }}" id="paypal_client_id">

		@if ($errors->has('paypal[client_id]'))
			<span class="help-block">
				<strong>{{ $errors->first('paypal[client_id]') }}</strong>
			</span>
		@endif
	</div>
</div>
<div class="clearfix"></div>

<!-- paypal - Client Secret -->
<div class="form-group row">
	<label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end" for="paypal_client_secret">{{ __('Client Secret') }}</label>
	<div class="col-sm-6">
		<input class="form-control f-14" name="paypal[client_secret]" type="text" placeholder="{{ __('PayPal Client Secret') }}"
		value="{{ isset($currencyPaymentMethod->method_data) ? json_decode($currencyPaymentMethod->method_data)->client_secret : '' }}" id="paypal_client_secret">
		@if ($errors->has('paypal[client_secret]'))
			<span class="help-block">
				<strong>{{ $errors->first('paypal[client_secret]') }}</strong>
			</span>
		@endif
	</div>
</div>

<!-- paypal - Mode -->
<div class="form-group row">
	<label class="col-sm-3 control-label mt-11 f-14 fw-bold text-md-end" for="paypal_mode">{{ __('Mode') }}</label>
	<div class="col-sm-6">
		<select class="form-control f-14" name="paypal[mode]" id="paypal_mode">
			<option value="">{{ __('Select Mode') }}</option>
			<option value='sandbox' {{ isset($currencyPaymentMethod->method_data) && (json_decode($currencyPaymentMethod->method_data)->mode) == 'sandbox' ? 'selected':"" }} >{{ __('sandbox') }}</option>
			<option value='live' {{ isset($currencyPaymentMethod->method_data) && (json_decode($currencyPaymentMethod->method_data)->mode) == 'live' ? 'selected':"" }} >{{ __('live') }}</option>
		</select>
	</div>
</div>
<div class="clearfix"></div>
