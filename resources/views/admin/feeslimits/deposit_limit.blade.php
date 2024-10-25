@extends('admin.layouts.master')
@section('title', __('Fees & Limits'))

@section('head_style')
    <!-- custom-checkbox -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/admin/customs/css/custom-checkbox.min.css') }}">
@endsection

@section('page_content')
    <div class="box box-default">
        <div class="box-body ps-2">
            <div class="row">
                <div class="col-md-12">
                    <div class="top-bar-title padding-bottom">{{ __('Fees Limits') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-body ps-3">

            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="dropdown pull-left">
                        <button class="btn btn-default dropdown-toggle f-14" type="button" data-bs-toggle="dropdown">{{ __('Currency') }} :
                            <span class="currencyName">{{ $currency->name }}</span>
                            <span class="caret"></span></button>
                        <ul class="dropdown-menu xss f-14 p-0">
                            @foreach ($currencyList as $currencyItem)
                                <li class="listItem px-2 py-1" data-type="{{ $currencyItem->type }}"  data-rel="{{ $currencyItem->id }}"
                                    data-default="{{ $currencyItem->default }}">
                                    <a class="px-2 py-1 d-block" href="#">{{ $currencyItem->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-md-2 offset-md-8 defaultCurrencyDiv dis-none">
                    <h4 class="form-control-static f-14 text-sm-end mb-0"><span class="label label-success f-14">{{ __('Default Currency') }}</span>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            @include('admin.common.currency_menu')
        </div>

        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">

                        @if ($list_menu == 'request_payment')
                            {{ ucwords(str_replace('_', ' ', $list_menu)) }} {{ __('Settings') }}
                        @elseif($list_menu == 'withdrawal')
                            {{ 'Payout Settings' }}
                        @else
                            {{ ucfirst($list_menu) }} {{ __('Settings') }}
                        @endif
                    </h3>
                </div>

                <form action='{{ url(config('adminPrefix') . '/settings/feeslimit/update-deposit-limit') }}'
                    class="form-horizontal" method="POST" id="deposit_limit_form">
                    {!! csrf_field() !!}

                    <input type="hidden" value="{{ $currency->id }}" name="currency_id" id="currency_id">
                    <input type="hidden" value="{{ $currency->type }}" name="type" id="type">
                    <input type="hidden" value="{{ $transaction_type }}" name="transaction_type" id="transaction_type">
                    <input type="hidden" value="{{ $list_menu }}" name="tabText" id="tabText">

                    <input type="hidden" value="{{ $currency->default }}" name="defaultCurrency" id="defaultCurrency">

                    <div class="box-body">
                        <div>
                            <div class="panel-group" id="accordion">
                                @foreach ($payment_methods as $key => $method)
                                    <input type="hidden" name="payment_method_id[]" value="{{ $method->id }}">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a data-bs-toggle="collapse" data-parent="#accordion"
                                                    href="#collapse{{ $method->id }}">
                                                    {{ isset($method->name) && $method->name == 'Mts' ? settings('name') : $method->name }}</a>
                                            </h4>
                                        </div>
                                        <div id="collapse{{ $method->id }}" class="panel-collapse collapse">
                                            <div class="panel-body">

                                                <!-- has_transaction -->
                                                <div class="form-group row">
                                                    <label class="col-sm-3 control-label f-14 fw-bold text-sm-end default_currency_label" for="has_transaction_{{ $method->id }}">{{ __('Is Activated') }}</label>
                                                    <div class="col-sm-5">
                                                        <label class="checkbox-container">
                                                            <input type="checkbox" class="has_transaction f-14"
                                                                data-method_id="{{ $method->id }}"
                                                                name="has_transaction[{{ $method->id }}]" value="Yes"
                                                                {{ isset($method->fees_limit?->has_transaction) && $method->fees_limit?->has_transaction == 'Yes' ? 'checked' : '' }}
                                                                {{ $currency->default == 1 ? 'disabled="disabled"' : ' ' }}
                                                                id="has_transaction_{{ $method->id }}">
                                                            <span class="checkmark"></span>
                                                        </label>

                                                        @if ($errors->has('has_transaction'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('has_transaction') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <p class="mb-0"><span class="default_currency_side_text f-14">{{ __('Default currency is always active') }}</span></p>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                <!-- Minimum Limit -->
                                                <div class="form-group row">
                                                    <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="min_limit_{{ $method->id }}">{{ __('Minimum Limit') }}</label>
                                                    <div class="col-sm-5">
                                                        <input class="form-control f-14 min_limit" name="min_limit[]" type="text"
                                                            value="{{ isset($method->fees_limit->min_limit) ? number_format((float) $method->fees_limit->min_limit, $preference, '.', '') : number_format((float) 1.0, $preference, '.', '') }}"
                                                            id="min_limit_{{ $method->id }}"
                                                            {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }}
                                                            onkeypress="return isNumberOrDecimalPointKey(this, event);"
                                                            oninput="restrictNumberToPrefdecimalOnInput(this)">

                                                        <small
                                                            class="form-text text-muted f-12"><strong>{{ allowedDecimalPlaceMessage($preference) }}</strong></small>
                                                        @if ($errors->has('min_limit'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('min_limit') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <p class="mb-0 f-14 mt-11">{{ __('If not set, minimum limit is :x', ['x' => number_format((float) 1.0, $preference, '.', '') ]) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                <!-- Maximum Limit -->
                                                <div class="form-group row">
                                                    <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="max_limit_{{ $method->id }}">{{ __('Maximum
                                                        Limit') }}</label>
                                                    <div class="col-sm-5">
                                                        <input class="form-control f-14 max_limit" name="max_limit[]" type="text"
                                                            value="{{ isset($method->fees_limit->max_limit) ? number_format((float) $method->fees_limit->max_limit, $preference, '.', '') : '' }}"
                                                            id="max_limit_{{ $method->id }}"
                                                            {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }}
                                                            onkeypress="return isNumberOrDecimalPointKey(this, event);"
                                                            oninput="restrictNumberToPrefdecimalOnInput(this)">
                                                        <small
                                                            class="form-text text-muted f-12"><strong>{{ allowedDecimalPlaceMessage($preference) }}</strong></small>
                                                        @if ($errors->has('max_limit'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('max_limit') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <p class="mb-0 f-14 mt-11">{{ __('If not set, maximum limit is infinity') }}</p>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                <!-- Charge Percentage -->
                                                <div class="form-group row">
                                                    <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="charge_percentage_{{ $method->id }}">{{ __('Charge Percentage') }}</label>
                                                    <div class="col-sm-5">
                                                        <input class="form-control f-14 charge_percentage"
                                                            name="charge_percentage[]" type="text"
                                                            value="{{ isset($method->fees_limit->charge_percentage) ? number_format((float) $method->fees_limit->charge_percentage, $preference, '.', '') : number_format((float) 0.0, $preference, '.', '') }}"
                                                            id="charge_percentage_{{ $method->id }}"
                                                            {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }}
                                                            onkeypress="return isNumberOrDecimalPointKey(this, event);"
                                                            oninput="restrictNumberToPrefdecimalOnInput(this)">
                                                        <small
                                                            class="form-text text-muted f-12"><strong>{{ allowedDecimalPlaceMessage($preference) }}</strong></small>
                                                        @if ($errors->has('charge_percentage'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('charge_percentage') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <p class="mb-0 f-14 mt-11">{{ __('If not set, charge percentage is :x', ['x' => number_format((float) 0.0, $preference, '.', '')]) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                <!-- Charge Fixed -->
                                                <div class="form-group row">
                                                    <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="charge_fixed_{{ $method->id }}">{{ __('Charge
                                                        Fixed') }}</label>
                                                    <div class="col-sm-5">
                                                        <input class="form-control f-14 charge_fixed" name="charge_fixed[]"
                                                            type="text"
                                                            value="{{ isset($method->fees_limit->charge_fixed) ? number_format((float) $method->fees_limit->charge_fixed, $preference, '.', '') : number_format((float) 0.0, $preference, '.', '') }}"
                                                            id="charge_fixed_{{ $method->id }}"
                                                            {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }}
                                                            onkeypress="return isNumberOrDecimalPointKey(this, event);"
                                                            oninput="restrictNumberToPrefdecimalOnInput(this)">
                                                        <small
                                                            class="form-text text-muted f-12"><strong>{{ allowedDecimalPlaceMessage($preference) }}</strong></small>
                                                        @if ($errors->has('charge_fixed'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('charge_fixed') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <p class="mb-0 f-14 mt-11">{{ __('If not set, charge fixed is :x', ['x' => number_format((float) 0.0, $preference, '.', '')]) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ url(config('adminPrefix') . '/settings/currency') }}"
                                class="btn btn-theme-danger f-14 me-1">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-theme f-14" id="deposit_limit_update">
                                <i class="fa fa-spinner fa-spin d-none"></i> <span
                                    id="deposit_limit_update_text">{{ __('Update') }}</span>
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')

@include('common.restrict_number_to_pref_decimal')
@include('common.restrict_character_decimal_point')

    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        function restrictNumberToPrefdecimalOnInput(e)
        {
            var type = $('#type').val();
            restrictNumberToPrefdecimal(e, type);
        }

        function formatNumberToPrefDecimal(num = 0)
        {
            let type = $('#type').val();
            let decimal_format = (type == 'fiat') ? "<?php echo preference('decimal_format_amount', 2); ?>" : "<?php echo preference('decimal_format_amount_crypto', 8); ?>";

            num = ((Math.abs(num)).toFixed(decimal_format))
            return num;
        }

        if ($('#defaultCurrency').val() == 1) {
            $('.defaultCurrencyDiv').show();
        } else {
            $('.defaultCurrencyDiv').hide();
        }

        $('#deposit_limit_form').validate({
            rules: {
                min_limit: {
                    number: true,
                },
                max_limit: {
                    number: true,
                },
                charge_percentage: {
                    number: true,
                },
                charge_fixed: {
                    number: true,
                },
                processing_time: {
                    number: true,
                },
            },
            submitHandler: function(form) {
                $("#deposit_limit_update").attr("disabled", true);
                $(".fa-spin").removeClass("d-none");
                $("#deposit_limit_update_text").text('Updating...');
                form.submit();
            }
        });

        $(".has_transaction").click(function() {
            var payment_method_id = $(this).data('method_id');
            if ($('#has_transaction_' + payment_method_id).prop('checked') == true) {
                $('#has_transaction_' + payment_method_id).val('Yes')
                $('#min_limit_' + payment_method_id).prop("readonly", false);
                $('#max_limit_' + payment_method_id).prop("readonly", false);
                $('#charge_percentage_' + payment_method_id).prop("readonly", false);
                $('#charge_fixed_' + payment_method_id).prop("readonly", false);
            } else {
                $('#has_transaction_' + payment_method_id).val('')
                $('#min_limit_' + payment_method_id).prop("readonly", true);
                $('#max_limit_' + payment_method_id).prop("readonly", true);
                $('#charge_percentage_' + payment_method_id).prop("readonly", true);
                $('#charge_fixed_' + payment_method_id).prop("readonly", true);
            }
        });

        //on load
        $(window).on('load', function() {
            var previousUrl = document.referrer;
            var urlByOwn = SITE_URL + '/' + ADMIN_PREFIX + '/settings/currency';
            if (previousUrl == urlByOwn) {
                localStorage.removeItem('currencyId');
                localStorage.removeItem('currencyName');
                localStorage.removeItem('defaultCurrency');
            } else {
                if ((localStorage.getItem('currencyName')) && (localStorage.getItem('currencyId')) && (localStorage.getItem('defaultCurrency'))) {
                    $('.currencyName').text(localStorage.getItem('currencyName'));
                    $('#currency_id').val(localStorage.getItem('currencyId'));
                    $('#defaultCurrency').val(localStorage.getItem('defaultCurrency'));
                    getFeesLimitDetails();
                } else {
                    getSpecificCurrencyDetails();
                }
            }
        });

        //currency dropdown
        $('.listItem').on('click', function() {
            var currencyId = $(this).attr('data-rel');
            var type = $(this).attr('data-type');
            var currencyName = $(this).text();
            var defaultCurrency = $(this).attr('data-default');
            if (defaultCurrency == 1) {
                $('.defaultCurrencyDiv').show();
            } else {
                $('.defaultCurrencyDiv').hide();
            }
            localStorage.setItem('currencyId', currencyId);
            localStorage.setItem('currencyName', currencyName);
            localStorage.setItem('defaultCurrency', defaultCurrency);
            $('.currencyName').text(currencyName);
            $('#currency_id').val(currencyId);
            $('#type').val(type);
            $('#defaultCurrency').val(defaultCurrency);
            getFeesLimitDetails();
        });

        //Window on load/click on list item get fees limit details
        function getFeesLimitDetails() {
            var currencyId = $('#currency_id').val();
            var checkDefaultCurrency = $('#defaultCurrency').val();
            var tabText = $('#tabText').val();
            var transaction_type = $('#transaction_type').val();
            var token = $("input[name=_token]").val();
            var url = SITE_URL + '/' + ADMIN_PREFIX + '/settings/get-feeslimit-details';
            $.ajax({
                url: url,
                type: "post",
                data: {
                    'currency_id': currencyId,
                    'transaction_type': transaction_type,
                    '_token': token
                },
                dataType: 'json',
                success: function(data) {
                    if (data.status == 200) {
                        var feesLimt = data.feeslimit;
                        if (checkDefaultCurrency == 1) {
                            $('.defaultCurrencyDiv').show();
                            $('.default_currency_label').html('Is Activated');
                            $('.default_currency_side_text').text('Default currency is always active');
                            $(".has_transaction").prop('checked', true);
                            $(".has_transaction").prop('disabled', true);
                            $('.has_transaction').val('Yes');
                        } else {
                            $('.defaultCurrencyDiv').hide();
                            $('.default_currency_label').html('Is Activated');
                            $('.default_currency_side_text').text('');
                            $(".has_transaction").prop('checked', false);
                            $('.has_transaction').removeAttr('disabled');
                            $('.has_transaction').val('');
                        }

                        $.each(feesLimt, function(key, value) {
                            // console.log(feesLimt);
                            if (value.fees_limit != null) {
                                $('#min_limit_' + value.id).val(formatNumberToPrefDecimal(value.fees_limit.min_limit));
                                if (value.fees_limit.max_limit != null) {
                                    $('#max_limit_' + value.id).val(formatNumberToPrefDecimal(value.fees_limit.max_limit));
                                } else {
                                    $('#max_limit_' + value.id).val('');
                                }
                                $('#charge_percentage_' + value.id).val(formatNumberToPrefDecimal(value.fees_limit.charge_percentage));
                                $('#charge_fixed_' + value.id).val(formatNumberToPrefDecimal(value.fees_limit.charge_fixed));

                                $('#has_transaction_' + value.id).val(value.fees_limit.has_transaction);
                                if (value.fees_limit.has_transaction == 'Yes') {
                                    $('#has_transaction_' + value.id).prop('checked', true);
                                    $('#min_limit_' + value.id).prop("readonly", false);
                                    $('#max_limit_' + value.id).prop("readonly", false);
                                    $('#charge_percentage_' + value.id).prop("readonly", false);
                                    $('#charge_fixed_' + value.id).prop("readonly", false);
                                } else {
                                    $('#has_transaction_' + value.id).prop('checked', false);
                                    $('#min_limit_' + value.id).prop("readonly", true);
                                    $('#max_limit_' + value.id).prop("readonly", true);
                                    $('#charge_percentage_' + value.id).prop("readonly", true);
                                    $('#charge_fixed_' + value.id).prop("readonly", true);
                                }
                            } else {
                                $('#min_limit_' + value.id).val(formatNumberToPrefDecimal(
                                '1.00000000'));
                                $('#max_limit_' + value.id).val('');
                                $('#charge_percentage_' + value.id).val(formatNumberToPrefDecimal(
                                    '0.00000000'));
                                $('#charge_fixed_' + value.id).val(formatNumberToPrefDecimal(
                                    '0.00000000'));
                                $('#has_transaction_' + value.id).prop('checked', false);
                                $('#min_limit_' + value.id).prop("readonly", true);
                                $('#max_limit_' + value.id).prop("readonly", true);
                                $('#charge_percentage_' + value.id).prop("readonly", true);
                                $('#charge_fixed_' + value.id).prop("readonly", true);
                            }
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        // Get Specific Currency Details
        function getSpecificCurrencyDetails() {
            var currencyId = $('#currency_id').val();
            var checkDefaultCurrency = $('#defaultCurrency').val();
            var transaction_type = $('#transaction_type').val();
            var token = $("input[name=_token]").val();
            var tabText = $('#tabText').val();
            var url = SITE_URL + '/' + ADMIN_PREFIX + '/settings/get-specific-currency-details';
            $.ajax({
                url: url,
                type: "post",
                data: {
                    'currency_id': currencyId,
                    'transaction_type': transaction_type,
                    '_token': token
                },
                dataType: 'json',
                success: function(data) {
                    if (data.status == 200) {
                        var feesLimt = data.feeslimit;
                        if (checkDefaultCurrency == 1) {
                            $('.defaultCurrencyDiv').show();
                            $('.default_currency_label').html('Is Activated');
                            $('.default_currency_side_text').text('Default currency is always active');
                            $(".has_transaction").prop('checked', true);
                            $('#has_transaction').attr('disabled', true);
                            $('#has_transaction').val('Yes');
                            $("#min_limit").prop("readonly", false);
                            $("#max_limit").prop("readonly", false);
                            $("#charge_percentage").prop("readonly", false);
                            $("#charge_fixed").prop("readonly", false);
                        } else {
                            $('.defaultCurrencyDiv').hide();
                            $('.default_currency_label').html('Is Activated');
                            $('.default_currency_side_text').hide();
                            $("#has_transaction").prop('checked', false);
                            $('#has_transaction').removeAttr('disabled');
                            $('.has_transaction').val('No');
                            $("#min_limit").prop("readonly", true);
                            $("#max_limit").prop("readonly", true);
                            $("#charge_percentage").prop("readonly", true);
                            $("#charge_fixed").prop("readonly", true);
                        }
                        $.each(feesLimt, function(key, value) {
                            // console.log(feesLimt);
                            if (value.fees_limit != null) {
                                $('#min_limit_' + value.id).val(formatNumberToPrefDecimal(value
                                    .fees_limit.min_limit));
                                if (value.fees_limit.max_limit != null) {
                                    $('#max_limit_' + value.id).val(formatNumberToPrefDecimal(value
                                        .fees_limit.max_limit));
                                } else {
                                    $('#max_limit_' + value.id).val('');
                                }
                                $('#charge_percentage_' + value.id).val(formatNumberToPrefDecimal(value
                                    .fees_limit.charge_percentage));
                                $('#charge_fixed_' + value.id).val(formatNumberToPrefDecimal(value
                                    .fees_limit.charge_fixed));

                                $('#has_transaction_' + value.id).val(value.fees_limit.has_transaction);
                                if (value.fees_limit.has_transaction == 'Yes') {
                                    $('#has_transaction_' + value.id).prop('checked', true);
                                    $('#min_limit_' + value.id).prop("readonly", false);
                                    $('#max_limit_' + value.id).prop("readonly", false);
                                    $('#charge_percentage_' + value.id).prop("readonly", false);
                                    $('#charge_fixed_' + value.id).prop("readonly", false);
                                } else {
                                    $('#has_transaction_' + value.id).prop('checked', false);
                                    $('#min_limit_' + value.id).prop("readonly", true);
                                    $('#max_limit_' + value.id).prop("readonly", true);
                                    $('#charge_percentage_' + value.id).prop("readonly", true);
                                    $('#charge_fixed_' + value.id).prop("readonly", true);
                                }
                            } else {
                                $('#min_limit_' + value.id).val(formatNumberToPrefDecimal(
                                '1.00000000'));
                                $('#max_limit_' + value.id).val('');
                                $('#charge_percentage_' + value.id).val(formatNumberToPrefDecimal(
                                    '0.00000000'));
                                $('#charge_fixed_' + value.id).val(formatNumberToPrefDecimal(
                                    '0.00000000'));

                                $('#has_transaction_' + value.id).prop('checked', false);
                                $('#min_limit_' + value.id).prop("readonly", true);
                                $('#max_limit_' + value.id).prop("readonly", true);
                                $('#charge_percentage_' + value.id).prop("readonly", true);
                                $('#charge_fixed_' + value.id).prop("readonly", true);
                            }
                        });
                    } else {
                        if (checkDefaultCurrency == 1) {
                            $('.defaultCurrencyDiv').show();
                            $('.default_currency_label').html('Is Activated');
                            $('.default_currency_side_text').text('Default currency is always active');
                            $('#has_transaction').removeAttr('disabled');
                        } else {
                            $('.defaultCurrencyDiv').hide();
                            $('.default_currency_label').html('Is Activated');
                            $('.default_currency_side_text').text('');
                        }
                        $('#id').val('');
                        $('.currencyName').text(data.currency.name);
                        $('#currency_id').val(data.currency.id);
                        $(".has_transaction").prop('checked', false);
                        $('.has_transaction').val('No');
                        $('.min_limit').val('1.00000000');
                        $('.max_limit').val('');
                        $('.charge_percentage').val('0');
                        $('.charge_fixed').val('0');
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    </script>

@endpush
