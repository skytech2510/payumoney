@extends('admin.layouts.master')
@section('title', __('Edit Transaction'))

@section('page_content')

<div class="box box-default">
	<div class="box-body">
		<div class="d-flex justify-content-between">
			<div>
				<div class="top-bar-title padding-bottom pull-left">{{ __('Transaction Details') }}</div>
			</div>

			<div>
				@if ($transaction->status)
					<p class="text-left mb-0 f-18">{{ __('Status') }} :
                    @php
                        $transactionTypes = getPaymoneySettings('transaction_types')['web'];
                        if (in_array($transaction->transaction_type_id, $transactionTypes['all'])) {
                            echo getStatusText($transaction->status);
                        }
                    @endphp
                    </p>
				@endif
			</div>
		</div>
	</div>
</div>
<section class="min-vh-100">
    <div class="my-30">
        <form action="{{ url(config('adminPrefix').'/transactions/update/'.$transaction->id) }}" class="form-horizontal" id="transactions_form" method="POST">
            {{ csrf_field() }}
            <div class="row f-14">
                <!-- Page title start -->
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="p-4 rounded">
                                        <input type="hidden" value="{{ $transaction->id }}" name="id" id="id">
                                        <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
                                        <input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">
                                        <input type="hidden" value="{{ $transaction->uuid }}" name="uuid" id="uuid">
                                        <input type="hidden" value="{{ $transaction->user_id }}" name="user_id" id="user_id">
                                        <input type="hidden" value="{{ $transaction->end_user_id }}" name="end_user_id" id="end_user_id">
                                        <input type="hidden" value="{{ $transaction->currency_id }}" name="currency_id" id="currency_id">
                                        <input type="hidden" value="{{ ($transaction->percentage) }}" name="percentage" id="percentage">
                                        <input type="hidden" value="{{ ($transaction->charge_percentage) }}" name="charge_percentage" id="charge_percentage">
                                        <input type="hidden" value="{{ ($transaction->charge_fixed) }}" name="charge_fixed" id="charge_fixed">
                                        <input type="hidden" value="{{ base64_encode($transaction->payment_method_id) }}" name="payment_method_id" id="payment_method_id">
                                        <input type="hidden" class="form-control" name="subtotal" value="{{ $transaction->subtotal }}">

                                        {{-- User --}}
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="sender">{{ __('Sender') }}</label>

                                            <input type="hidden" class="form-control" name="user" value="
                                                @if (in_array($transaction->transaction_type_id, getPaymoneySettings('transaction_types')['web']['sent']))
                                                    {{ getColumnValue($transaction->user) }}
                                                @elseif (in_array($transaction->transaction_type_id, getPaymoneySettings('transaction_types')['web']['received']))
                                                    {{ getColumnValue($transaction->end_user) }}
                                                @endif
                                            ">
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    @if (in_array($transaction->transaction_type_id, getPaymoneySettings('transaction_types')['web']['sent']))
                                                        {{ getColumnValue($transaction->user) }}
                                                    @elseif (in_array($transaction->transaction_type_id, getPaymoneySettings('transaction_types')['web']['received']))
                                                        {{ getColumnValue($transaction->end_user) }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Receiver --}}
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="receiver">{{ __('Receiver') }}</label>

                                            <input type="hidden" class="form-control" name="receiver" value="
                                                @switch($transaction->transaction_type_id)
                                                    @case(module('TatumIo') ? 'Crypto_Sent' : false)
                                                    @case(module('TatumIo') ? 'Crypto_Received' : false)
                                                        {{ getColumnValue($transaction->end_user) }}
                                                        @break
                                                @endswitch
                                            ">

                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    @switch($transaction->transaction_type_id)
                                                        @case(module('TatumIo') ? Crypto_Sent : false)
                                                            {{ getColumnValue($transaction->end_user) }}
                                                            @break
                                                        @case(module('TatumIo') ? Crypto_Received : false)
                                                            {{ getColumnValue($transaction->user) }}
                                                            @break
                                                    @endswitch
                                                </p>
                                            </div>
                                        </div>

                                         <!-- TatumIo -->
                                        @if (module('TatumIo'))
                                            @if (isset($senderAddress))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="crypto_sender_address">{{ __('Sender Address') }}</label>
                                                    <input type="hidden" class="form-control" name="crypto_sender_address" value="{{ $senderAddress }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static" id="crypto_sender_address">{{ $senderAddress }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Receiver Address -->
                                            @if (isset($receiverAddress))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="crypto_receiver_address">{{ __('Receiver Address') }}</label>
                                                    <input type="hidden" class="form-control" name="crypto_receiver_address" value="{{ $receiverAddress }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static" id="crypto_receiver_address">{{ $receiverAddress }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Txid -->
                                            @if (isset($txId))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="crypto_txid">{{ $transaction->payment_method?->name }} {{ __('TxId') }}</label>
                                                    <input type="hidden" class="form-control" name="crypto_txid" value="{{ $txId }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static" id="crypto_txid">{{ wordwrap($txId, 50, "\n", true) }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Confirmations -->
                                            @if (isset($confirmations))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end" for="crypto_confirmations">{{ __('Confirmations') }}</label>
                                                    <input type="hidden" class="form-control" name="crypto_confirmations" value="{{ $confirmations }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static" id="crypto_confirmations">{{ $confirmations }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        @if ($transaction->uuid)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Transaction ID') }}</label>
                                                <input type="hidden" class="form-control" name="transactions_uuid" value="{{ $transaction->uuid }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->uuid }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Type -->
                                        @if ($transaction->transaction_type_id)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="type">{{ __('Type') }}</label>
                                                <input type="hidden" class="form-control" name="type" value="{{ str_replace('_', ' ', $transaction->transaction_type?->name) }}">
                                                <input type="hidden" class="form-control" name="transaction_type_id" value="{{ $transaction->transaction_type_id }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ ($transaction->transaction_type?->name == "Withdrawal") ? "Payout" : str_replace('_', ' ', $transaction->transaction_type?->name) }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Currency -->
                                        @if ($transaction->currency)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="currency">{{ __('Currency') }}</label>
                                                <input type="hidden" class="form-control" name="currency" value="{{ $transaction->currency?->code }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->currency?->code }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Payment Method -->
                                        @if (isset($transaction->payment_method_id))
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="payment_method">{{ __('Payment Method') }}</label>
                                                <input type="hidden" class="form-control" name="payment_method" value="{{ ($transaction->payment_method?->name == "Mts") ? settings('name') : $transaction->payment_method?->name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ ($transaction->payment_method?->name == "Mts") ? settings('name') : $transaction->payment_method?->name }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($transaction->created_at)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="created_at">{{ __('Date') }}</label>
                                                <input type="hidden" class="form-control" name="created_at" value="{{ $transaction->created_at }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ dateFormat($transaction->created_at) }}</p>
                                                </div>
                                            </div>
                                            @endif

                                        @if ($transaction->status)
                                            <div class="form-group row align-items-center">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="status">{{ __('Change Status') }}</label>
                                                <div class="col-sm-6">
                                                    <p class="form-control-static"><span class="label label-danger space-none" id="crypto-sent-status">{{ str_replace('_', ' ', $transaction->transaction_type?->name) }}{{ __(' Status Cannot Be Changed') }}</span></p>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-md-6 offset-md-3">
                                                <a id="cancel_anchor" class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/transactions') }}">{{ __('Back') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="pt-4 rounded">
                                        @if ($transaction->subtotal)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-6 fw-bold text-sm-end" for="subtotal">{{ __('Amount') }}</label>
                                                <div class="col-sm-6">
                                                    {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber($transaction->subtotal, $transaction->currency?->id)) }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row total-deposit-feesTotal-space">
                                            <label class="control-label col-sm-6 fw-bold text-sm-end" for="fee">{{ __('Network Fee') }}</label>

                                            @php
                                                $total_transaction_fees = $transaction->charge_percentage + $transaction->charge_fixed;
                                            @endphp

                                            <input type="hidden" class="form-control" name="fee" value="{{ ($total_transaction_fees) }}">
                                            <div class="col-sm-6">
                                                @if (module('TatumIo') && $transaction->currency?->type == 'crypto_asset')
                                                    <p class="form-control-static">
                                                        @if ($transaction->transaction_type_id == Crypto_Sent)
                                                            {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber($network_fee, $transaction->currency_id)) }}
                                                        @elseif ($transaction->transaction_type_id == Crypto_Received)
                                                            {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber(0.00000000, $transaction->currency_id)) }}
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <hr class="increase-hr-height">

                                        @if ($transaction->total)
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label col-sm-6 fw-bold text-sm-end" for="total">{{ __('Total') }}</label>
                                                <input type="hidden" class="form-control" name="total" value="{{ ($transaction->total) }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static">
                                                        @if (module('TatumIo') && ($transaction->currency->type == 'crypto_asset') && ($transaction->transaction_type_id == Crypto_Sent))
                                                            {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber($transaction->subtotal + $network_fee, $transaction->currency_id)) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
