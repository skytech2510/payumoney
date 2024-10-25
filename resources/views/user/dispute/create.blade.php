@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="disputeCreate">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center text-dark">{{ __('New Dispute') }}</p>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray-100 dark-p mt-20"> {{ __('Write down to us the problem you are facing and select your reason. Our team will get back to you soon.') }} </p>
    @include('user.common.alert')
    <form method="post" action="{{ route('user.disputes.store') }}" id="disputeCreateForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
        <input type="hidden" name="claimant_id" value="{{ $transaction->user_id }}">
        <input type="hidden" name="defendant_id" value="{{ $transaction->end_user_id }}">
        <div class="row">
            <div class="col-12">
                <div class="label-top new-ticket">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15 mt-4 r-mt-amount">{{ __('Title') }}</label>
                    <input type="text" class="form-control input-form-control apply-bg" name="title" id="title" value="{{ old('title') }}" placeholder="{{ __('Enter Title') }}" required data-value-missing="{{ __('This field is required') }}">
                    @error('title')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="param-ref money-ref r-mt-11">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15 mt-4 r-mt-0">{{ __('Reason') }}</label>
                    <select class="select2" data-minimum-results-for-search="Infinity" name="reason_id" id="reason_id">
                        @foreach ($reasons as $reason)
                            <option value="{{ $reason->id }}">{{ $reason->title }}</option>
                        @endforeach
                    </select>
                    @error('reason_id')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15 mt-4 r-mt-amount" for="floatingTextarea">{{ __('Your Message') }}</label>
            <textarea name="description" class="form-control l-s0 input-form-control h-100p" id="description" placeholder="{{ __('Enter your message') }}" required data-value-missing="{{ __('This field is required.') }}">{{ old('description') }}</textarea>
            @error('description')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button  type="submit" class="btn btn-lg btn-primary mt-4" id="disputeCreateSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                 <span id="disputeCreateSubmitBtnText">{{ __('Create Dispute') }}</span>
            </button>
        </div>

        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.disputes.index') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn">
                <span id="disputeSvgIcon">{!! svgIcons('left_angle') !!}</span>
                <span class="ms-1 back-btn">{{ __('Back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
<script>
	'use strict';
    var csrfToken = $('[name="_token"]').val();
    var submitButtonText = "{{ __('Submitting...') }}";
</script>
<script src="{{ asset('public/user/customs/js/dispute.min.js')}}" type="text/javascript"></script>
@endpush