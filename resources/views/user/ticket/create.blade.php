@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="ticketCreate">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center text-dark">{{ __('NEW TICKET') }}</p>
    <p class="mb-0 text-center f-14 gilroy-medium text-gray-100 dark-p mt-20"> {{ __('Write down to us the problem you are facing and set the level of priority. Our team will get back to you soon.') }} </p>

    @include('user.common.alert')

    <form method="post" action="{{ route('user.tickets.store') }}" id="ticketCreateForm" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="label-top new-ticket">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15 mt-4 r-mt-amount">{{ __('Subject') }}</label>
                    <input type="text" class="form-control input-form-control apply-bg" name="subject" id="subject" value="{{ old('subject') }}" placeholder="{{ __('Enter Subject') }}" required data-value-missing="{{ __('This field is required.') }}">
                    @error('subject')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="param-ref money-ref r-mt-11">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15 mt-4 r-mt-0">{{ __('Priority') }}</label>
                    <select class="select2" data-minimum-results-for-search="Infinity" name="priority" id="priority">
                        <option value="Low">{{ __('Low') }}</option>
                        <option value="Normal">{{ __('Normal') }}</option>
                        <option value="High">{{ __('High') }}</option>
                    </select>
                    @error('priority')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15 mt-4 r-mt-amount" for="floatingTextarea">{{ __('Your Message') }}</label>
            <textarea name="message" class="form-control l-s0 input-form-control h-100p" id="message" placeholder="{{ __('Enter your message') }}" required data-value-missing="{{ __('This field is required.') }}">{{ old('message') }}</textarea>
            @error('message')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button  type="submit" class="btn btn-lg btn-primary mt-4" id="ticketCreateSubmitBtn">
                <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                    <span class="visually-hidden"></span>
                </div>
                 <span id="ticketCreateSubmitBtnText">{{ __('Create Ticket') }}</span>
            </button>
        </div>

        <div class="d-flex justify-content-center align-items-center mt-4 back-direction">
            <a href="{{ route('user.tickets.index') }}" class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn">
                {!! svgIcons('left_angle') !!}
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
<script src="{{ asset('public/user/customs/js/ticket.min.js') }}" type="text/javascript"></script>
@endpush
