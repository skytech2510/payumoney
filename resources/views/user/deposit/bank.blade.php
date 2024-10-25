@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="depositBank">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Deposit Money') }}</p>
    <p class="mb-0 text-center f-13 gilroy-medium text-gray mt-4 dark-A0">{{ __('Step: 2 of 3') }}</p>
    <p class="mb-0 text-center f-18 gilroy-medium text-dark dark-5B mt-2">{{ __('Bank Information') }}</p>
    <div class="text-center">{!! svgIcons('stepper_confirm') !!}</div>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray dark-p mt-20">{{ __('Fill in the details of your bank deposit with attached file. Take a look over
        the details before confirmation.') }}</p>
    <form action="{{ route('user.deposit.bank.store') }}" method="post" id="depositBankForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="method" id="method" value="{{ $transInfo['payment_method'] }}" >
        <input type="hidden" name="amount" id="amount" value="{{ $transInfo['totalAmount'] }}">

        <div class="mt-32 param-ref">
            <!-- Selected Bank Name -->
            <label class="gilroy-medium text-gray-100 mb-2">{{ __('Select Bank') }}</label>
            <select class="select2" data-minimum-results-for-search="Infinity" name="bank" id="bank">
                @foreach($banks as $bank)
                    <option value="{{ $bank['id'] }}" {{ isset($bank['is_default']) && $bank['is_default'] == 'Yes' ? "selected" : "" }}>{{ $bank['bank_name'] }}</option>
                @endforeach
            </select>

            <!-- Account Details -->
            <div class="mt-4">
                <div class="d-flex mt-3">

                    <!-- Account Name -->
                    @if ($bank['account_name'])
                    <div class="w-50">
                        <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 leading-14">{{ __('Account Name') }}</p>
                        <p class="mb-0 gilroy-medium text-dark mt-2 bank-info f-15" id="account_name">{{  $bank['account_name'] }}</p>
                    </div>
                    @endif

                    <!-- Account Number -->
                    @if ($bank['account_number'])
                    <div class="ms-4 ms-sm-5 w-50">
                        <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 leading-14">{{ __('Account Number') }}</p>
                        <p class="mb-0 gilroy-medium text-dark mt-2 bank-info f-15" id="account_number">{{  $bank['account_number'] }}</p>
                    </div>
                    @endif
                </div>

                <!-- Bank Name -->
                <div class="d-flex r-mt-16 mt-20">
                    @if ($bank['bank_name'])
                    <div class="w-50">
                        <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 leading-14">{{ __('Bank Name') }}</p>
                        <p class="mb-0 gilroy-medium text-dark mt-2 bank-info f-15" id="bank_name">{{  $bank['bank_name'] }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Attachment -->
            <div class="mb-3 mt-36 attach-file attach-print">
                <label for="formFileMultiple" class="form-label text-gray-100 gilroy-medium">{{ __('Attached File') }}</label>
                <input class="form-control upload-filed" type="file" id="formFileMultiple" name="attached_file" multiple required data-value-missing="{{ __('This field is required.') }}">
                <p class="mb-0 text-gray-100 dark-B87 gilroy-regular f-12 mt-10 leading-14">{{ __('Upload your documents (Max: :x MB)', ['x' => preference('file_size')]) }}</p>
            </div>

            <!-- Bank Logo -->
            <p>{{ __('Deposit Money via') }} <span id="bank_logo"></span></p>

            <!-- Confirm Details -->
            <div class="mt-36 res-mt-24 transaction-box">
                <p class="mb-0 gilroy-Semibold f-18 text-dark bn-details">{{ __('Details') }}</p>

                <!-- Amount -->
                <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                    <p class="mb-0 gilroy-regular text-gray-100">{{ __('Amount') }}</p>
                    <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['amount'], $transInfo['currency_id'])) }}</p>
                </div>

                <!-- Fee -->
                <div class="d-flex justify-content-between border-b-EF pb-13 mt-3">
                    <p class="mb-0 gilroy-regular text-gray-100">{{ __('Fee') }}</p>
                    <p class="mb-0 gilroy-regular text-gray-100">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['fee'], $transInfo['currency_id'])) }}</p>
                </div>

                <!-- Total -->
                <div class="d-flex justify-content-between mt-3 total">
                    <p class="mb-0 gilroy-medium text-dark">{{ __('Total') }}</p>
                    <p class="mb-0 gilroy-medium text-dark">{{ moneyFormat($transInfo['currencyCode'], formatNumber($transInfo['totalAmount'], $transInfo['currency_id'])) }}</p>
                </div>
            </div>
        </div>

        <!-- Confirm button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-lg btn-primary mt-4" id="depositBankSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                    <span class="visually-hidden"></span>
                </div>
                <span id="depositBankSubmitBtnText">{{ __('Confirm & Deposit') }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>

        <!-- Back button -->
        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.deposit.create') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn" id="depositConfirmBackBtn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
<script>
    'use strict';
    var token = $('[name="_token"]').val();
    var bankDetailsUrl = "{{ route('user.deposit.bank.details') }}";
    var confirmBtnText = "{{ __('Confirming...') }}";
    var bankLogoPath = "{{ asset('public/uploads/files/bank_logos') }}";
    var defaultBankLogoPath = "{{ defaultImage('bank') }}";
</script>
<script src="{{ asset('public/user/customs/js/deposit.min.js') }}"></script>
@endpush
