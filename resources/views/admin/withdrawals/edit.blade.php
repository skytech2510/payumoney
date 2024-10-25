@extends('admin.layouts.master')
@section('title', __('Edit Payout'))

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Withdrawal Details') }}</div>
                </div>

                <div>
                    <p class="text-left mb-0 f-18">{{ __('Status') }} : {!! getStatusText($withdrawal->status) !!}</p>
                </div>
            </div>
        </div>
    </div>
    <section class="min-vh-100">
        <div class="my-30">
            <div class="row">
                <form action="{{ url(config('adminPrefix') . '/withdrawals/update') }}" class="form-horizontal row"
                    id="withdrawal_form" method="POST">
                    {{ csrf_field() }}
                    <!-- Page title start -->
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-body">
                                <div class="panel">
                                    <div>
                                        <div class="p-4">
                                            <input type="hidden" value="{{ $withdrawal->id }}" name="id" id="id">
                                            <input type="hidden" value="{{ $withdrawal->user_id }}" name="user_id"
                                                id="user_id">
                                            <input type="hidden" value="{{ getColumnValue($withdrawal->currency, 'id', '') }}"
                                                name="currency_id" id="currency_id">
                                            <input type="hidden" value="{{ $withdrawal->uuid }}" name="uuid" id="uuid">

                                            <input type="hidden" value="{{ $transaction->transaction_type_id }}"
                                                name="transaction_type_id" id="transaction_type_id">
                                            <input type="hidden" value="{{ getColumnValue($transaction->transaction_type, 'name') }}"
                                                name="transaction_type" id="transaction_type">
                                            <input type="hidden" value="{{ $transaction->status }}"
                                                name="transaction_status" id="transaction_status">
                                            <input type="hidden" value="{{ $transaction->transaction_reference_id }}"
                                                name="transaction_reference_id" id="transaction_reference_id">

                                            <!-- User --> 
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="user">{{ __('User') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">
                                                        {{ getColumnValue($withdrawal->user) }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- TrxID --> 
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="withdrawal_uuid">{{ __('Transaction ID') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $withdrawal->uuid }}</p>
                                                </div>
                                            </div>

                                            <!-- Currency --> 
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="currency">{{ __('Currency') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $withdrawal->currency?->code }}</p>
                                                </div>
                                            </div>

                                            <!-- Payment Method --> 
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="payment_method">{{ __('Payment Method') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ $withdrawal->payment_method?->name == 'Mts' ? settings('name') : $withdrawal->payment_method?->name }}</p>
                                                </div>
                                            </div>


                                            @if (isset($withdrawal->withdrawal_detail))
                                                @if ($withdrawal->payment_method?->id == Bank)
                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="account_name">{{ __('Account Name') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">{{ $withdrawal->withdrawal_detail?->account_name }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="account_number">{{ __('Account Number') }}/{{ __('IBAN') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">{{ $withdrawal->withdrawal_detail?->account_number }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="swift_code">{{ __('SWIFT Code') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">{{ $withdrawal->withdrawal_detail?->swift_code }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="bank_name">{{ __('Bank Name') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">{{ $withdrawal->withdrawal_detail?->bank_name }}</p>
                                                        </div>
                                                    </div>
                                                @elseif ($withdrawal->payment_method?->id == Paypal)
                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="paypal_id">{{ __('Paypal ID') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">{{ $withdrawal->withdrawal_detail?->email }}</p>
                                                        </div>
                                                    </div>
                                                @elseif ($withdrawal->payment_method?->id == Crypto)
                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="crypto_address">{{ __('Withdrawal Address') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">{{ $withdrawal->withdrawal_detail?->crypto_address }}</p>
                                                        </div>
                                                    </div>
                                                @elseif (config('mobilemoney.is_active') && $withdrawal->payment_method?->id == (defined('MobileMoney') ? MobileMoney : ''))
                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="mobilemoney_id">{{ __('Network') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">
                                                                {{ $withdrawal->withdrawal_detail?->mobilemoney?->mobilemoney_name }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="mobile_number">{{ __('Mobile Number') }}</label>
                                                        <div class="col-sm-9">
                                                            <p class="form-control-static f-14">{{ $withdrawal->withdrawal_detail?->mobile_number }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            <!-- Created at -->
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="created_at">{{ __('Date') }}</label>
                                                <div class="col-sm-9">
                                                    <p class="form-control-static f-14">{{ dateFormat($withdrawal->created_at) }}</p>
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="form-group row align-items-center">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-3" for="status">{{ __('Change Status') }}</label>
                                                <div class="col-sm-6">
                                                    <select class="form-control select2 w-60" name="status" id="status">
                                                        <option value="Success" {{ $withdrawal->status == 'Success' ? 'selected' : '' }}>{{ __('Success') }}</option>
                                                        <option value="Pending" {{ $withdrawal->status == 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                                        <option value="Blocked" {{ $withdrawal->status == 'Blocked' ? 'selected' : '' }}>{{ __('Cancel') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 offset-md-3">
                                                    <a id="cancel_anchor" class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix') . '/withdrawals') }}">{{ __('Cancel') }}</a>
                                                    <button type="submit" class="btn btn-theme f-14" id="withdrawal_edit">
                                                        <i class="fa fa-spinner fa-spin d-none"></i>
                                                        <span id="withdrawal_edit_text">{{ __('Update') }}</span>
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

                                            <!-- Amount -->
                                            <div class="form-group row">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="amount">{{ __('Amount') }}</label>
                                                <input type="hidden" class="form-control" name="amount" id="amount" value="{{ $withdrawal->amount }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($withdrawal->currency)->symbol, formatNumber($withdrawal->amount, optional($withdrawal->currency)->id)) }}
                                                    </p>
                                                </div>
                                            </div>
                                            

                                            <div class="form-group row total-deposit-feesTotal-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6 d-flex justify-content-end" for="feesTotal">{{ __('Fees') }}
                                                    <span>
                                                        <small class="transactions-edit-fee">
                                                            @if (isset($transaction))
                                                                ({{ formatNumber($transaction->percentage, optional($withdrawal->currency)->id) }}%
                                                                +
                                                                {{ formatNumber($withdrawal->charge_fixed, optional($withdrawal->currency)->id) }})
                                                            @else
                                                                ({{ 0 }}%+{{ 0 }})
                                                            @endif
                                                        </small>
                                                    </span>
                                                </label>

                                                @php
                                                    $feesTotal = $withdrawal->charge_percentage + $withdrawal->charge_fixed;
                                                @endphp

                                                <input type="hidden" class="form-control" name="feesTotal" id="feesTotal" value="{{ $feesTotal }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($withdrawal->currency)->symbol, formatNumber($feesTotal, optional($withdrawal->currency)->id)) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <hr class="increase-hr-height">

                                            @php
                                                $total = $feesTotal + $withdrawal->amount;
                                            @endphp

                                            <!-- Total -->
                                            <div class="form-group row total-deposit-space">
                                                <label class="control-label f-14 fw-bold text-sm-end col-sm-6" for="total">{{ __('Total') }}</label>
                                                <input type="hidden" class="form-control" name="total" id="total" value="{{ $total }}">
                                                <div class="col-sm-6">
                                                    <p class="form-control-static f-14">
                                                        {{ moneyFormat(optional($withdrawal->currency)->symbol, formatNumber($total, optional($withdrawal->currency)->id)) }}
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
        </div>
    </section>
@endsection

@push('extra_body_scripts')
    <script type="text/javascript">
        $(".select2").select2({});

        // disabling submit and cancel button after clicking it
        $(document).ready(function() {
            $('form').submit(function() {
                $("#withdrawal_edit").attr("disabled", true);
                $('#cancel_anchor').attr("disabled", "disabled");
                $(".fa-spin").removeClass("d-none");
                $("#withdrawal_edit_text").text('Updating...');

                // Click False
                $('#withdrawal_edit').click(false);
                $('#cancel_anchor').click(false);
            });
        });
    </script>
@endpush
