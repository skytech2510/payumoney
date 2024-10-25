@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="sendMoneyCreate">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Send Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Start Transfer') }}</p>
    <div class="text-center">
        {!! svgIcons('stepper_create') !!}
    </div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Enter your recipients :x & then add an amount with currency. You can also provide a note for reference.', [
        'x' => preference('processed_by') == 'email' 
            ? __('email address') 
            : (preference('processed_by') == 'phone' 
                ? __('phone number') 
                : __('email address or phone number'))
    ]) }}</p>
    
    <form method="post" action="{{ route('user.send_money.store') }}" id="sendMoneyCreateForm">
        @csrf
        <input type="hidden" name="percentage_fee" id="feesPercentage" value="">
        <input type="hidden" name="fixed_fee" id="feesFixed" value="">
        <input type="hidden" name="total_fee" id="totalFees" value="0.00">
        <input type="hidden" name="sendMoneyProcessedBy" id="sendMoneyProcessedBy">

        <!-- Recipient -->
        <div class="mt-28 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Recipient') }}</label>
            <input type="text" class="form-control input-form-control apply-bg" name="receiver" id="receiver" value="{{ session('transInfo')['receiver'] ?? old('receiver') }}" 
            placeholder="{{ $placeHolder }}"
            onkeyup="this.value = this.value.replace(/\s/g, '')" required data-value-missing="{{ __('This field is required.') }}">
            @error('receiver')
                <div class="error">{{ $message }}</div>
            @enderror
            <span class="receiverError custom-error"></span>
        </div> 
        <p class="mb-0 text-gray-100 gilroy-regular f-12 mt-2"><em>{{ $helpText }}</em></p>

        <!-- Currency -->
        <div class="row">
            <div class="col-md-6">
                <div class="param-ref mt-20">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Currency') }}</label>
                    <select class="select2" data-minimum-results-for-search="Infinity" name="wallet" id="wallet">
                    @foreach($wallets as $wallet)
                        <option data-type="{{ $wallet->active_currency?->type }}" value="{{ $wallet->id }}"
                        @if (old('wallet') && old('wallet') == $wallet->id)
                            {{ 'selected="selected"' }}
                        @elseif (!empty(session('transInfo')) && session('transInfo')['wallet_id'] == $wallet->id)
                            {{ 'selected="selected"' }}
                        @elseif (empty(old('wallet')) && empty(session('transInfo')) && $wallet->is_default == 'Yes')
                            {{ 'selected="selected"' }}
                        @endif
                        >{{ $wallet->active_currency?->code }}</option>
                    @endforeach
                    </select>
                    <span id="walletlHelp" class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-2">
                        {{ __('Fee') }} (<span id="formattedFeesPercentage">{{ $amountPlaceHolder }}</span>%+<span id="formattedFeesFixed">{{ $amountPlaceHolder }}</span>)&nbsp;{{ __('Total Fee') }}:&nbsp;<span id="formattedTotalFees">{{ $amountPlaceHolder }}</span>
                    </span>
                </div> 
            </div>

            <!-- Amount -->
            <div class="col-md-6">
                <div class="label-top mt-20">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Amount') }}</label>
                    <input type="text" class="form-control input-form-control apply-bg l-s2" name="amount" id="amount" 
                    placeholder="{{ $amountPlaceHolder }}"
                    onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="{{ __('This field is required.') }}" value="{{ session('transInfo')['amount'] ?? old('amount') }}">
                    
                    @error('amount')
                        <div class="custom-error">{{ $message }}</div>
                    @enderror
                    <label class="custom-error amount-limit-error"></span>
                </div>
            </div>
        </div>

        <!-- Note -->
        <div class="label-top mt-20">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="floatingTextarea">{{ __('Note') }}</label>
            <textarea class="form-control l-s0 input-form-control h-100p" id="floatingTextarea note" name="note" required data-value-missing="{{ __('This field is required.') }}">{{ session('transInfo')['note'] ?? old('note') }}</textarea>
            @error('note')
                <div class="custom-error">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="sendMoneyCreateSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="sendMoneyCreateSubmitBtnText">{{ __('Proceed') }}</span>
                <span id="sendMoneySvgIcon">{!! svgIcons('right_angle') !!}</span>
            </a>
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
        let processedBy = "{{ preference('processed_by') }}";
        let transactionTypeId = "{{ Transferred }}";
        let placeHolder = "{{ __('Please enter valid :x') }}";
        let validEmailMessage = placeHolder.replace(':x', 'email (ex: user@gmail.com)'); 
        let validPhoneMessage = placeHolder.replace(':x', 'phone (ex: +12015550123)');
        let validEmailOrPhoneMessage = placeHolder.replace(':x', 'email (ex: user@gmail.com) or phone (ex: +12015550123)');
        let lowBalanceText = "{{ __('Not have enough balance !') }}";
        let receiverStatusUrl = "{{ route('user.send_money.receiver_status_check') }}";
        let checkAmountLimitUrl = "{{ route('user.send_money.check_amount_limit') }}";
        let submitBtnText = '{{ __("Processing...") }}';
    </script>
    <script src="{{ asset('public/user/customs/js/send-money.min.js') }}"></script>
@endpush    