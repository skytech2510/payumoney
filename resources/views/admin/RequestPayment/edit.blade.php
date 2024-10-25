@extends('admin.layouts.master')
@section('title', __('Edit Request Payment'))

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Request Payment Details') }}</div>
                </div>

                <div>
                    @if ($request_payments->status)
                        <p class="text-left mb-0 f-18">{{ __('Status') }} : 
                            @if ($request_payments->status == 'Success')
                                <span class="text-green">{{ __('Success') }}</span>
                            @endif
                            @if ($request_payments->status == 'Pending')
                                <span class="text-blue">{{ __('Pending') }}</span>
                            @endif
                            @if ($request_payments->status == 'Refund')
                                <span class="text-warning">{{ __('Refunded') }}</span>
                            @endif
                            @if ($request_payments->status == 'Blocked')
                                <span class="text-red">{{ __('canceled') }}</span>
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <section class="min-vh-100">
        <div class="my-30">
            <form action="{{ url(config('adminPrefix') . '/request_payments/update') }}" class="row form-horizontal"
                method="POST">
                {{ csrf_field() }}
                <!-- Page title start -->
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="p-4">
                                        <input type="hidden" value="{{ $request_payments->id }}" name="id" id="id">
                                        <input type="hidden" value="{{ $request_payments->uuid }}" name="uuid" id="uuid">
                                        <input type="hidden" value="{{ $request_payments->user_id }}" name="user_id" id="user_id">
                                        <input type="hidden" value="{{ $request_payments->currency?->id }}" name="currency_id" id="currency_id">
                                        <input type="hidden" value="{{ $request_payments->note }}" name="note" id="note">

                                        @if (isset($transaction))
                                            <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
                                            <input type="hidden" value="{{ $transaction->transaction_type?->name }}" name="transaction_type" id="transaction_type">
                                            <input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
                                            <input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">

                                            <input type="hidden" value="{{ $transaction->user_type }}" name="user_type" id="user_type">

                                            <input type="hidden" value="{{ $transaction->percentage }}" name="percentage" id="percentage">
                                            <input type="hidden" value="{{ $transaction->charge_percentage }}" name="charge_percentage" id="charge_percentage">
                                            <input type="hidden" value="{{ $transaction->charge_fixed }}" name="charge_fixed" id="charge_fixed">
                                        @endif

                                       
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="user">{{ __('Request From') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ getColumnValue($request_payments->user) }}
                                                </p>
                                            </div>
                                        </div>
                                

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="receiver">{{ __('Request To') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ getColumnValue($request_payments->receiver) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3"
                                                for="request_payments_uuid">{{ __('Transaction ID') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ $request_payments->uuid }}</p>
                                            </div>
                                        </div>

                                        @if ($request_payments->email)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3"
                                                    for="request_payments_email">{{ __('Email') }}</label>
                                                <input type="hidden" class="form-control f-14"
                                                    name="request_payments_email"
                                                    value="{{ $request_payments->email }}" id="request_payments_email">
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">
                                                        {{ $request_payments->email }}</p>
                                                </div>
                                            </div>
                                        @endif


                                        @if ($request_payments->currency)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="currency">{{ __('Currency') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ optional($request_payments->currency)->code }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3"
                                                for="created_at">{{ __('Date') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ dateFormat($request_payments->created_at) }}</p>
                                            </div>
                                        </div>

                                        <div class="form-group row align-items-center">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="status">{{ __('Change Status') }}</label>
                                            <div class="col-sm-6">

                                                @if (isset($transactionOfRefunded) && isset($requestPaymentsOfRefunded))
                                                    <p class="form-control-static f-14"><span
                                                            class="label label-success">{{ __('Already Refunded') }}</span></p>

                                                    <p class="form-control-static f-14"><span
                                                            class="label label-danger">{{ __('Refunded Reference') }}: <i>
                                                                <a id="requestPaymentsOfRefunded"
                                                                    href="{{ url(config('adminPrefix') . "/request_payments/edit/$requestPaymentsOfRefunded->id") }}">(
                                                                    {{ $transactionOfRefunded->refund_reference }}
                                                                    )</a>
                                                            </i>
                                                        </span>
                                                    </p>
                                                @else
                                                    <select class="form-control select2 w-60" name="status" id="status">

                                                        @if (isset($transaction->status) && $transaction->status == 'Success')
                                                            <option value="Success"
                                                                {{ isset($request_payments->status) && $request_payments->status == 'Success' ? 'selected' : '' }}>
                                                                {{ __('Success') }}</option>
                                                            <option value="Refund"
                                                                {{ isset($request_payments->status) && $request_payments->status == 'Refund' ? 'selected' : '' }}>
                                                                {{ __('Refunded') }}</option>

                                                        @elseif ($request_payments->status == 'Pending')
                                                            <option value="Pending"
                                                                {{ isset($request_payments->status) && $request_payments->status == 'Pending' ? 'selected' : '' }}>
                                                                {{ __('Pending') }}</option>
                                                            <option value="Blocked"
                                                                {{ isset($request_payments->status) && $request_payments->status == 'Blocked' ? 'selected' : '' }}>
                                                                {{ __('Cancel') }}</option>

                                                        @elseif ($request_payments->status == 'Blocked')
                                                            <option value="Pending"
                                                                {{ isset($request_payments->status) && $request_payments->status == 'Pending' ? 'selected' : '' }}>
                                                                {{ __('Pending') }}</option>
                                                            <option value="Blocked"
                                                                {{ isset($request_payments->status) && $request_payments->status == 'Blocked' ? 'selected' : '' }}>
                                                                {{ __('Cancel') }}</option>
                                                        @endif
                                                    </select>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row">
                                            @if (!isset($transactionOfRefunded->refund_reference))
                                                <div class="col-md-6 offset-md-3">
                                                    <a id="cancel_anchor"
                                                    class="btn btn-theme-danger f-14 me-1"
                                                    href="{{ url(config('adminPrefix') . '/request_payments') }}">{{ __('Cancel') }}</a>
                                                    <button type="submit" class="btn btn-theme f-14"
                                                        id="request_payment">
                                                        <i class="fa fa-spinner fa-spin d-none"></i> <span
                                                            id="request_payment_text">{{ __('Update') }}</span>
                                                    </button>
                                                </div>
                                            @endif
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
                                    <div class="pt-4">

                                        @if ($request_payments->amount)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="amount">{{ __('Requested Amount') }}</label>
                                                <input type="hidden" class="form-control f-14" name="amount"
                                                    value="{{ $request_payments->amount }}" id="amount">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14 pull-left">
                                                        {{ moneyFormat($request_payments->currency?->symbol, formatNumber($request_payments->amount, $request_payments->currency?->id)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row total-deposit-feesTotal-space">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="accept_amount">{{ __('Accepted Amount') }}</label>
                                            <input type="hidden" class="form-control f-14" name="accept_amount"
                                                value="{{ $request_payments->accept_amount }}" id="accept_amount">
                                            <div class="col-sm-6">
                                                <p class="form-control-static f-14 pull-left">
                                                    {{ moneyFormat($request_payments->currency?->symbol, formatNumber($request_payments->accept_amount, $request_payments->currency?->id)) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="form-group row total-deposit-feesTotal-space-request-payment">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-6 d-flex justify-content-end" for="fee">{{ __('Fees') }}
                                                <span>
                                                    <small class="transactions-edit-fee">
                                                        @if (isset($transaction) && $transaction->transaction_type_id == Request_Received)
                                                            ({{ formatNumber($transaction->percentage, $request_payments->currency?->id) }}% +
                                                            {{ formatNumber($transaction->charge_fixed, $request_payments->currency?->id) }})
                                                        @else
                                                            ({{ 0 }}%+{{ 0 }})
                                                        @endif
                                                    </small>
                                                </span>
                                            </label>

                                            <div class="col-sm-6">
                                                <p class="form-control-static f-14 pull-left">
                                                    {{ isset($transaction) ? moneyFormat($request_payments->currency?->symbol, formatNumber($transaction->charge_percentage + $transaction->charge_fixed, $request_payments->currency?->id)) : moneyFormat($request_payments->currency?->symbol, formatNumber(0.0, $request_payments->currency?->id)) }}
                                                </p>
                                            </div>
                                        </div>

                                        <hr class="increase-hr-height-request-payment">
                                        @php
                                            if (isset($transaction)) {
                                                $total = $transaction->charge_percentage + $transaction->charge_fixed + $request_payments->accept_amount;
                                            } else {
                                                $total = $request_payments->amount;
                                            }
                                        @endphp

                                        <div class="form-group row total-deposit-space-request-payment">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="total">{{ __('Total') }}</label>
                                            <div class="col-sm-6">
                                                <p class="form-control-static f-14">
                                                    {{ moneyFormat($request_payments->currency?->symbol, formatNumber($total, $request_payments->currency?->id)) }}
                                                </p>
                                            </div>
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
        $(".select2").select2({});

        // disabling submit and cancel button after clicking it
        $(document).ready(function() {
            $('form').submit(function() {
                $("#request_payment").attr("disabled", true);
                $('#cancel_anchor').attr("disabled", "disabled");
                $(".fa-spin").removeClass("d-none");
                $("#request_payment_text").text('Updating...');

                // Click False
                $('#request_payment').click(false);
                $('#cancel_anchor').click(false);
            });

            $('#requestPaymentsOfRefunded').css('color', 'white');
        });
    </script>
@endpush
