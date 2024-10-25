@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="requestMoneyAccept">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Accept Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Accept Request Money') }}</p>
    <div class="text-center">{!! svgIcons('stepper_create') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Enter your payer email address or phone number then add an amount with currency to request payment. You may add a note for reference.') }}</p>

    <form method="post" action="{{ url('request_payment/accepted') }}" id="requestMoneyAcceptForm">
        @csrf
        <input type="hidden" value="{{ $requestPayment->id }}" name="id" id="id">
        <input type="hidden" value="{{ $requestPayment->currency_id }}" name="currency_id" id="currency_id" >
        <input type="hidden" value="{{ $requestPayment->currency->symbol }}" name="currencySymbol" id="currencySymbol" >
        <input type="hidden" value="{{ $requestPayment->amount * ($transfer_fee->charge_percentage/100) }}" name="percentage_fee" id="percentage_fee" >
        <input type="hidden" value="{{ $transfer_fee->charge_fixed }}" name="fixed_fee" id="fixed_fee">
        <input type="hidden" name="fee" class="total_fees" id="total_fees" value="0.00">

        <!-- Email or Phone -->
        <div class="mt-28 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="email">{{ __('Recipent') }}</label>
            <input type="text" name="emailOrPhone" id="emailOrPhone" class="form-control input-form-control apply-bg" value="{{ ($requestPayment->phone) ? $$requestPayment->user?->email : $requestPayment->user?->email }}" readonly required data-value-missing="{{ __('This field is required.') }}">
            <span class="custom-error requestEmailOrPhoneError"></span>
            <small id="emailHelp" class="form-text text-danger"></small>
            @error('emailOrPhone')
                <div class="custom-error">{{ $message }}</div>
            @enderror
        </div>
        <p class="mb-0 text-warning gilroy-regular f-12 mt-2"><em>* {{ __('Your email address will remain confidential') }}</em></p>

        <!-- Currency & Amount -->
        <div class="row">
            <!-- Currency -->
            <div class="col-md-6">
                <div class="param-ref mt-20">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15" id="currency_id">{{ __('Currency') }}</label>
                    <input class="form-control input-form-control apply-bg" name="currency" data-type="{{ $requestPayment->currency->type }}" data-rel="{{$requestPayment->currency->id}}" id="currency" type="text" value="{{$requestPayment->currency->code}}" readonly>
                    @error('currency')
                        <span class="custom-error">{{ $message }}</span>
                    @enderror
                    <span class="custom-error currency-error"></span>
                    
                    <span id="walletlHelp" class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">
                        {{ __('Fee') }} (<span id="formattedFeesPercentage">{{ formatNumber($transfer_fee->charge_percentage,  $transfer_fee->currency_id) }}</span>%+<span id="formattedFeesFixed">{{ formatNumber($transfer_fee->charge_fixed,  $transfer_fee->currency_id) }}</span>)&nbsp;
                        {{ __('Total Fee') }}:&nbsp;<span id="formattedFeesTotal">{{ formatNumber(($requestPayment->amount * ($transfer_fee->charge_percentage/100))+($transfer_fee->charge_fixed), $transfer_fee->currency_id) }}</span>
                    </span>
                </div>
            </div>

            <!-- Amount -->
            <div class="col-md-6">
                <div class="label-top mt-20">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15" for="amount">{{ __('Amount') }}</label>
                    <input type="text" class="form-control input-form-control apply-bg l-s2 amount" name="amount" id="amount" onkeypress="return isNumberOrDecimalPointKey(this, event);" value="{{ number_format($requestPayment->amount, 2, '.', '') }}" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="{{ __('This field is required.') }}">
                    @error('amount')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    <span class="custom-error amount-error"></span>
                </div>
            </div>
        </div>

        <!-- Note -->
        <div class="label-top mt-20">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="note">{{ __('Note') }}</label>
            <textarea readonly class="form-control l-s0 input-form-control h-100p focus-bgcolor" rows="3" name="note" id="note" required data-value-missing="{{ __('This field is required.') }}">{{ $requestPayment->note }}</textarea>
            @error('note')
                <div class="custom-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="requestMoneyAcceptSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span id="requestMoneyAcceptSubmitBtnText">{{ __('Proceed') }}</span>
                <span id="requestMoneySvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('js')
@include('common.restrict_number_to_pref_decimal')
@include('common.restrict_character_decimal_point')

<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
<script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js') }}"></script>
<script type="text/javascript">
    'use strict';
    let token = $('[name="_token"]').val();
    let transactionTypeId = '{{ Request_Received }}';
    let submitBtnText = '{{ __("Processing...") }}';
    var requestMoneyAcceptLimitUrl  = "{{ route('user.request_money.accept.limit') }}";
</script>

<script src="{{ asset('public/user/customs/js/accept-money.min.js') }}"></script>
@endpush
