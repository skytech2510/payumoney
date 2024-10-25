@extends('admin.layouts.master')

@section('title', __('Deposit'))

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
                <a href="{{ url(config('adminPrefix').'/users/deposit/create/'. $users->id) }}" class="pull-right btn btn-theme f-14 active">{{ __('Deposit') }}</a>
            </div>
        </div>
    </div>

    <div class="box mt-20">
        <div class="box-body">
            <div class="col-md-7">
                <div class="panel panel-default">
                    <div class="panel-body f-14">
                        <h3 class="text-center f-24"><strong>{{ __('Details') }}</strong></h3>
                        <div class="row">
                            <div class="col-md-6 pull-left">{{ __('Amount') }}</div>
                            <div class="col-md-6  text-sm-end">
                                <strong>{{ moneyFormat($transInfo['currSymbol'], isset($transInfo['amount']) ? formatNumber($transInfo['amount'], $transInfo['currency_id']) : 0.0) }}</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pull-left">{{ __('Fee') }}</div>
                            <div class="col-md-6 text-sm-end">
                                <strong>{{ moneyFormat($transInfo['currSymbol'], isset($transInfo['fee']) ? formatNumber($transInfo['fee'], $transInfo['currency_id']) : 0.0) }}</strong>
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-md-6 pull-left"><strong>{{ __('Total') }}</strong></div>
                            <div class="col-md-6 text-sm-end">
                                <strong>{{ moneyFormat($transInfo['currSymbol'], isset($transInfo['totalAmount']) ? formatNumber($transInfo['totalAmount'], $transInfo['currency_id']) : 0.0) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div>
                    <div class="float-start">
                        <a href="#" class="admin-user-deposit-confirm-back-link">
                            <button class="btn btn-theme-danger admin-user-deposit-confirm-back-btn">
                                <strong class="f-14"><i class="fa fa-angle-left"></i>&nbsp;&nbsp;{{ __('Back') }}</strong>
                            </button>
                        </a>
                    </div>
                    <div class="float-end">
                        <form class="d-block" action="{{ url(config('adminPrefix') . '/users/deposit/storeFromAdmin') }}"
                            method="POST" accept-charset="UTF-8"
                            id="admin-user-deposit-confirm" novalidate="novalidate">
                            <input value="{{ csrf_token() }}" name="_token" id="token" type="hidden">
                            <input value="{{ $transInfo['totalAmount'] }}" name="amount" id="amount" type="hidden">
                            <input value="{{ $users->id }}" name="user_id" type="hidden">
                            <button type="submit" class="btn btn-theme f-14"
                                id="admin-user-deposit-confirm-btn">
                                <i class="fa fa-spinner fa-spin f-14 d-none"></i>
                                <span id="admin-user-deposit-confirm-btn-text">
                                    <strong class="f-14">{{ __('Confirm') }}&nbsp; <i class="fa fa-angle-right"></i></strong>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')

    <!-- jquery.validate -->
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $('#admin-user-deposit-confirm').validate({
            rules: {
                amount: {
                    required: false,
                },
            },
            submitHandler: function(form) {
                $("#admin-user-deposit-confirm-btn").attr("disabled", true);
                $(".fa-spin").removeClass("d-none");
                var pretext = $("#admin-user-deposit-confirm-btn-text").text();
                $("#admin-user-deposit-confirm-btn-text").text('Confirming...');

                //Make back button disabled and prevent click
                $('.admin-user-deposit-confirm-back-btn').attr("disabled", true).click(function(e) {
                    e.preventDefault();
                });

                //Make back anchor prevent click
                $('.admin-user-deposit-confirm-back-link').click(function(e) {
                    e.preventDefault();
                });

                form.submit();
                setTimeout(function() {
                    $("#admin-user-deposit-confirm-btn-text").html(pretext +
                        '<i class="fa fa-angle-right"></i>');
                    $("#admin-user-deposit-confirm-btn").removeAttr("disabled");
                    $(".fa-spin").hide();
                }, 10000);
            }
        });

        //Only go back by back button, if submit button is not clicked
        $(document).on('click', '.admin-user-deposit-confirm-back-btn', function(e) {
            e.preventDefault();
            window.history.back();
        });
    </script>
@endpush
