@extends('admin.layouts.master')
@section('title', __('Edit Merchant Payment'))

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Edit Merchant Payment') }}</div>
                </div>

                <div>
                    <p class="text-left mb-0 text-18">{{ __('Status') }}: {!! getStatusText($merchant_payment->status) !!}</p>
                </div>
            </div>
        </div>
    </div>

    <section class="min-vh-100">
        <div class="my-30">
            <form action="{{ url(config('adminPrefix') . '/merchant_payments/update') }}" class="form-horizontal row"
                id="merchant_payment_form" method="POST">
                {{ csrf_field() }}
                <!-- Page title start -->
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="p-4">
                                        <input type="hidden" value="{{ $merchant_payment->id }}" name="id" id="id">
                                        <input type="hidden" value="{{ base64_encode($merchant_payment->merchant?->id) }}" name="merchant_id" id="merchant_id">
                                        <input type="hidden" value="{{ $merchant_payment->currency?->id }}" name="currency_id" id="currency_id">
                                        <input type="hidden" value="{{ base64_encode($merchant_payment->payment_method?->id) }}" name="payment_method_id" id="payment_method_id">
                                        <input type="hidden" value="{{ $merchant_payment->user_id }}" name="paid_by_user_id" id="paid_by_user_id">
                                        <input type="hidden" value="{{ base64_encode($merchant_payment->gateway_reference) }}" name="gateway_reference" id="gateway_reference">
                                        <input type="hidden" value="{{ $merchant_payment->order_no }}" name="order_no" id="order_no">
                                        <input type="hidden" value="{{ $merchant_payment->item_name }}" name="item_name" id="item_name">
                                        <input type="hidden" value="{{ $merchant_payment->charge_percentage }}" name="charge_percentage" id="charge_percentage">
                                        <input type="hidden" value="{{ $merchant_payment->charge_fixed }}" name="charge_fixed" id="charge_fixed">

                                        @if (!empty($transaction))
                                            <input type="hidden" value="{{ $transaction->transaction_type_id }}"
                                                name="transaction_type_id" id="transaction_type_id">
                                            <input type="hidden" value="{{ $transaction->transaction_type?->name }}"
                                                name="transaction_type" id="transaction_type">
                                            <input type="hidden" value="{{ $transaction->status }}"
                                                name="transaction_status" id="transaction_status">
                                            <input type="hidden" value="{{ $transaction->user_type }}"
                                                name="user_type" id="user_type">
                                            <input type="hidden" value="{{ $transaction->transaction_reference_id }}"
                                                name="transaction_reference_id" id="transaction_reference_id">
                                            <input type="hidden" value="{{ $transaction->percentage }}"
                                                name="percentage" id="percentage">
                                        @endif
                                        
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="merchant_user">{{ __('Merchant') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">{{ getColumnValue($merchant_payment->merchant->user) }}</p>
                                            </div>
                                        </div>

                                       
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3">{{ __('Merchant Type') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ $merchant_payment->merchant?->type }}</p>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="user">{{ __('User') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">{{ getColumnValue($merchant_payment->user) }}</p>
                                            </div>
                                        </div>

                                        
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="mp_uuid">{{ __('Transaction ID') }}</label>
                                            <input type="hidden" class="form-control" name="mp_uuid"
                                                value="{{ $merchant_payment->uuid }}" id="mp_uuid">
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ $merchant_payment->uuid }}</p>
                                            </div>
                                        </div>

                                        
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3"
                                                for="currency">{{ __('Currency') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ $merchant_payment->currency?->code }}</p>
                                            </div>
                                        </div>

                                        <!-- Fee Bearer -->
                                        <div class="form-group row">
                                            <label class="control-label col-sm-3 f-14 fw-bold text-sm-end">{{ __('Fee Bearer') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">{{ $merchant_payment->fee_bearer == 'Merchant' ? getColumnValue($merchant_payment->merchant->user) : getColumnValue($merchant_payment->user) }}</p>
                                            </div>
                                        </div>
                                       
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="payment_method">{{ __('Payment Method') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ optional($merchant_payment->payment_method)->name == 'Mts' ? settings('name') : optional($merchant_payment->payment_method)->name }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3"
                                                for="created_at">{{ __('Date') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ dateFormat($merchant_payment->created_at) }}</p>
                                            </div>
                                        </div>

                                        @if ($merchant_payment->status)
                                            <div class="form-group row align-items-center">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="status">{{ __('Change Status') }}</label>
                                                <div class="col-sm-6">

                                                    @if (isset($transactionOfRefunded) && isset($merchantPaymentOfRefunded))

                                                        <p class="form-control-static f-14"><span
                                                                class="label label-success">{{ __('Already Refunded') }}</span></p>

                                                        <p class="form-control-static f-14"><span
                                                                class="label label-danger">{{ __('Refunded Reference') }}: <i>
                                                                    <a id="merchantPaymentOfRefunded"
                                                                        href="{{ url(config('adminPrefix') . "/merchant_payments/edit/$merchantPaymentOfRefunded->id") }}">(
                                                                        {{ $transactionOfRefunded->refund_reference }}
                                                                        )</a>
                                                                </i>
                                                            </span>
                                                        </p>

                                                    @else
                                                        <select class="form-control select2 w-60" name="status" id="status">
                                                            @if ($merchant_payment->status == 'Success')
                                                                <option value="Success"
                                                                    {{ isset($merchant_payment->status) && $merchant_payment->status == 'Success' ? 'selected' : '' }}>
                                                                    {{ __('Success') }}</option>
                                                                <option value="Pending"
                                                                    {{ isset($merchant_payment->status) && $merchant_payment->status == 'Pending' ? 'selected' : '' }}>
                                                                    {{ __('Pending') }}</option>
                                                                <option value="Refund"
                                                                    {{ isset($merchant_payment->status) && $merchant_payment->status == 'Refund' ? 'selected' : '' }}>
                                                                    {{ __('Refund') }}</option>
                                                            @else
                                                                <option value="Success"
                                                                    {{ isset($merchant_payment->status) && $merchant_payment->status == 'Success' ? 'selected' : '' }}>
                                                                    {{ __('Success') }}</option>
                                                                <option value="Pending"
                                                                    {{ isset($merchant_payment->status) && $merchant_payment->status == 'Pending' ? 'selected' : '' }}>
                                                                    {{ __('Pending') }}</option>
                                                            @endif
                                                        </select>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        <div class="row">
                                            @if (!isset($transactionOfRefunded->refund_reference))
                                                <div class="col-md-6 offset-md-3">
                                                    <a id="cancel_anchor"
                                                    class="btn btn-theme-danger f-14 me-1"
                                                    href="{{ url(config('adminPrefix') . '/merchant_payments') }}">{{ __('Cancel') }}</a>
                                                    <button type="submit" class="btn btn-theme f-14"
                                                        id="merchant_payment_edit">
                                                        <i class="fa fa-spinner fa-spin d-none"></i>
                                                        <span id="merchant_payment_edit_text">{{ __('Update') }}</span>
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

                                        @if ($merchant_payment->amount)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="amount">{{ __('Amount') }}</label>
                                                <input type="hidden" class="form-control" name="amount"
                                                    value="{{ $merchant_payment->amount }}" id="amount">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($merchant_payment->amount)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row total-deposit-feesTotal-space">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-6 d-flex justify-content-end" for="feesTotal">{{ __('Fees') }}
                                                <span>
                                                    <small class="transactions-edit-fee">
                                                        ({{ formatNumber($merchant_payment->percentage, $merchant_payment->currency_id) }}% +
                                                        {{ formatNumber($merchant_payment->charge_fixed, $merchant_payment->currency_id) }})
                                                    </small>
                                                </span>
                                            </label>

                                            @php
                                                $feesTotal = $merchant_payment->charge_percentage + $merchant_payment->charge_fixed;
                                            @endphp

                                            <div class="col-sm-6">
                                                <p class="form-control-static f-14">
                                                    {{ moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($feesTotal, $merchant_payment->currency_id)) }}
                                                </p>
                                            </div>
                                        </div>

                                        <hr class="increase-hr-height">

                                        @php
                                            $total = $feesTotal + $merchant_payment->amount;
                                        @endphp

                                        @if (isset($total))
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="total">{{ __('Total') }}</label>
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($total)) }}
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
            </form>
        </div>
    </section>
@endsection

@push('extra_body_scripts')

    <script type="text/javascript">
        $(".select2").select2({});

        // disabling submit and cancel button after form submit
        $(document).ready(function() {
            $('form').submit(function() {
                $("#merchant_payment_edit").attr("disabled", true);

                $('#cancel_anchor').attr("disabled", "disabled");

                $(".fa-spin").removeClass("d-none");

                $("#merchant_payment_edit_text").text('Updating...');

                // Click False
                $('#merchant_payment_edit').click(false);
                $('#cancel_anchor').click(false);
            });

            $('#merchantPaymentOfRefunded').css('color', 'white');
        });
    </script>

@endpush
