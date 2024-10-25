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
        <h3 class="f-24">{{ $name }}</h3>
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
        <div class="row">
            <div class="col-md-7">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-center">
                            <div class="confirm-btns"><i class="fa fa-check f-14"></i></div>
                        </div>
                        <div class="text-center">
                            <div class="f-24 text-success mt-2"> {{ __('Success') }}!</div>
                        </div>
                        <div class="text-center f-14 mt-2"><p class="mb-0"><strong> {{ __('Deposit Completed Successfully') }}</strong></p></div>
                        <h5 class="text-center f-14 mt-1">{{ __('Deposit Amount') }} : {{ moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['subtotal'], $transInfo['currency_id'])) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="{{ url(config('adminPrefix')."/users/deposit/print/".$transInfo['id'])}}" target="_blank" class="btn button-secondary"><strong class="f-14">{{ __('Print') }}</strong></a>
                    </div>
                    <div>
                        <a href="{{ url(config('adminPrefix')."/users/deposit/create/" . $users->id)}}" class="btn btn-theme"><strong class="f-14">{{ __('Deposit Again') }}</strong></a>
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
