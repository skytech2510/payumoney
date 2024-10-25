@extends('admin.layouts.master')
@section('title', __('Edit Deposit'))

@section('page_content')
<div class="box box-default">
	<div class="box-body">
		<div class="d-flex justify-content-between">
			<div>
				<div class="top-bar-title padding-bottom pull-left">{{ __('Deposit Details') }}</div>
			</div>
			<div>
				<p class="text-left mb-0 f-18">{{ __('Status') }} : {!! getStatusText($deposit->status) !!}</p>
			</div>
		</div>
	</div>
</div>

<div class="my-30">
    <form action="{{ url(config('adminPrefix').'/deposits/update') }}" class="form-horizontal row" id="deposit_form" method="POST">
        {{ csrf_field() }}
        <!-- Page title start -->
        <div class="col-md-8">
            <div class="box">
                <div class="box-body">
                    <div class="panel">
                        <div>
                            <div class="p-4">
                                <input type="hidden" value="{{ $deposit->id }}" name="id" id="id">
                                <input type="hidden" value="{{ $deposit->user_id }}" name="user_id" id="user_id">
                                <input type="hidden" value="{{ $deposit->currency?->id }}" name="currency_id" id="currency_id">
                                <input type="hidden" value="{{ $deposit->uuid }}" name="uuid" id="uuid">
                                <input type="hidden" value="{{ ($deposit->charge_percentage)  }}" name="charge_percentage" id="charge_percentage">
                                <input type="hidden" value="{{ ($deposit->charge_fixed)  }}" name="charge_fixed" id="charge_fixed">

                                <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
                                <input type="hidden" value="{{ $transaction->transaction_type?->name }}" name="transaction_type" id="transaction_type">
                                <input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
                                <input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">

                                <!-- User -->
                                <div class="form-group row">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="user">{{ __('User') }}</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static f-14">{{ getColumnValue($deposit->user) }}</p>
                                    </div>
                                </div>

                                <!-- TrxID -->
                                <div class="form-group row">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="deposit_uuid">{{ __('Transaction ID') }}</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static f-14">{{ $deposit->uuid }}</p>
                                    </div>
                                </div>

                                <!-- Currency -->
                                <div class="form-group row">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="currency">{{ __('Currency') }}</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static f-14">{{ $deposit->currency?->code }}</p>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="form-group row">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="payment_method">{{ __('Payment Method') }}</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static f-14">{{ ($deposit->payment_method?->name == "Mts") ? settings('name') : $deposit->payment_method?->name }}</p>
                                    </div>
                                </div>

                                <!-- Info for Bank -->
                                @if ($deposit->bank)
                                    <div class="form-group row">
                                        <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="bank_name">{{ __('Bank Name') }}</label>
                                        <div class="col-sm-6">
                                            <p class="form-control-static f-14">{{ $deposit->bank?->bank_name }}</p>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="bank_branch_name">{{ __('Branch Name') }}</label>
                                        <div class="col-sm-6">
                                            <p class="form-control-static f-14">{{ $deposit->bank?->bank_branch_name }}</p>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="account_name">{{ __('Account Name') }}</label>
                                        <div class="col-sm-6">
                                            <p class="form-control-static f-14">{{ $deposit->bank?->account_name }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if (config('mobilemoney.is_active') && $deposit->mobilemoney)
                                    <div class="form-group row">
                                        <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="mobilemoney_id">{{ __('Network') }}</label>
                                        <div class="col-sm-6">
                                            <p class="form-control-static f-14">{{ $deposit->mobilemoney?->mobilemoney_name ?? '' }}</p>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="mobilemoney_name">{{ __('Mobile Number') }}</label>
                                        <div class="col-sm-6">
                                            <p class="form-control-static f-14">{{ $deposit->mobilemoney?->mobilemoney_number ?? '' }}</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- File -->
                                @if ($deposit->file)
                                    <div class="form-group row">
                                        <label class="control-label fw-bold f-14 text-sm-end col-sm-3">{{ __('Attached File') }}</label>
                                        <div class="col-sm-6">
                                            <p class="form-control-static f-14">
                                                @if ($deposit->bank)
                                                    <a href="{{ url('public/uploads/files/bank_attached_files').'/'.$deposit->file?->filename }}" download={{ $deposit->file?->filename }}><i class="fa fa-fw fa-download"></i>
                                                        {{ $deposit->file?->originalname }}
                                                    </a>
                                                @elseif (config('mobilemoney.is_active') && $deposit->mobilemoney)
                                                    <a href="{{ url('public/uploads/files/mobilemoney_attached_files').'/'.$deposit->file?->filename }}" download={{ $deposit->file?->filename }}><i class="fa fa-fw fa-download"></i>
                                                        {{ $deposit->file?->originalname }}
                                                    </a>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Created At -->
                                <div class="form-group row">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="created_at">{{ __('Date') }}</label>
                                    <div class="col-sm-6">
                                        <p class="form-control-static f-14">{{ dateFormat($deposit->created_at) }}</p>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="form-group row align-items-center">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-3" for="status">{{ __('Change Status') }}</label>
                                    <div class="col-sm-6">
                                        <select class="form-control select2 w-60" name="status" id="status">
                                            <option value="Success" {{ $deposit->status ==  'Success'? 'selected':"" }}>{{ __('Success') }}</option>
                                            <option value="Pending"  {{ $deposit->status == 'Pending' ? 'selected':"" }}>{{ __('Pending') }}</option>
                                            <option value="Blocked"  {{ $deposit->status == 'Blocked' ? 'selected':"" }}>{{ __('Cancel') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 offset-md-3">
                                        <a id="cancel_anchor" class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/deposits') }}">{{ __('Cancel') }}</a>
                                        <button type="submit" class="btn btn-theme f-14" id="deposits_edit">
                                            <i class="fa fa-spinner fa-spin d-none"></i> <span id="deposits_edit_text">{{ __('Update') }}</span>
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
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-6" for="amount">{{ __('Amount') }}</label>
                                    <input type="hidden" class="form-control" name="amount" value="{{ ($deposit->amount) }}" id="amount">
                                    <div class="col-sm-6">
                                    <p class="form-control-static f-14">{{ moneyFormat(optional($deposit->currency)->symbol, formatNumber($deposit->amount, $deposit->currency?->id)) }}</p>
                                    </div>
                                </div>
                               

                                <div class="form-group row total-deposit-feesTotal-space">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-6 d-flex justify-content-end" for="feesTotal">{{ __('Fees') }}
                                        <span>
                                            <small class="transactions-edit-fee">
                                                @if (isset($transaction))
                                                ({{(formatNumber($transaction->percentage, $deposit->currency?->id))}}% + {{ formatNumber($deposit->charge_fixed, $deposit->currency?->id) }})
                                                @else
                                                    ({{0}}%+{{0}})
                                                @endif
                                            </small>
                                        </span>
                                    </label>

                                    @php
                                        $feesTotal = $deposit->charge_percentage + $deposit->charge_fixed;
                                    @endphp

                                    <input type="hidden" class="form-control" name="feesTotal" value="{{ ($feesTotal) }}" id="feesTotal">

                                    <div class="col-sm-6">
                                    <p class="form-control-static f-14">{{ moneyFormat(optional($deposit->currency)->symbol, formatNumber($feesTotal, $deposit->currency->id)) }}</p>
                                    </div>
                                </div>

                                <hr class="increase-hr-height">

                                @php
                                    $total = $feesTotal + $deposit->amount;
                                @endphp

                                <!-- Total -->
                                <div class="form-group row total-deposit-space">
                                    <label class="control-label fw-bold f-14 text-sm-end col-sm-6" for="total">{{ __('Total') }}</label>
                                    <input type="hidden" class="form-control" name="total" value="{{ ($total) }}" id="total">
                                    <div class="col-sm-6">
                                    <p class="form-control-static f-14">{{ moneyFormat(optional($deposit->currency)->symbol, formatNumber($total, $deposit->currency?->id)) }}</p>
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
@endsection

@push('extra_body_scripts')
<script type="text/javascript">

	$(".select2").select2({});

	// disabling submit and cancel button after form submit
	$(document).ready(function()
	{
        $('form').submit(function()
        {
            $("#deposits_edit").attr("disabled", true);
            $('#cancel_anchor').attr("disabled","disabled");
            $(".fa-spin").removeClass("d-none");
            $("#deposits_edit_text").text('Updating...');

            // Click False
            $('#deposits_edit').click(false);
            $('#cancel_anchor').click(false);
        });
	});
</script>
@endpush
