@extends('admin.layouts.master')
@section('title', __('Edit Transaction'))

@section('page_content')

<div class="box box-default">
	<div class="box-body">
		<div class="d-flex justify-content-between">
			<div>
				<div class="top-bar-title padding-bottom pull-left">{{ __('Transaction Details') }}</div>
				@if (isset($dispute))
					@if( $transaction->transaction_type_id == Payment_Sent && $transaction->status == 'Success' && $dispute->status != 'Open')
						<a id="dispute_{{ $transaction->id }}" href="{{ url(config('adminPrefix').'/dispute/add/'.$transaction->id) }}" class="btn button-secondary btn-sm pull-right ml-10 mt-10">{{ __('Open Dispute') }}</a>
					@endif
				@endif
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

                                        <input type="hidden" value="{{ base64_encode($transaction->merchant_id) }}" name="merchant_id" id="merchant_id">
                                        <input type="hidden" class="form-control" name="subtotal" value="{{ $transaction->subtotal }}">


                                        <!--MerchantPayment-->
                                        @if (isset($transaction->merchant_payment))
                                            <input type="hidden" value="{{ base64_encode($transaction->merchant_payment?->gateway_reference) }}" name="gateway_reference" id="gateway_reference">
                                            <input type="hidden" value="{{ $transaction->merchant_payment?->order_no }}" name="order_no" id="order_no">
                                            <input type="hidden" value="{{ $transaction->merchant_payment?->item_name }}" name="item_name" id="item_name">
                                        @endif

                                        {{-- User --}}
                                        <div class="form-group row">

                                            @php
                                                echo getStatusInputLabel($transaction->transaction_type_id, 'user')
                                            @endphp

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

                                        <!-- Receiver -->
                                        <div class="form-group row">
                                            @php
                                                echo getStatusInputLabel($transaction->transaction_type_id, 'receiver');
                                            @endphp

                                            <input type="hidden" class="form-control" name="receiver" value="
                                                @switch($transaction->transaction_type_id)
                                                    @case(Deposit)
                                                    @case(Exchange_From)
                                                    @case(Exchange_To)
                                                    @case(Withdrawal)
                                                        {{ getColumnValue($transaction->end_user) }}
                                                        @break
                                                    @case(Transferred)
                                                    @case(Received)

                                                            @if ($transaction->transfer?->receiver)
                                                                {{ getColumnValue($transaction->transfer->receiver) }}
                                                            @elseif ($transaction->transfer?->email)
                                                                {{ $transaction->transfer?->email }}
                                                            @elseif ($transaction->transfer?->phone)
                                                                {{ $transaction->transfer?->phone }}
                                                            @else
                                                                {{ '-' }}
                                                            @endif
                                                        @break
                                                    @case(Request_Sent)
                                                    @case(Request_Received)
                                                        {{ isset($transaction->request_payment->receiver) ? getColumnValue($transaction->request_payment->receiver) : $transaction->request_payment->email }}
                                                        @break
                                                    @case(Payment_Sent)
                                                        {{ getColumnValue($transaction->end_user) }}
                                                        @break
                                                    @case(Payment_Received)
                                                        {{ getColumnValue($transaction->user) }}
                                                        @break
                                                @endswitch
                                            ">

                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    @switch($transaction->transaction_type_id)
                                                        @case(Deposit)
                                                        @case(Exchange_From)
                                                        @case(Exchange_To)
                                                        @case(Withdrawal)
                                                            {{ getColumnValue($transaction->end_user) }}
                                                            @break
                                                        @case(Transferred)
                                                        @case(Received)
                                                            @if ($transaction->transfer?->receiver)
                                                                {{ getColumnValue($transaction->transfer->receiver) }}
                                                            @elseif ($transaction->transfer?->email)
                                                                {{ $transaction->transfer?->email }}
                                                            @elseif ($transaction->transfer?->phone)
                                                                {{ $transaction->transfer?->phone }}
                                                            @else
                                                                {{ '-' }}
                                                            @endif

                                                            @break
                                                        @case(Request_Sent)
                                                        @case(Request_Received)
                                                            {{ isset($transaction->request_payment?->receiver) ? getColumnValue($transaction->request_payment->receiver) : $transaction->request_payment->email }}
                                                            @break
                                                        @case(Payment_Sent)
                                                            {{ getColumnValue($transaction->end_user) }}
                                                            @break
                                                        @case(Payment_Received)
                                                            {{ getColumnValue($transaction->user) }}
                                                            @break
                                                    @endswitch
                                                </p>
                                            </div>
                                        </div>

                                        @if ($transaction->uuid)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end" for="transactions_uuid">{{ __('Transaction ID') }}</label>
                                                <input type="hidden" class="form-control" name="transactions_uuid" id="transactions_uuid" value="{{ $transaction->uuid }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->uuid }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Type -->
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Type') }}</label>
                                            <input type="hidden" class="form-control" name="type" value="{{ str_replace('_', ' ', getColumnValue($transaction->transaction_type, 'name')) }}">
                                            <input type="hidden" class="form-control" name="transaction_type_id" value="{{ $transaction->transaction_type_id }}">
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{{ str_replace('_', ' ', getColumnValue($transaction->transaction_type, 'name')) }}</p>
                                            </div>
                                        </div>

                                        <!-- Currency -->
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 fw-bold text-sm-end" for="currency">{{ __('Currency') }}</label>
                                            <input type="hidden" class="form-control" name="currency" id="currency" value="{{ getColumnValue($transaction->currency, 'code') }}">
                                            <div class="col-sm-9">
                                                <p class="form-control-static">{{ getColumnValue($transaction->currency, 'code') }}</p>
                                            </div>
                                        </div>

                                        @if (in_array($transaction->transaction_type_id, [Payment_Sent, Payment_Received] ))
                                            <!-- Fee Bearer -->
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Fee Bearer') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">
                                                        @php
                                                            $feeBearer = $transaction->merchant_payment?->fee_bearer;
                                                            $transactionTypeId = $transaction->transaction_type_id;
                                                        @endphp

                                                        @if ($feeBearer == 'Merchant' && $transactionTypeId == Payment_Received)
                                                            {{ getColumnValue($transaction->user) }}
                                                        @elseif ($feeBearer == 'Merchant' && $transactionTypeId == Payment_Sent)
                                                            {{ getColumnValue($transaction->end_user) }}
                                                        @elseif ($feeBearer == 'User' && $transactionTypeId == Payment_Sent)
                                                            {{ getColumnValue($transaction->user) }}
                                                        @elseif ($feeBearer == 'User' && $transactionTypeId == Payment_Received)
                                                            {{ getColumnValue($transaction->end_user) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Payment Method -->
                                        @if (isset($transaction->payment_method_id))
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Payment Method') }}</label>
                                                <input type="hidden" class="form-control" name="payment_method" value="{{ ($transaction->payment_method?->id == Mts) ? settings('name') : $transaction->payment_method?->name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ ($transaction->payment_method?->id == Mts) ? settings('name') : $transaction->payment_method?->name }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if (isset($transaction->payment_status))
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Payment Status') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{!! getStatusText($transaction->payment_status) !!}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- If bank deposit  -->
                                        @if ($transaction->bank)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Bank Name') }}</label>
                                                <input type="hidden" class="form-control" name="bank_name" value="{{ $transaction->bank?->bank_name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->bank?->bank_name }}</p>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Branch Name') }}</label>
                                                <input type="hidden" class="form-control" name="bank_branch_name" value="{{ $transaction->bank?->bank_branch_name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->bank?->bank_branch_name }}</p>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Account Name') }}</label>
                                                <input type="hidden" class="form-control" name="account_name" value="{{ $transaction->bank?->account_name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->bank?->account_name }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- If MobileMoney deposit  -->
                                        @if (config('mobilemoney.is_active') && $transaction->mobilemoney)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Network Name') }}</label>
                                                <input type="hidden" class="form-control" name="mobilemoney_id" value="{{ $transaction->mobilemoney?->mobilemoney_name }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->mobilemoney?->mobilemoney_name ?? '' }}</p>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Mobile Number') }}</label>
                                                <input type="hidden" class="form-control" name="bank_branch_name" value="{{ $transaction->mobilemoney?->mobilemoney_number }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ $transaction->mobilemoney?->mobilemoney_number ?? '' }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($transaction->file?->filename)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Attached File') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">
                                                        @if ($transaction->payment_method?->id == Bank)
                                                            <a href="{{ url('public/uploads/files/bank_attached_files').'/'.$transaction->file?->filename }}" download={{ $transaction->file?->filename }}><i class="fa fa-fw fa-download"></i>
                                                                {{ $transaction->file?->originalname }}
                                                            </a>
                                                        @elseif (config('mobilemoney.is_active') && $transaction->payment_method?->id == (defined('MobileMoney') ? MobileMoney : ''))
                                                            <a href="{{ url('public/uploads/files/mobilemoney_attached_files').'/'.$transaction->file?->filename }}" download={{ $transaction->file?->filename }}><i class="fa fa-fw fa-download"></i>
                                                            {{ $transaction->file?->originalname }}
                                                            </a>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($transaction->transaction_type_id == Withdrawal)
                                            @if ($transaction->payment_method?->id == Bank)
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Account Name') }}</label>
                                                    <input type="hidden" class="form-control" name="account_name" value="{{ $transaction->withdrawal?->withdrawal_detail?->account_name }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->account_name }}</p>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Account Number') }}/ {{ __('IBAN') }}</label>
                                                    <input type="hidden" class="form-control" name="account_number" value="{{ $transaction->withdrawal?->withdrawal_detail?->account_number }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->account_number }}</p>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('SWIFT Code') }}</label>
                                                    <input type="hidden" class="form-control" name="swift_code" value="{{ $transaction->withdrawal?->withdrawal_detail?->swift_code }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->swift_code }}</p>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Bank Name') }}</label>
                                                    <input type="hidden" class="form-control" name="bank_name" value="{{ $transaction->withdrawal?->withdrawal_detail?->bank_name }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->bank_name }}</p>
                                                    </div>
                                                </div>
                                            @elseif ($transaction->payment_method?->id == Paypal)
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Paypal ID') }}</label>
                                                    <input type="hidden" class="form-control" name="bank_name" value="{{ $transaction->withdrawal?->withdrawal_detail?->email ?? '' }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->email ?? '' }}</p>
                                                    </div>
                                                </div>
                                            @elseif ($transaction->payment_method?->id == Crypto)
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Withdrawal Address') }}</label>
                                                    <input type="hidden" class="form-control" name="crypto_address" value="{{ $transaction->withdrawal?->withdrawal_detail?->crypto_address ?? '' }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->crypto_address ?? '' }}</p>
                                                    </div>
                                                </div>
                                            @elseif (config('mobilemoney.is_active') && $transaction->payment_method?->id == (defined('MobileMoney') ? MobileMoney : ''))
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Network') }}</label>
                                                    <input type="hidden" class="form-control" name="mobilemoney_id" value="{{ $transaction->withdrawal?->withdrawal_detail?->mobilemoney?->mobilemoney_name ?? '' }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->mobilemoney?->mobilemoney_name ?? '' }}</p>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Mobile Number') }}</label>
                                                    <input type="hidden" class="form-control" name="mobile_number" value="{{ $transaction->withdrawal?->withdrawal_detail?->mobile_number ?? '' }}">
                                                    <div class="col-sm-9">
                                                        <p class="form-control-static">{{ $transaction->withdrawal?->withdrawal_detail?->mobile_number ?? '' }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        <!-- Transaction Date -->
                                        @if ($transaction->created_at)
                                            <div class="form-group row">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Date') }}</label>
                                                <input type="hidden" class="form-control" name="created_at" value="{{ $transaction->created_at }}">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static">{{ dateFormat($transaction->created_at) }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($transaction->status)
                                            <div class="form-group row align-items-center">
                                                <label class="control-label col-sm-3 fw-bold text-sm-end">{{ __('Change Status') }}</label>
                                                <div class="col-sm-6">

                                                    @if (isset($transaction->refund_reference) && isset($transactionOfRefunded))
                                                        <p class="form-control-static"><span class="label label-success">{{ __('Already Refunded') }}</span></p>
                                                        <p class="form-control-static">
                                                            <span class="label label-danger">{{ __('Refunded Reference') }}:
                                                                <i><a id="transactionOfRefunded" href="{{  url(config('adminPrefix')."/transactions/edit/$transactionOfRefunded->id") }}">( {{ $transaction->refund_reference }} )</a></i>
                                                            </span>
                                                        </p>

                                                    @else
                                                        <select class="form-control select2 w-60" name="status">
                                                            @if ($transaction->transaction_type_id == Deposit)
                                                                <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                <option value="Pending"  {{ $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>

                                                            @elseif ($transaction->transaction_type_id == Transferred || $transaction->transaction_type_id == Received)
                                                                @if ($transaction->status == 'Success')
                                                                    <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                    <option value="Pending"  {{ $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                    <option value="Refund" {{ $transaction->status ==  'Refund' ? 'selected':"" }}>{{ __('Refund') }}</option>
                                                                    <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>
                                                                @else
                                                                    <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                    <option value="Pending"  {{ $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                    <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>
                                                                @endif

                                                            @elseif ($transaction->transaction_type_id == Exchange_From || $transaction->transaction_type_id == Exchange_To)
                                                                <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>

                                                            @elseif ($transaction->transaction_type_id == Request_Sent || $transaction->transaction_type_id == Request_Received)
                                                                @if ($transaction->status == 'Pending')
                                                                    <option value="Pending" {{ $transaction->status ==  'Pending'? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                    <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>

                                                                @elseif ($transaction->status == 'Blocked')
                                                                    <option value="Pending" {{ $transaction->status ==  'Pending'? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                    <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>

                                                                @elseif ($transaction->status == 'Success')
                                                                    <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                    <option value="Refund"  {{ $transaction->status == 'Refund' ? 'selected':"" }}>{{ __('Refund') }}</option>
                                                                @endif

                                                            @elseif ($transaction->transaction_type_id == Withdrawal)
                                                                <option value="Success" {{ $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                <option value="Pending"  {{ $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                <option value="Blocked"  {{ $transaction->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>

                                                            @elseif ($transaction->transaction_type_id == Payment_Sent || $transaction->transaction_type_id == Payment_Received)
                                                                @if ($transaction->status ==  'Success')
                                                                    <option value="Success" {{ isset($transaction->status) && $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                    <option value="Pending"  {{ isset($transaction->status) && $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                    <option value="Refund"  {{ isset($transaction->status) && $transaction->status == 'Refund' ? 'selected':"" }}>{{ __('Refund') }}</option>
                                                                @else
                                                                    <option value="Success" {{ isset($transaction->status) && $transaction->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                                                    <option value="Pending"  {{ isset($transaction->status) && $transaction->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                                                @endif
                                                            @endif
                                                        </select>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-md-6 offset-md-3">
                                                <a id="cancel_anchor" class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/transactions') }}">{{ __('Cancel') }}</a>
                                                @if (!isset($transaction->refund_reference))
                                                    <button type="submit" class="btn btn-theme f-14" id="request_payment">
                                                        <i class="fa fa-spinner fa-spin d-none"></i> <span id="transactions_edit_text">{{ __('Update') }}</span>
                                                    </button>
                                                @endif
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
                                                <label class="control-label col-sm-6 fw-bold text-sm-end">{{ __('Amount') }}</label>
                                                <div class="col-sm-6">
                                                    {{ moneyFormat(optional($transaction->currency)->symbol, formatNumber($transaction->subtotal, optional($transaction->currency)->id)) }}
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row total-deposit-feesTotal-space">
                                            <label class="control-label col-sm-6 d-flex fw-bold justify-content-end">{{ __('Fees') }}
                                                <span>
                                                    <small class="transactions-edit-fee">
                                                        @if (isset($transaction))
                                                            ({{ formatNumber($transaction->percentage, $transaction->currency?->id) }}% +
                                                            {{formatNumber($transaction->charge_fixed, $transaction->currency?->id)}})
                                                        @else
                                                            (0% + 0)
                                                        @endif
                                                    </small>
                                                </span>
                                            </label>
                                            @php
                                                $total_transaction_fees = $transaction->charge_percentage + $transaction->charge_fixed;
                                            @endphp

                                            <input type="hidden" class="form-control" name="fee" value="{{ ($total_transaction_fees) }}">
                                            <div class="col-sm-6">
                                                <p class="form-control-static">{{  moneyFormat(optional($transaction->currency)->symbol, formatNumber($total_transaction_fees, optional($transaction->currency)->id)) }}</p>
                                            </div>
                                        </div>

                                        <hr class="increase-hr-height">

                                        @if ($transaction->total)
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label col-sm-6 fw-bold text-sm-end">{{ __('Total') }}</label>
                                                <input type="hidden" class="form-control" name="total" value="{{ ($transaction->total) }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static">
                                                        {{  moneyFormat(optional($transaction->currency)->symbol, str_replace("-",'',formatNumber($transaction->total, optional($transaction->currency)->id)) ) }}
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

@push('extra_body_scripts')

<script type="text/javascript">
	$(window).on('load', function()
	{
		$(".select2").select2({});
	});

	// disabling submit and cancel button after clicking it
	$(document).ready(function ()
	{
        $('form').submit(function ()
        {
            $("#transactions_edit").attr("disabled", true);
            $('#cancel_anchor').attr("disabled", "disabled");
            $(".fa-spin").removeClass("d-none");
            $("#transactions_edit_text").text('Updating...');

	        // click False
            $('#transactions_edit').click(false);
            $('#cancel_anchor').click(false);
        });
        $('#transactionOfRefunded').css('color', 'white');
	});
</script>

@endpush
