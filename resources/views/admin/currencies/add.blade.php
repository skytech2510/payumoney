@extends('admin.layouts.master')
@section('title', __('Add Currency'))

@section('head_style')
    <link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
@endsection

@section('page_content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Add Currency') }}</h3>
                </div>
                <br>
                <form action="{{ url(config('adminPrefix') . '/settings/add_currency') }}" method="post" class="form-horizontal" enctype="multipart/form-data" id="add_currency_form">
                    @csrf
                    <!-- Name -->
                    <div class="form-group row">
                        <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label" for="name">{{ __('Name') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="name" class="form-control f-14" value="{{ old('name') }}" placeholder="{{ __('Name') }}" id="name">
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        </div>
                    </div>
                    <!-- Code -->
                    <div class="form-group row">
                        <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label" for="code">{{ __('Code') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="code" class="form-control f-14" value="{{ old('code') }}" placeholder="{{ __('Code') }}" id="code">
                            <span class="text-danger">{{ $errors->first('code') }}</span>
                        </div>
                    </div>
                    <!-- Symbol -->
                    <div class="form-group row">
                        <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label" for="symbol">{{ __('Symbol') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="symbol" class="form-control f-14" value="{{ old('symbol') }}" placeholder="{{ __('Symbol') }}" id="symbol">
                            <span class="text-danger">{{ $errors->first('symbol') }}</span>
                        </div>
                    </div>
					<!-- Type -->
					<div class="form-group row">
                        <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label" for="type">{{ __('Type') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 type select2" name="type" id="type">
                                <option {{ old('type') == 'fiat' ? 'selected' : '' }} value='fiat'>{{ __('Fiat') }}</option>
                                <option {{ old('type') == 'crypto' ? 'selected' : '' }} value='crypto'>{{ __('Crypto') }}</option>
                            </select>
                        </div>
                    </div>
                     <!-- Address -->
                     <div class="form-group row" id="address_div">
                        <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label" for="address">{{ __('Merchant Address') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="address" class="form-control f-14" value="{{ old('address') }}" placeholder="{{ __('Currency crypto address') }}" id="address">
                            <span class="text-danger">{{ $errors->first('address') }}</span>
                        </div>
                    </div>
                    <!-- Exchange Rate -->
                    <div id="exchange_rate_div">
                        <div class="form-group row">
                            <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label mt-11" for="rate">{{ __('Exchange Rate') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="rate" class="form-control f-14" value="{{ old('rate') }}" placeholder="{{ __('Rate') }}" id="rate"
                                onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                                <span class="text-danger">{{ $errors->first('rate') }}</span>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12">
                                    <strong>{{ __('*Exchagne rate should be equivalent to default currency (allowed upto 8 decimal places).') }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="form-group row">
                        <label for="currency-logo" class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label mt-11">{{ __('Logo') }}</label>
                        <div class="col-sm-6">
                            <input type="file" name="logo" class="form-control f-14 input-file-field" id="currency-logo">
                            <div class="clearfix"></div>
                            <small class="form-text text-muted f-12"><strong>{{ allowedImageDimension(64, 64) }}</strong></small>
                            <div class="setting-img">
                                <img src='{{ image(null, 'currency') }}' class="img-w64" id="currency-demo-logo-preview">
                            </div>
                        </div>
                    </div>

                    <!-- Exchange From -->
                    <div id="exchange_from_div">
                        <div class="form-group row">
                            <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label" for="exchange_from">{{ __('Exchange From') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-12 exchange_from select2" name="exchange_from" id="exchange_from">
                                    <option {{ old('exchange_from') == 'local' ? 'selected' : '' }} value='local'>{{ __('Local') }}</option>
                                    <option {{ old('exchange_from') == 'api' ? 'selected' : '' }} value='api'>{{ __('API') }}</option>
                                </select>
                                <small class="rate-setup-text text-muted f-12 d-none" role='button'><strong><span class="text-danger">{{ __('Setup Currency Converter') }}</span> <a href=""data-bs-toggle="modal" data-bs-target="#currency-converter">{{ __('Click here.') }}</a></strong></small>
                            </div>
                        </div>
                    </div>


					<!-- Status -->
                    <div class="form-group row">
                        <label class="f-14 mt-11 fw-bold text-sm-end col-sm-3 control-label" for="status">{{ __('Status') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14 select2" name="status" id="status">
                                <option {{ old('status') == 'Active' ? 'selected' : '' }} value='Active'>{{ __('Active') }}</option>
                                <option {{ old('status') == 'Inactive' ? 'selected' : '' }} value='Inactive'>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 offset-md-3 pb-4">
                            <a class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix') . '/settings/currency') }}">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-theme f-14" id="currency-add-submit-btn">
                                <i class="fa fa-spinner fa-spin d-none"></i>
                                <span id="currency-add-submit-btn-text">{{ __('Submit') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="currency-converter" class="modal fade display_none" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content pB-27 M-50">
                <div class="modal-header header-background">
                    <h3 class="header-text f-24">{{ __('How to set up currrency conveter') }}</h3>
                </div>
                <div class="modal-body p-4 ps-5">
                    <div class="text-strong">
                        <p class="f-14">{{ __('Set up any currency converter api') }}</p>
                        <p class="blue-text f-18">{{ __('Currency Converter Api') }}</p>
                        <ul class="f-14">
                            <li><strong>{{ __('URL') }}: </strong><a target="_blank" href="//free.currencyconverterapi.com">free.currencyconverterapi.com</a>
                            <li><strong>{{ __('Api Key') }}:</strong> {{ __('Get an api key from URL') }}</li>
                            <li>{{ __('After that set the value in the Api Key field.') }}</li>
                        </ul>
                        <p class="blue-text f-18">{{ __('Exchange Rate Api') }}</p>
                        <ul class="f-14">
                            <li><strong>{{ __('URL') }}: </strong><a target="_blank" href="//exchangerate-api.com/">exchangerate-api.com</a>
                            <li><strong>{{ __('Api Key') }}:</strong>{{ __('Get an api key from URL') }}</li>
                            <li>{{ __('After that set the value in the Api Key field.') }}</li>
                        </ul>
                        <a target="_blank" href="{{ url(config('adminPrefix') . '/settings/currency-conversion-rate-api') }}" class="currency-converter-link f-14">{{ __('Click this for Currency Converter Setup') }}</a>
                    </div>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-primary custom-btn-small f-14 float-end mt-2 me-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')

    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script>

    @include('common.read-file-on-change')
    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')

    <script type="text/javascript">

        $(window).on('load', function(){
            var type = $('select#type').find(':selected').val();
            if (type == 'crypto') {
                $('#exchange_from_div, #exchange_rate_div').css('display', 'none');
                $('#address_div').css('display', 'block');
            } else if (type == 'fiat') {
                $('#exchange_from_div, #exchange_rate_div').css('display', 'block');
                $('#address_div').css('display', 'none');
            }
        });

        $(document).on('change', '#currency-logo', function() {
            let orginalSource = '{{ url('public/user_dashboard/images/favicon.png') }}';
            readFileOnChange(this, $('#currency-demo-logo-preview'), orginalSource);
        });

        function restrictNumberToPrefdecimalOnInput(e)
        {
            var type = $('select#type').find(':selected').val()
            restrictNumberToPrefdecimal(e, type);
        }

        $.validator.setDefaults({
            highlight: function(element) {
                $(element).parent('div').addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).parent('div').removeClass('has-error');
            },
        });

        $('#type').on('change', function() {
            var type = $(this).find('option:selected').val();
            if (type == 'crypto') {
                $('#exchange_from_div, #exchange_rate_div').css('display', 'none');
                $('#address_div').css('display', 'flex');
            } else if (type == 'fiat') {
                $('#exchange_from_div, #exchange_rate_div').css('display', 'block');
                $('#address_div').css('display', 'none');
            }
        });

        $('#exchange_from').on('change', function() {
            var exchangeFrom = $(this).find('option:selected').val();
            if (exchangeFrom == 'api') {
                var exchangeEnabledApi = "{{ settings('exchange_enabled_api') }}";
                var currencyConverterApiKey = "{{ settings('currency_converter_api_key') }}";
                var exchangeRateApiKey = "{{ settings('exchange_rate_api_key') }}";

                if (exchangeEnabledApi != 'Disabled' && ((exchangeEnabledApi == 'currency_converter_api_key' && currencyConverterApiKey != '') || ( exchangeEnabledApi == 'exchange_rate_api_key' && exchangeRateApiKey != ''))) {
                    $('#currency-add-submit-btn').attr('disabled', false);
                } else {
                    $('#currency-add-submit-btn').attr('disabled', true);
                    $('.rate-setup-text').removeClass('d-none');
                }
            } else if (exchangeFrom == 'local') {
                $('.rate-setup-text').addClass('d-none');
                $('#currency-add-submit-btn').attr('disabled', false);
            }
        });

        $('#add_currency_form').validate({
            rules: {
                name: {
                    required: true,
                },
                code: {
                    required: true,
                },
                symbol: {
                    required: true,
                },
                type: {
                    required: true,
                },
                rate: {
                    required: true,
                    number: true,
                    min: 0.0001,
                },
                address: {
                    required: true,
                },
                logo: {
                    extension: "png|jpg|jpeg|gif|bmp",
                },
            },
            messages: {
                rate: {
                    min: "Please enter values greater than 0.0001!"
                },
                logo: {
                    extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
                }
            },
            submitHandler: function(form) {
                $("#currency-add-submit-btn").attr("disabled", true);
                $('#cancel-link').attr("disabled", true);
                $(".fa-spin").removeClass("d-none");
                $("#currency-add-submit-btn-text").text('Submitting...');
                $('#currency-add-submit-btn').click(function(e) {
                    e.preventDefault();
                });
                $('#cancel-link').click(function(e) {
                    e.preventDefault();
                });
                form.submit();
            }
        });
    </script>

@endpush
