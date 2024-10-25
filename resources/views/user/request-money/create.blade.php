@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="requestMoneyCreate">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Request Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 1 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Create Request') }}</p>
    <div class="text-center">{!! svgIcons('stepper_create') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Enter your payer :x then add an amount with currency to request payment. You may add a note for reference.', [
        'x' => preference('processed_by') == 'email' 
            ? __('email address') 
            : (preference('processed_by') == 'phone' 
                ? __('phone number') 
                : __('email address or phone number'))
    ]) }}</p>
    
    <form method="post" action="{{ route('user.request_money.store') }}" id="requestMoneyCreateForm">
        @csrf
        <input type="hidden" name="processed_by" id="processed_by" value="{{ preference('processed_by') }}">

        <!-- Request Receiver -->
        <div class="mt-28 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Recipient') }}</label>
            <input type="text" class="form-control input-form-control apply-bg" name="email" id="requestCreatorEmail" 
            placeholder="{{ $placeHolder }}" onkeyup="this.value = this.value.replace(/\s/g, '')" value="{{ session('transInfo')['email'] ?? old('email') }}" required data-value-missing="{{ __('This field is required') }}">
            @error('email')
                <div class="custom-error">{{ $message }}</div>
            @enderror
            <span class="requestEmailOrPhoneError custom-error"></span>
        </div> 
        <p class="mb-0 text-gray-100 gilroy-regular f-12 mt-2"><em>{{ $helpText }}</em></p>

        <div class="row">
            <div class="col-md-6">
              <div class="param-ref mt-20">
                <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Currency') }}</label>
                <select class="select2" data-minimum-results-for-search="Infinity" name="currency_id" id="currency_id">
                    @foreach($currencyList as $currency)
                        <option data-type="{{ $currency['type'] }}" value="{{ $currency['id'] }}" 
                        @if (old('currency_id') && old('currency_id') == $currency['id'])
                            {{ 'selected="selected"' }}
                        @elseif (!empty(session('transInfo')) && session('transInfo')['currency_id'] == $currency['id'])
                            {{ 'selected="selected"' }}
                        @elseif (empty(old('currency_id')) && empty(session('transInfo')) && $defaultWallet->currency_id == $currency['id'])
                            {{ 'selected="selected"' }}
                        @endif
                        >{{ $currency['code'] }}</option>
                    @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
                <div class="label-top mt-20">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Amount') }}</label>
                    <input type="text" class="form-control input-form-control apply-bg l-s2" name="amount" id="amount" value="{{ session('transInfo')['amount'] ?? old('amount') }}" 
                    placeholder="{{ $amountPlaceHolder }}" 
                    onkeypress="return isNumberOrDecimalPointKey(this, event);" 
                    oninput="restrictNumberToPrefdecimalOnInput(this)" 
                    required data-value-missing="{{ __('This field is required') }}">
                    @error('amount')
                        <div class="custom-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Note -->
        <div class="label-top mt-20">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="floatingTextarea">{{ __('Note') }}</label>
            <textarea class="form-control l-s0 input-form-control h-100p" id="floatingTextarea note" placeholder="{{ __('Enter your note') }}" name="note" required data-value-missing="{{ __('This field is required') }}">{{ session('transInfo')['note'] ?? old('note') }}</textarea>
            @error('note')
                <div class="custom-error">{{ $message }}</div>
            @enderror
        </div> 
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="requestMoneyCreateSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <span class="px-1" id="requestMoneyCreateSubmitBtnText">{{ __('Proceed') }}</span>
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
<script>
    "use strict";
    var csrfToken = $('[name="_token"]').val();
    var checkEmailOrPhoneUrl  = "{{ route('user.request_money.check_email_or_phone') }}";
    var processedBy = "{{ preference('processed_by') }}";
    var emailValidText = "{{ __('Please enter valid email (ex: user@gmail.com)') }}";
    var phoneValidText = "{{ __('Please enter valid phone (ex: +12015550123)') }}";
    var emailOrPhoneValidText = "{{ __('Please enter valid email (ex: user@gmail.com) or phone (ex: +12015550123)') }}";
    let helpText = "{{ __('We will never share your :x with anyone else.') }}";
    let emailHelpText = helpText.replace(':x', 'email');
    let phoneHelpText = helpText.replace(':x', 'phone');
    let emailOrPHoneHelpText = helpText.replace(':x', 'email or phone');
    let placeHolder = "{{ __('Please enter valid :x') }}";
    let validEmailMessage = placeHolder.replace(':x', 'email (ex: user@gmail.com)'); 
    let validPhoneMessage = placeHolder.replace(':x', 'phone (ex: +12015550123)');
    let validEmailOrPhoneMessage = placeHolder.replace(':x', 'email (ex: user@gmail.com) or phone (ex: +12015550123)');
    let submitBtnText = '{{ __("Processing...") }}';

</script>
<script src="{{ asset('public/user/customs/js/request-money.min.js') }}"></script>

@endpush