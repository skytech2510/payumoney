@extends('admin.layouts.master')
@section('title', __('Edit Currency Exchange'))

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Exchange Details') }}</div>
                </div>

                <div>
                    @if ($exchange->status)
                        <p class="text-left mb-0 f-18">{{ __('Status') }} : 
                            @if ($exchange->status == 'Success')
                                <span class="text-green">{{ __('Success') }}</span>
                            @endif
                            @if ($exchange->status == 'Blocked')
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
            <form action="{{ url(config('adminPrefix') . '/exchange/update') }}" class="form-horizontal row"
                id="exchange_form" method="POST">
                @csrf
                <!-- Page title start -->
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="panel">
                                <div>
                                    <div class="p-4">
                                        <input type="hidden" value="{{ $exchange->id }}" name="id" id="id">
                                        <input type="hidden" value="{{ $exchange->type }}" name="type" id="type">
                                        <input type="hidden" value="{{ $exchange->user_id }}" name="user_id" id="user_id">
                                        <input type="hidden" value="{{ $exchange->currency_id }}" name="currency_id" id="currency_id">

                                        <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
                                        <input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
                                        <input type="hidden" value="{{ $transaction->transaction_reference_id }}"  name="transaction_reference_id" id="transaction_reference_id">

                                        
                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="user">{{ __('User') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">{{ getColumnValue($exchange->user) }}</p>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="exchange_uuid">{{ __('Transaction
                                                ID') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">{{ $exchange->uuid }}</p>
                                            </div>
                                        </div>

                                        @if ($exchange->from_wallet)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="from_wallet">{{ __('Exchange
                                                    From') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">
                                                        {{ $exchange->fromWallet?->currency?->code }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($exchange->to_wallet)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="to_wallet">{{ __('Exchange
                                                    To') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">
                                                        {{ $exchange->toWallet?->currency?->code }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($exchange->exchange_rate)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="exchange_rate">{{ __('Exchange
                                                    Rate') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->currency)->symbol, (float) $exchange->exchange_rate) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="form-group row">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="created_at">{{ __('Date') }}</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static f-14">
                                                    {{ dateFormat($exchange->created_at) }}</p>
                                            </div>
                                        </div>

                                        <div class="form-group row align-items-center">
                                            <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="status">{{ __('Change Status') }}</label>
                                            <div class="col-sm-6">
                                                <select class="form-control select2 w-50" name="status" id="status">
                                                    <option value="Success"
                                                        {{ isset($exchange->status) && $exchange->status == 'Success' ? 'selected' : '' }}>
                                                        {{ __('Success') }}</option>
                                                    <option value="Blocked"
                                                        {{ isset($exchange->status) && $exchange->status == 'Blocked' ? 'selected' : '' }}>
                                                        {{ __('Cancel') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 offset-md-3">
                                                <a id="cancel_anchor"
                                                    class="btn btn-theme-danger f-14 me-1"
                                                    href="{{ url(config('adminPrefix') . '/exchanges') }}">{{ __('Cancel') }}</a>
                                                <button type="submit" class="btn btn-theme f-14"
                                                    id="exchange_edit">
                                                    <i class="fa fa-spinner fa-spin d-none"></i>
                                                    <span id="exchange_edit_text">{{ __('Update') }}</span>
                                                </button>
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
                                    <div class="pt-4">
                                        @if ($exchange->amount)
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="amount">{{ __('Amount') }}</label>
                                                <input type="hidden" class="form-control f-14" name="amount"
                                                    value="{{ $exchange->amount }}" id="amount">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->currency)->symbol, formatNumber($exchange->amount)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if (isset($exchange->fee))
                                            <div class="form-group row total-deposit-feesTotal-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6 d-flex justify-content-end" for="fee">{{ __('Fees') }}
                                                    <span>
                                                        <small class="transactions-edit-fee">
                                                            @if (isset($transaction) && $transaction->transaction_type_id == Exchange_From)
                                                                ({{ formatNumber($transaction->percentage) }}% +
                                                                {{ formatNumber($transaction->charge_fixed) }})
                                                            @else
                                                                ({{ 0 }}%+{{ 0 }})
                                                            @endif
                                                        </small>
                                                    </span>
                                                </label>

                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->currency)->symbol, formatNumber($exchange->fee)) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                        <hr class="increase-hr-height">

                                        @php
                                            $total = $exchange->fee + $exchange->amount;
                                        @endphp

                                        @if (isset($total))
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="total">{{ __('Total') }}</label>
                                                <input type="hidden" class="form-control f-14" name="total"
                                                    value="{{ $total }}" id="total">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($exchange->currency)->symbol, formatNumber($total)) }}
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

        // disabling submit and cancel button after clicking it
        $(document).ready(function() {
            $('form').submit(function() {

                $("#exchange_edit").attr("disabled", true);
                $('#cancel_anchor').attr("disabled", "disabled");
                $(".fa-spin").removeClass("d-none");
                $("#exchange_edit_text").text('Updating...');

                // Click False
                $('#exchange_edit').click(false);
                $('#cancel_anchor').click(false);
            });
        });
    </script>
@endpush
