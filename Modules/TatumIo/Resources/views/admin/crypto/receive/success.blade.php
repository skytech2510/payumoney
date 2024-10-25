@extends('admin.layouts.master')

@section('title', __('Crypto Receive Success'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumIo/Resources/assets/admin/css/crypto_sent_receive.min.css') }}">
@endsection

@section('page_content')
<div class="row">
    <div class="col-md-2">
        <button type="button" class="btn btn-theme active mt-15 f-14">{{ __('Crypto Receive') }}</button>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-4">
        <div class="pull-right f-14">
            <h3>{{ $user_full_name }}</h3>
        </div>
    </div>
</div>

<div class="box mt-20">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-7">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="text-center">
                                  <div class="confirm-btns"><i class="fa fa-check"></i></div>
                                </div>
                                <div class="text-center">
                                    <div class="f-24 text-success mt-2"> {{ __('Success!') }}</div>
                                </div>
                                <div class="text-center f-14 mt-2"><p><strong> {{ __(':x received successfully.', ['x' => $walletCurrencyCode]) }}</strong></p></div>
                                <div class="text-center f-14 mt-1"><p><strong> {{ __('Amount will be added after :x confirmations.', ['x' => $confirmations]) }}</strong></p></div>
                                <div class="text-center f-14 mt-1"><p> {{ __('Address: :x', ['x' => $receiverAddress]) }}</p></div>
                                <h5 class="text-center f-14 mt-1">{{ __('Received Amount: :x', ['x' => moneyFormat($currencySymbol, formatNumber($amount, $currencyId))]) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.crypto_send_receive.print', encrypt($transactionId)) }}" target="_blank" class="btn button-secondary"><strong>{{ __('Print') }}</strong></a>
                            </div>
                            <div>
                                <a href="{{ route('admin.tatum.crypto_receive.create', encrypt($walletCurrencyCode)) }}" class="btn btn-theme"><strong>{{ __('Receive :x Again', ['x' => $walletCurrencyCode]) }}</strong></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
