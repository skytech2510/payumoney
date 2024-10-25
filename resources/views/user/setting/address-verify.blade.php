@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 exchange pt-62 shadow" id="addressVerify">
    <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Settings') }}</p>
    <div class="row">
        <div class="col-12">
            <nav>
                <div class="nav-tab-parent d-flex justify-content-center mt-4">
                    <div class="d-flex p-2 border-1p rounded-pill gap-1 bg-white nav-tab-child">
                        <a href="{{ route('user.setting.identitiy_verify') }}" class="tablink-edit text-gray-100">{{ __('Identity Verification') }}</a>
                        <a href="{{ route('user.setting.address_verify') }}" class="tablink-edit text-gray-100 tabactive">{{ __('Address Verfication') }}</a>
                        @if ($two_step_verification != 'disabled')
                            <a href="{{ route('user.setting.twoFa') }}" class="tablink-edit text-gray-100">{{ __('TwoFa') }}</a>
                        @endif
                    </div>
                </div>
            </nav>
             @include('user.common.alert')
            <div class="mt-28 label-top">
                <form method="post" action="{{ route('user.setting.address_verify.update') }}" id="addressVerifyForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" value="{{ $user->id }}" name="user_id" id="user_id" />
                    <input type="hidden" value="{{ !empty($documentVerification->file_id) ? $documentVerification->file_id : '' }}" name="existingAddressFileID" id="existingAddressFileID" />

                    <div class="attach-file attach-print amount-label">
                        <label class="gilroy-medium text-B87 f-15 mb-2 mt-24 r-mt-amount r-mt-6" for="address_file">{{ __('Attach Address Proof') }}</label>
                        @if(!empty($documentVerification->status))
                            <span class="gilroy-medium {{ getColor(ucfirst($documentVerification->status)) }} f-15">{{ ' (' . $documentVerification->status . ')' }}</span>
                        @endif
                        <input type="file" class="form-control upload-filed" name="address_file" id="address_file" required data-value-missing="{{ __('This field is required.') }}">
                        @error('address_file')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    <p class="mb-0 f-11 gilroy-regular text-B87 mt-10">{{ __('Upload your documents (Max: :x mb)', ['x' => preference('file_size')]) }}</p>
                    
                    @if (!empty($documentVerification->file))
                    <div class="proof-btn-div d-flex justify-content-start mt-3">
                        <a href="{{ route('user.setting.kyc_proof.download', [$documentVerification->file->filename, 'address-proof']) }}" class='btn f-10 leading-12 proof-btn p-0 border-DF bg-FFF text-dark'><span>{{ $documentVerification->file->originalname }}</span>
                            {!! svgIcons('download_icon') !!}
                        </a>
                    </div>
                    @endif
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary px-4 py-2 mt-3" id="addressVerifySubmitBtn">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="addressVerifySubmitBtnText">{{ __('Verify Address') }}</span>
                        </button>
                    </div>
                </form>
                <!-- 2nd step end-->
            </div>
        </div>                        
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
<script>
    'use strict';
    var csrfToken = $('[name="_token"]').val();
    var submitButtonText = "{{ __('Submitting...') }}";
</script>
<script src="{{ asset('public/user/customs/js/settings.min.js') }}"></script>
@endpush