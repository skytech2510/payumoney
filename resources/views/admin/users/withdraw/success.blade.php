@extends('admin.layouts.master')

@section('title', __('Payout'))

@section('page_content')

<style type="text/css">
.confirm-btns {
    width: 35px;
    height: 35px;
    background-color: #58c42b !important;
    border-radius: 50%;
    border: 1px solid #247701;
    color: #FFFFFF;
    text-align: center;
    line-height: 25px;
    font-size: 25px;
    text-shadow: #009933;
    margin: 0 auto;
}
</style>

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
        <h3 class="f-24">{{ $name }}</h3>
    </div>
    <div class="col-md-3"></div>
    <div class="col-md-5">
        <div class="pull-right">
            <a href="{{ url(config('adminPrefix').'/users/withdraw/create/' . $users->id) }}" class="pull-right btn btn-theme f-14 active">{{ __('Withdraw') }}</a>
        </div>
    </div>
</div>

<div class="box mt-20">
    <div class="box-body">
        <div class="row">
            <div class="col-md-7">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-center">
                            <div class="confirm-btns"><i class="fa fa-check f-14"></i></div>
                        </div>
                        <div class="text-center">
                            <div class="text-success f-24 mt-2">{{ __('Success') }}!</div>
                        </div>
                        <div class="text-center f-14 mt-2"><p class="mb-0"><strong>{{ __('Withdrawal completed successfully.') }}</strong></p></div>
                        <h5 class="text-center f-14 mt-1">{{ __('Amount') }} : {{ moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['subtotal'], $transInfo['currency_id'])) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div>
                    <div class="float-start">
                        <a href="{{ url(config('adminPrefix')."/users/withdraw/print/".$transInfo['id'])}}" target="_blank" class="btn button-secondary"><strong class="f-14">{{ __('Print') }}</strong></a>
                    </div>
                    <div class="float-end">
                        <a href="{{ url(config('adminPrefix')."/users/withdraw/create/" . $users->id)}}" class="btn btn-theme"><strong class="f-14">{{ __('Withdraw Again') }}</strong></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')
<script type="text/javascript">
</script>
@endpush
