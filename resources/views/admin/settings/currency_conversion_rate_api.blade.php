@extends('admin.layouts.master')
@section('title', __('Currency Conversion Rate Api'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/css/select2.min.css')}}">
@endsection


@section('page_content')

    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border text-center">
                    <h3 class="box-title"> {{ __('Fiat Conversion Api') }}</h3>
                </div>

                <form action="{{ url(config('adminPrefix').'/settings/currency-conversion-rate-api') }}" method="post" class="form-horizontal" id="CurrencyConversionRateApi" >
                    {!! csrf_field() !!}

                    <!-- box-body -->
                    <div class="box-body">

                        <!-- Exchange enabled api -->
                        <div class="form-group row align-items-center">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="exchange_enabled_api">{{ __('Enabled Api') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-14 exchange_enabled_api select2" name="exchange_enabled_api" id="exchange_enabled_api">
                                    <option value='Disabled' {{ $currencyExchangeApi['exchange_enabled_api'] == 'Disabled' ? 'selected':""}}>{{ __('Disabled') }}</option>
                                    <option value='currency_converter_api_key' {{ $currencyExchangeApi['exchange_enabled_api'] == 'currency_converter_api_key' ? 'selected':""}}>{{ __('Currency Converter Api') }}</option>
                                    <option value='exchange_rate_api_key' {{ $currencyExchangeApi['exchange_enabled_api'] == 'exchange_rate_api_key' ? 'selected':""}}>{{ __('Exchange Rate Api') }}</option>

                                </select>
                                @if($errors->has('exchange_enabled_api'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('exchange_enabled_api') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Currency converter api -->
                        <div class="form-group row align-items-center">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="currency_converter_api_key">{{ __('Currency Converter Api') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="currency_converter_api_key" class="form-control f-14 exchange-api" value="{{ old('currency_converter_api_key', $currencyExchangeApi['currency_converter_api_key']) }}" placeholder="{{ __('currency converter api key') }}" id="currency_converter_api_key">
                                @if($errors->has('currency_converter_api_key'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('currency_converter_api_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <!-- Exchange rate api -->
                        <div class="form-group row align-items-center">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="exchange_rate_api_key">{{ __('Exchange rate Api') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="exchange_rate_api_key" class="form-control f-14 exchange-api" value="{{ old('exchange_rate_api_key',  $currencyExchangeApi['exchange_rate_api_key']) }}" placeholder="{{ __('Exchange rate api key') }}" id="exchange_rate_api_key">
                                @if($errors->has('exchange_rate_api_key'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('exchange_rate_api_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="box-header text-center">
                        <h3 class="box-title">{{ __('Crypto Conversion Api') }}</h3>
                    </div>

                    <div class="box-body">

                        <!-- Crypto enabled api -->
                        <div class="form-group row align-items-center">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="crypto_compare_enabled_api">{{ __('Enabled Api') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-14 crypto-compare-enabled-api select2" name="crypto_compare_enabled_api" id="crypto_compare_enabled_api">
                                    <option value='Enabled' {{ $cryptoCompareApi['crypto_compare_enabled_api'] == 'Enabled' ? 'selected':""}}>{{ __('Enabled') }}</option>
                                    <option value='Disabled' {{ $cryptoCompareApi['crypto_compare_enabled_api'] == 'Disabled' ? 'selected':""}}>{{ __('Disabled') }}</option>
                                </select>
                                @if($errors->has('crypto_compare_enabled_api'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('crypto_compare_enabled_api') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Currency converter api -->
                        <div class="form-group row align-items-center">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="crypto_compare_api_key">{{ __('Cryptocompare API') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="crypto_compare_api_key" class="form-control f-14 exchange-api" value="{{ old('crypto_compare_api_key', $cryptoCompareApi['crypto_compare_api_key']) }}" placeholder="{{ __('Crypto compare api key') }}" id="crypto_compare_api_key">
                                @if($errors->has('crypto_compare_api_key'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('crypto_compare_api_key') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- /.box-body -->

                    <!-- box-footer -->
                    @if(Common::has_permission(auth('admin')->user()->id, 'edit_conversion_rate_api'))
                        <div class="box-footer">
                            <button class="btn btn-theme pull-right f-14" type="submit">{{ __('Submit') }}</button>
                        </div>
                    @endif
                    <!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/js/additional-methods.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/js/select2.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

    $(window).on('load', function()
    {
        $(".exchange_enabled_api").select2({});
    });

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    $('#CurrencyConversionRateApi').validate({
        rules: {
            currency_converter_api_key: {
                require_from_group: function() {
                    var exchange_enabled_api = $( '#exchange_enabled_api option:selected' ).val();
                    if( exchange_enabled_api == "Disabled" ) {
                        return [0, ".exchange-api"];
                    } else {
                        return [1, ".exchange-api"];
                    }
                }
            },
            exchange_rate_api_key: {
                require_from_group: function() {
                    var exchange_enabled_api = $( '#exchange_enabled_api option:selected' ).val();
                    if( exchange_enabled_api == "Disabled" ) {
                        return [0, ".exchange-api"];
                    } else {
                        return [1, ".exchange-api"];
                    }
                }
            },
            crypto_compare_api_key: {
                required : function() {
                    var action = $( '#crypto_compare_enabled_api option:selected' ).val();
                    if(action == "Enabled") {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        },
    });

</script>

@endpush
