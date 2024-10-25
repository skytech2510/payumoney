@extends('admin.layouts.master')

@section('title', __('Crypto Send Success'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/BlockIo/Resources/assets/admin/css/crypto_sent_receive.min.css') }}">
@endsection

@section('page_content')

<div class="row">
    <div class="col-md-2">
        <button type="button" class="btn btn-theme btn-flat active mt-15">{{ __('Crypto Send') }}</button>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-4">
        <div class="pull-right">
            <h3>{{ $user_full_name }}</h3>
        </div>
    </div>
</div>

<div class="box">
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
                                    <div class="h3 mt6 text-success"> {{ __('Success!') }}</div>
                                </div>
                                <div class="text-center"><p><strong>{{ __(':x sent successfully.', ['x' => $walletCurrencyCode]) }}</strong></p></div>
                                <div class="text-center"><p><strong> {{ __('Amount will be added after :x confirmations.', ['x' => $confirmations]) }}</strong></p></div>
                                <div class="text-center"><p> {{ __('Address:') }} {{ $receiverAddress }}</p></div>
                                <h5 class="text-center mt10">{{ __('Sent Amount:') }} {{ moneyFormat($currencySymbol, formatNumber($amount, $currencyId)) }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="ml-0">
                            <div class="float-left">
                                <a href="{{ route('admin.crypto_send_receive.print', encrypt($transactionId)) }}" target="_blank" class="btn button-secondary"><strong>{{ __('Print') }}</strong></a>
                            </div>
                            <div class="float-right">
                                <a href="{{ route('admin.crypto_send.create', [encrypt($walletCurrencyCode)]) }}" class="btn btn-theme"><strong>{{ __('Send :x Again', ['x' => $walletCurrencyCode]) }}</strong></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection