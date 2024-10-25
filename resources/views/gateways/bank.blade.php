@extends('gateways.layouts.master')

@section('content')

<form action="{{ route('gateway.confirm_payment') }}" method="POST" accept-charset="UTF-8" id="bank_deposit_form" enctype="multipart/form-data">

    <input value="{{csrf_token()}}" name="_token" id="token" type="hidden">
    <input value="{{ $payment_method }}" name="payment_method_id" id="payment_method_id" type="hidden">
    <input type="hidden" name="payment_type" id="payment_type" value="{{ $payment_type }}">
    <input type="hidden" name="transaction_type" id="transaction_type" value="{{ $transaction_type }}">
    <input type="hidden" name="gateway" id="transaction_type" value="bank">
    <input type="hidden" name="amount" id="amount" value="{{ $total }}">
    <input type="hidden" name="total_amount" id="total_amount" value="{{ $total }}">
    <input type="hidden" name="redirect_url" id="redirect_url" value="{{ $redirectUrl }}">
    <input type="hidden" name="uuid" id="uuid" value="{{ $uuid }}">
    <input type="hidden" name="params" value="{{ $params }}">

    <div class="param-ref">
        <!-- Selected Bank Name -->
        <div>
            <label class="form-label text-gray-100 gilroy-medium">{{ __('Select Bank') }}</label>
            <select class="form-control bank form-select" name="bank_id" id="bank">
                    @foreach($banks as $bank)
                        <option value="{{ $bank['id'] }}" {{ isset($bank['is_default']) && $bank['is_default'] == 'Yes' ? "selected" : "" }}>{{ $bank['bank_name'] }}</option>
                    @endforeach
            </select>
        </div>


        <!-- Account Details -->
        <div class="d-flex mt-3">
            <!-- Account Name -->
            @if ($bank['account_name'])
            <div class="w-50">
                <p class="form-label text-gray-100 gilroy-medium">{{ __('Account Name') }}</p>
                <p class="form-label text-gray-100 gilroy-medium" id="account_name">{{  $bank['account_name'] }}</p>
            </div>
            @endif

            <!-- Account Number -->
            @if ($bank['account_number'])
            <div class="ms-4 ms-sm-5 w-50">
                <p class="form-label text-gray-100 gilroy-medium">{{ __('Account Number') }}</p>
                <p class="form-label text-gray-100 gilroy-medium" id="account_number">{{  $bank['account_number'] }}</p>
            </div>
            @endif
        </div>

        <!-- Bank Name -->
        <div class="d-flex r-mt-16 mt-2">
            @if ($bank['bank_name'])
            <div class="w-50">
                <p class="form-label text-gray-100 gilroy-medium">{{ __('Bank Name') }}</p>
                <p class="form-label text-gray-100 gilroy-medium" id="bank_name">{{  $bank['bank_name'] }}</p>
            </div>
            @endif
        </div>

        <!-- Attachment -->
        <div class="mb-3attach-file attach-print">
            <label for="formFileMultiple" class="form-label text-gray-100 gilroy-medium">{{ __('Attached File') }}</label>
            <input class="form-control upload-filed" type="file" id="formFileMultiple" name="file" multiple required data-value-missing="{{ __('This field is required.') }}">
            <p class="form-label text-gray-100 gilroy-medium f-12">{{ __('Upload your documents (Max: :x MB)', ['x' => preference('file_size')]) }}</p>
        </div>

    </div>



    <div class="row">
        <div class="col-12">
            <div class="d-grid mt-2">
                <button type="submit" class="btn btn-lg btn-primary" type="submit" id="bank-button-submit">
                    <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none">
                        <span class="visually-hidden"></span>
                    </div>
                    <span id="bankSubmitBtnText" class="px-1">{{ __('Pay with :x', ['x' => ucfirst($gateway)]) }}</span>
                </button>
            </div>
        </div>
    </div>

</form>

@endsection

@section('js')

<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<script>
    "use strict";
    var bankDetailsUrl = "{{ url('deposit/bank-payment/get-bank-detail')}}";
    var confirming = "{{ __('Confirming...') }}"
    var token = "{{ csrf_token() }}";
    var extensionText =  "{{ __('Please select (png, jpg, jpeg, gif, bmp, pdf, docx, txt or rtf) file!') }}";
    var requiredText = "{{ __('This field is required.') }}";
</script>
<script src="{{ asset('public/frontend/customs/js/gateways/bank.min.js') }}"></script>


@endsection




