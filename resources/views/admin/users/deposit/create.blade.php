@extends('admin.layouts.master')

@section('title', __('Profile'))

@section('page_content')
    <div class="box">
        <div class="panel-body ml-20">
            <ul class="nav nav-tabs f-14 cus" role="tablist">
                @include('admin.users.user_tabs')
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <h3 class="f-24">{{ getColumnValue($users) }}</h3>
        </div>
        <div class="col-md-3"></div>
        <div class="col-md-5">
            <div class="pull-right">
                <a href="{{ url(config('adminPrefix').'/users/deposit/create', $users->id) }}" class="pull-right btn btn-theme f-14 active">{{ __('Deposit') }}</a>
            </div>
        </div>
    </div>

    <div class="box mt-20">
        <div class="box-body">
            <div class="panel panel-info">
                <div class="panel-body">
                    <form action="{{  url(config('adminPrefix')."/users/deposit/create", $users->id) }}" method="post" accept-charset='UTF-8' id="admin-user-deposit-create">
                        <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                        <input type="hidden" name="user_id" id="user_id" value="{{ $users->id }}">
                        <input type="hidden" name="fullname" id="fullname" value="{{ getColumnValue($users) }}">
                        <input type="hidden" name="percentage_fee" id="percentage_fee" value="">
                        <input type="hidden" name="fixed_fee" id="fixed_fee" value="">
                        <input type="hidden" name="fee" class="total_fees" value="0.00">

                        <div class="row">
                            {{-- Currency --}}
                            <div class="col-md-4">
                                <div class="form-group f-14">
                                    <label class="mb-1" for="currency_id">{{ __('Currency') }}</label>
                                    <select class="select2 wallet" name="currency_id" id="currency_id">
                                        @foreach ($activeCurrencyList as $aCurrency)
                                            <option data-type="{{ $aCurrency['type'] }}" value="{{ $aCurrency['id'] }}">{{ $aCurrency['code'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <small id="walletlHelp" class="form-text text-muted f-12">
                                    {{ __('Fee') }}(<span class="pFees">0</span>%+<span class="fFees">0</span>), {{ __('Total') }}:  <span class="total_fees">0.00</span>
                                </small>
                            </div>
                            {{-- Amount --}}
                            <div class="col-md-4">
                                <div class="form-group f-14">
                                    <label class="mb-1" for="amount">Amount</label>
                                    <input type="text" class="form-control amount f-14" name="amount" placeholder="0.00" type="text" id="amount" onkeypress="return isNumberOrDecimalPointKey(this, event);"
                                        value="" oninput="restrictNumberToPrefdecimalOnInput(this)">
                                    <span class="amountLimit text-danger fw-bold"></span>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-5 dis-none">
                                <div class="form-group">
                                    <label for="payment_method">{{ __('Payment Method') }}</label>
                                    <select class="form-control payment_method" name="payment_method" id="payment_method">
                                        <option value="{{ $payment_met->id }}">{{ $payment_met->name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ url(config('adminPrefix').'/users/edit/', $users->id) }}" class="btn btn-theme-danger me-1"><span class="f-14"><i class="fa fa-angle-left"></i>&nbsp;{{ __('Back') }}</span></a>
                            <button type="submit" class="btn btn-theme" id="deposit-create">
                                <i class="fa fa-spinner fa-spin f-14 d-none"></i>
                                <span id="deposit-create-text" class="f-14">{{ __('Next') }}&nbsp;<i class="fa fa-angle-right"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

@include('common.restrict_number_to_pref_decimal')
@include('common.restrict_character_decimal_point')

<script type="text/javascript">
    $(".select2").select2({});

    $('#admin-user-deposit-create').validate({
        rules: {
            amount: {
                required: true,
            },
        },
        submitHandler: function (form)
        {
            $("#deposit-create").attr("disabled", true);
            $(".fa-spin").removeClass("d-none");
            var pretext=$("#deposit-create-text").text();
            $("#deposit-create-text").text('Depositing...');
            form.submit();
            setTimeout(function(){
                $("#deposit-create-text").html(pretext + '<i class="fa fa-angle-right"></i>');
                $("#deposit-create").removeAttr("disabled");
                $(".fa-spin").hide();
            },1000);
        }
    });

    function restrictNumberToPrefdecimalOnInput(e)
    {
        var type = $('select#currency_id').find(':selected').data('type')
        restrictNumberToPrefdecimal(e, type);
    }

    function determineDecimalPoint() {

        var currencyType = $('select#currency_id').find(':selected').data('type')

        if (currencyType == 'crypto') {
            $('.pFees, .fFees, .total_fees').text(CRYPTODP);
            $("#amount").attr('placeholder', CRYPTODP);

        } else if (currencyType == 'fiat') {

            $('.pFees, .fFees, .total_fees').text(FIATDP);
            $("#amount").attr('placeholder', FIATDP);
        }
    }

    $(window).on('load', function (e) {
        determineDecimalPoint();
        checkAmountLimitAndFeesLimit();
    });

    $(document).on('input', '.amount', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    $(document).on('change', '.wallet', function (e) {
        determineDecimalPoint();
        checkAmountLimitAndFeesLimit();
    });

    function checkAmountLimitAndFeesLimit()
    {
        var token = $("#token").val();
        var amount = $('#amount').val();
        var currency_id = $('#currency_id').val();
        var payment_method_id = $('#payment_method').val();

        $.ajax({
            method: "POST",
            url: SITE_URL+"/"+ADMIN_PREFIX+"/users/deposit/amount-fees-limit-check",
            dataType: "json",
            data: {
                "_token": token,
                'amount': amount,
                'currency_id': currency_id,
                'payment_method_id': payment_method_id,
                'user_id': '{{ $users->id }}',
                'transaction_type_id': '{{Deposit}}'
            }
        })
        .done(function (response)
        {
            // console.log(response.success);

            if (response.success.status == 200)
            {
                $("#percentage_fee").val(response.success.feesPercentage);
                $("#fixed_fee").val(response.success.feesFixed);
                $(".percentage_fees").html(response.success.feesPercentage);
                $(".fixed_fees").html(response.success.feesFixed);
                $(".total_fees").val(response.success.totalFees);
                $('.total_fees').html(response.success.totalFeesHtml);
                $('.pFees').html(response.success.pFeesHtml);
                $('.fFees').html(response.success.fFeesHtml);

                $('.amountLimit').text('');
                $("#deposit-create").attr("disabled", false);
                return true;
            }
            else
            {
                if (amount == '')
                {
                    $('.amountLimit').text('');
                }
                else
                {
                    $('.amountLimit').text(response.success.message);
                    $("#deposit-create").attr("disabled", true);
                    return false;
                }
            }
        });
    }
</script>

@endpush
