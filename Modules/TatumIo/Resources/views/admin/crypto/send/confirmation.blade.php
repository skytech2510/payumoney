@php
    $amount = formatNumber($cryptoTrx['amount'], $cryptoTrx['currency_id']);
    $networkFee = formatNumber($cryptoTrx['network_fee'], $cryptoTrx['currency_id']);
    $network = $cryptoTrx['network'];
    $user_id = $cryptoTrx['user_id'];
    $user_full_name = $cryptoTrx['user_full_name'];
    $total = formatNumber($cryptoTrx['amount'] + $cryptoTrx['network_fee'], $cryptoTrx['currency_id']);
@endphp

@extends('admin.layouts.master')

@section('title', __('Crypto Send Confirm'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumIo/Resources/assets/admin/css/crypto_sent_receive.min.css') }}">
@endsection

@section('page_content')

<div class="row">
    <div class="col-md-2">
        <button type="button" class="btn btn-theme active mt-15 f-14">{{ __('Crypto Send') }}</button>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-4">
        <div class="pull-right">
            <h3 class="f-24">{{ $user_full_name }}</h3>
        </div>
    </div>
</div>

<div class="box mt-20">
    <div class="box-body" id="crypto-send-confirm">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-7">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <h3 class="text-center f-24"><strong>{{ __('Details') }}</strong></h3>
                                <div class="row">
                                    <div class="col-md-6 pull-left f-14">{{ __('Sent Amount') }}</div>
                                    <div class="col-md-6  text-sm-end f-14"><strong>{{ moneyFormat($cryptoTrx['currency_symbol'], $amount) }}</strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 pull-left f-14">{{ __('Network Fee') }}</div>
                                    <div class="col-md-6 text-sm-end f-14"><strong>{{ moneyFormat($cryptoTrx['currency_symbol'], $networkFee) }}</strong></div>
                                </div>
                                <hr />
                                <div class="row">
                                    <div class="col-md-6 pull-left f-14"><strong>{{ __('Total') }}</strong></div>
                                    <div class="col-md-6 text-sm-end f-14"><strong>{{ moneyFormat($cryptoTrx['currency_symbol'], $total) }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="ml-0">
                            <div class="float-left">
                                <a href="#" class="admin-user-crypto-send-confirm-back-link">
                                    <button class="btn btn-theme-danger admin-user-crypto-send-confirm-back-btn f-14"><strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;{{ __('Back') }}</strong></button>
                                </a>
                            </div>
                            <div class="float-right">
                                <form action="{{ route('admin.tatum.crypto_send.success') }}" method="POST" accept-charset="UTF-8" id="admin-user-crypto-send-confirm" novalidate="novalidate">
                                    <input value="{{csrf_token()}}" name="_token" id="token" type="hidden">
                                    <input type="hidden" name="network" value="{{ $network }}">

                                    <button type="submit" class="btn btn-theme f-14" id="admin-user-crypto-send-confirm-btn">
                                        <i class="fa fa-spinner fa-spin d-none"></i>
                                        <span id="admin-user-crypto-send-confirm-btn-text">
                                            <strong>{{ __('Confirm') }}&nbsp; <i class="fa fa-angle-right"></i></strong>
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"  type="text/javascript" ></script>
<script>
    'use strict';
    var cryptoSendBackConfirmUrl = '{{ route("admin.crypto_send.create", ["code" => encrypt($network)]) }}';
    var confirm = '{{ __("Confirm") }}';
    var confirming = '{{ __("Confirming...") }}';
</script>

<script src="{{ asset('Modules/TatumIo/Resources/assets/admin/js/crypto_sent.min.js') }}"  type="text/javascript"></script>
@endpush
