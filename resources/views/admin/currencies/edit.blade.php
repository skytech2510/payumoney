@extends('admin.layouts.master')
@section('title', __('Edit Currency'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
@endsection

@section('page_content')
    <!-- Main content -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Edit Currency') }}</h3>
                </div>
                <form action="{{ url(config('adminPrefix') . '/settings/edit_currency', $currency->id) }}" method="POST" class="form-horizontal" enctype="multipart/form-data" id="edit_currency_form">
                    @csrf
                    <input type="hidden" name="default_currency" value="{{ $currency->default }}">
                    <input type="hidden" value="{{ $currency->allowed_wallet_creation }}" name="allowed_wallet_creation" id="allowed_wallet_creation">
                    <input type="hidden" value="{{ $currency->id }}" name="currency_id" id="currency_id">
                    <div class="box-body">
                        <!-- Name -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="name" class="form-control f-14 f-14" value="{{ $currency->name }}" placeholder="{{ __('Name') }}" id="name">
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>
                        </div>
                        <!-- Code -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="code">{{ __('Code') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="code" class="form-control f-14 f-14" value="{{ $currency->code }}" placeholder="{{ __('Code') }}" id="code">
                                <span class="text-danger">{{ $errors->first('code') }}</span>
                            </div>
                        </div>
                        <!-- Symbol -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="symbol">{{ __('Symbol') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="symbol" class="form-control f-14 f-14" value="{{ $currency->symbol }}" placeholder="{{ __('Symbol') }}" id="symbol">
                                <span class="text-danger">{{ $errors->first('symbol') }}</span>
                            </div>
                        </div>
                        <!-- Currency Type -->
						<div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="type">{{ __('Type') }}</label>
                            <div class="col-sm-6">
								<select class="form-control f-14 type select2" name="type" id="type">
									<option value='fiat' {{ $currency->type == 'fiat' ? 'selected' : '' }}>{{ __('Fiat') }}</option>
									<option value='crypto' {{ $currency->type == 'crypto' ? 'selected' : '' }}>{{ __('Crypto') }}</option>
								</select>
								<span class="text-danger">{{ $errors->first('type') }}</span>
                            </div>
                        </div>
                        <!-- Address -->
                        <div id="address_div">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="address">{{ __('Merchant Address') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="address" class="form-control f-14 f-14" value="{{ $currency->address }}" placeholder="{{ __('Currency crypto address') }}" id="address">
                                    <span class="text-danger">{{ $errors->first('address') }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Exchange Rate -->
                        <div id="exchange_rate_div">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end mt-11" for="rate">{{ __('Exchange Rate') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="rate" class="form-control f-14 f-14" value="{{ (float) $currency->rate }}" placeholder="{{ __('Rate') }}" id="rate" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                                    <span class="text-danger">{{ $errors->first('rate') }}</span>
                                    <div class="clearfix"></div>
                                    <small class="form-text text-muted f-12"><strong>{{ __('*Allowed upto 8 decimal places.') }}</strong></small>
                                </div>
                            </div>
                        </div>
                        <!-- Logo -->
                        <div class="form-group row">
                            <label for="logo" class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11">{{ __('Logo') }}</label>
                            <div class="col-sm-6">
                                <input type="file" name="logo" class="form-control f-14 input-file-field" data-rel="{{ isset($currency->logo) ? $currency->logo : '' }}" id="logo" value="{{ isset($currency->logo) ? $currency->logo : '' }}">
                                <span class="text-danger">{{ $errors->first('logo') }}</span>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ allowedImageDimension(64, 64) }}</strong></small>
                                <div class="setting-img">
                                    <img src='{{ image($currency->logo, 'currency') }}' width="64" height="64" id="{{ !empty($currency->logo) ? 'currency-logo-preview' : 'currency-demo-logo-preview'  }}">
                                    {!! !empty($currency->logo) ? '<span class="remove_currency_preview"></span>' : '' !!}
                                </div>
                            </div>
                        </div>
                        <!-- Allowed wallet creation -->
                        <div id="allowed_wallet_creation_div">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="create_wallet">{{ __('Create Wallet') }}</label>
                                <div class="col-sm-6">
                                    <input type="checkbox" data-toggle="toggle" name="create_wallet" id="create_wallet">
                                    <div class="clearfix"></div>
                                    <small class="form-text text-muted f-12"><strong>{{ __('*If On, ') }}<span class="network-name"></span> {{ __('wallet will be created for all registered users.') }}</strong></small>
                                </div>
                            </div>
                        </div>
                        <!-- Exchange From -->
                        <div id="exchange_from_div">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="exchange_from">{{ __('Exchange From') }}</label>
                                <div class="col-sm-6">
                                    <select class="form-control f-12 exchange_from select2" name="exchange_from" id="exchange_from">
                                        <option value='local' {{ isset($currency->exchange_from) && $currency->exchange_from == 'local' ? 'selected' : '' }}>{{ __('Local') }}</option>
                                        <option value='api' {{ isset($currency->exchange_from) && $currency->exchange_from == 'api' ? 'selected' : '' }}>{{ __('API') }}</option>
                                    </select>
                                    <small class="rate-setup-text text-muted f-12 d-none" role='button'><strong><span class="text-danger">{{ __('Setup Currency Converter') }}</span> <a href=""data-bs-toggle="modal" data-bs-target="#currency-converter">{{ __('Click here.') }}</a></strong></small>
                                    <span class="text-danger">{{ $errors->first('exchange_from') }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Status -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end mt-11" for="status">{{ __('Status') }}</label>
                            <div class="col-sm-6">
                                @if ($currency->default == 1)
                                    <p class="f-14 mb-0"><span class="label label-danger f-11">{{ __('Staus Change Disallowed') }}</span></p>
                                    <p><span class="label label-warning f-11">{{ __('Default Currency') }}</span></p>
                                @else
                                    <select class="form-control f-14 status" name="status" id="status">
                                        <option value='Active' {{ $currency->status == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value='Inactive' {{ $currency->status == 'Inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                    </select>
                                    <span class="text-danger">{{ $errors->first('status') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 offset-md-3">
                                <a class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix') . '/settings/currency') }}">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-theme f-14" id="currency-edit-submit-btn">
                                    <i class="fa fa-spinner fa-spin d-none"></i> <span id="currency-edit-submit-btn-text">{{ __('Update') }}</span>
                                </button>
                            </div>
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
                        <p class="f-18">{{ __('Set up any currency converter api') }}</p>
                        <p class="blue-text f-13">{{ __('Currency Converter Api') }}</p>
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

    <!-- jquery.validate -->
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.js') }}" type="text/javascript"></script>
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

        let checkAllowedWalletCreation = $('#allowed_wallet_creation').val();
        if(checkAllowedWalletCreation == 'Yes') {
            $('#create_wallet').prop('checked', true).change();
        }

        function restrictNumberToPrefdecimalOnInput(e)
        {
            var type = $('select#type').find(':selected').val()
            restrictNumberToPrefdecimal(e, type);
        }

        function exchangeRateFrom(type) {
            if (type == 'crypto') {
                $('#exchange_from_div, #exchange_rate_div').css('display', 'none');
                $('#address_div').css('display', 'block');
                $('#allowed_wallet_creation_div').css('display', 'block');
            } else if (type == 'fiat') {
                $('#exchange_from_div, #exchange_rate_div').css('display', 'block');
                $('#address_div').css('display', 'none');
                $('#allowed_wallet_creation_div').css('display', 'block');
            }
        }

        $(window).on('load', function() {
            exchangeRateFrom($('#type').find('option:selected').val());
        });

        $('#type').on('change', function() {
            exchangeRateFrom($(this).find('option:selected').val());
        });

        $(document).on('change', '#logo', function() {
            let orginalSource = '{{ url('public/user_dashboard/images/favicon.png') }}';
            let logo = $('#logo').attr('data-rel');
            if (logo != '') {
                readFileOnChange(this, $('#currency-logo-preview'), orginalSource);
                $('.remove_currency_preview').remove();
            }
            readFileOnChange(this, $('#currency-demo-logo-preview'), orginalSource);
        });

        $('#exchange_from').on('change', function() {
            var exchangeFrom = $(this).find('option:selected').val();
            if (exchangeFrom == 'api') {
                var exchangeEnabledApi = "{{ settings('exchange_enabled_api') }}";
                var currencyConverterApiKey = "{{ settings('currency_converter_api_key') }}";
                var exchangeRateApiKey = "{{ settings('exchange_rate_api_key') }}";

                if (exchangeEnabledApi != 'Disabled' && ((exchangeEnabledApi == 'currency_converter_api_key' && currencyConverterApiKey != '') || ( exchangeEnabledApi == 'exchange_rate_api_key' && exchangeRateApiKey != ''))) {
                    $('#currency-edit-submit-btn').attr('disabled', false);
                } else {
                    $('#currency-edit-submit-btn').attr('disabled', true);
                    $('.rate-setup-text').removeClass('d-none');
                }
            } else if (exchangeFrom == 'local') {
                $('.rate-setup-text').addClass('d-none');
                $('#currency-edit-submit-btn').attr('disabled', false);
            }
        });

        $(document).on('click', '.remove_currency_preview', function() {
            var image = $('#logo').attr('data-rel');
            var currency_id = $('#currency_id').val();

            if (image) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "POST",
                    url: SITE_URL + "/" + ADMIN_PREFIX + "/settings/currency/delete-currency-logo",
                    data: {
                        'logo': image,
                        'currency_id': currency_id,
                    },
                    dataType: 'json',
                    success: function(reply) {
                        if (reply.success == 1) {
                            swal({
                                    title: "Deleted!",
                                    text: reply.message,
                                    type: "success"
                                },
                                function() {
                                    location.reload();
                                }
                            );
                        } else {
                            alert(reply.message);
                            location.reload();
                        }
                    }
                });
            }
        });

        $.validator.setDefaults({
            highlight: function(element) {
                $(element).parent('div').addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).parent('div').removeClass('has-error');
            },
        });

        $('#edit_currency_form').validate({
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
                address: {
                    required: true,
                },
                rate: {
                    required: true,
                    number: true,
                    min: 0.0001,
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
                },
            },
            submitHandler: function(form) {
                $("#currency-edit-submit-btn").attr("disabled", true);
                $('#cancel-link').attr("disabled", true);
                $(".fa-spin").removeClass("d-none");
                $("#currency-edit-submit-btn-text").text('Updating...');
                $('#currency-edit-submit-btn').click(function(e) {
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
