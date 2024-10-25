@extends('user.layouts.app')

@section('content')
<div class="text-center disput-parent-content">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20">{{ __('Dispute details') }}</p>
    <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title">{{ __('Everything you need to know about the dispute') }}</p>
</div>
<div class="row mt-24" id="disputeDiscussion">
    @include('user.common.alert')
    <div class="col-lg-4">
        <div class="sticky-mode">
            <div class="d-flex align-items-center back-direction">
                <a href="{{ route('user.disputes.index') }}" class="text-gray-100 f-16 leading-20 gilroy-medium d-inline-flex align-items-center position-relative back-btn">
                    {!! svgIcons('left_angle') !!}
                    <span class="ms-1 back-btn">{{ __('Back to list') }}</span>
                </a>
            </div>
            <div class="accordion" id="accordionExample">
                <div class="accordion-item mt-12 details-info-left-box details-info bg-white" >
                    <div class="dis-details-title d-flex justify-content-between" id="headingOne">
                        <p class="mb-0 f-20 gilroy-Semibold text-dark">{{ __('Detailed Information') }}</p>
                        <div class="d-arrow-div rotate">
                            <img class="d-arrow cursor-pointer" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" src="{{ asset('public/user/templates/images/d-arrow.png') }}">
                        </div>
                    </div>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="dis-details-body">
                            <div class="borders-bottom mt-8"></div>
                            <div class="dis-details-body mt-24">
                                <div class="d-flex dispute-id justify-content-between">
                                    <div class="d-flex">
                                    <p class="mb-0 text-gray-100 f-13 gilroy-medium">{{ __('Dispute ID') }} :&nbsp;  </p>
                                    <p class="text-primary f-13 gilroy-medium"> {{ $dispute->code }}</p>
                                    </div>
                                    <p class="mb-0 {{ getColor($dispute->status) }} gilroy-medium f-13 leading-16 text-end">{{ $dispute->status }}</p>
                                </div>
                                <div class="dis-title-box">
                                    <p class="mb-0 text-gray-100 f-13 gilroy-medium">{{ __('Title') }}</p>
                                    <p class="mb-0 text-dark f-16 gilroy-medium mt-6">{{ $dispute->title }}</p>
                                </div>
                                <div class="d-flex argument">
                                    <div class="claimant w-50">
                                    <p class="mb-0 f-13 gilroy-medium text-gray-100">{{ __('Claimant') }}</p>
                                    <p class="mb-0 f-15 gilroy-medium mt-8 text-dark">{{ getColumnValue($dispute->claimant) }}</p>
                                    </div>
                                    <div class="defendant w-50">
                                    <p class="mb-0 f-13 gilroy-medium text-gray-100">{{ __('Defendant') }}</p>
                                    <p class="mb-0 f-15 gilroy-medium mt-8 text-dark">{{ getColumnValue($dispute->defendant) }}</p>
                                    </div>
                                </div>
                                <div class="dis-transection-div mt-24">
                                    <p class="mb-0 f-13 gilroy-medium text-gray-100">{{ __('Transaction ID') }}</p>
                                    <p class="mb-0 f-15 gilroy-medium mt-8 text-dark">{{ $dispute->transaction->uuid }}</p>
                                </div>
                                <div class="dis-time-div mt-24">
                                    <p class="mb-0 f-13 gilroy-medium text-gray-100">{{ __('Time') }}</p>
                                    <p class="mb-0 f-15 gilroy-medium mt-8 text-dark">{{ dateFormat($dispute->created_at) }}</p>
                                </div>
                                <div class="dis-time-div reason mt-24">
                                    <p class="mb-0 f-13 gilroy-medium text-gray-100">{{ __('Reason Details') }}</p>
                                    <p class="mb-0 f-14 leading-22 gilroy-medium mt-8 text-dark">{{ $dispute->reason?->title }}</p>
                                </div>
                                @if($dispute->status == 'Open' && $dispute->claimant_id == auth()->id())
                                    <form action="{{ route('user.disputes.change_status') }}" method="post" id="disputeStatusChangeForm">
                                        @csrf
                                        <input type="hidden" name="dispute_id" value="{{ $dispute->id }}">
                                        <div class="d-flex gap-10">
                                            <div class="param-ref money-ref r-select dispute-select">
                                                <label class="gilroy-medium text-gray-100 mb-2 f-13 leading-16 mt-4 r-mt-0">{{ __('Change Status') }}</label>
                                                <select class="select2" data-minimum-results-for-search="Infinity" name="status" id="status">
                                                    <option value="Open" {{ $dispute->status == 'Open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                                                        <option value="Closed" {{ $dispute->status == 'Closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                                </select>
                                            </div>
                                            <div class="d-flex align-items-end d-update">
                                                <button type="submit" class="cursor-pointer bg-primary text-white green-btn see-deatils-btn d-flex align-items-center justify-content-center mt-2p" id="disputeStatusChangeSubmitBtn">
                                                    <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="disputeStatusChangeLoader">
                                                        <span class="visually-hidden"></span>
                                                    </div>
                                                    <span id="disputeStatusChangeSubmitBtnText">{{ __('Update') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 dis-mb-top">
        <div class="details-info-left-box details-info bg-white mt-32">
            <div class="dis-details-title borders-bottom">
                <p class="mb-0 f-20 gilroy-Semibold text-dark">{{ __('Conversations') }}</p>
            </div>
            @if($dispute->status == 'Open')
                <form action="{{ route('user.disputes.reply.store') }}" id="disputeReplyForm" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="dispute_id" value="{{ $dispute->id }}">
                    <div class="label-top">
                        <label class="gilroy-medium text-gray-100 f-15 mt-4 r-mt-amount" for="floatingTextarea">{{ __('Write Message') }}</label>
                        <textarea class="form-control l-s0 f-14 input-form-control h-100p mt-6 f" name="description" id="description" required data-value-missing="{{ __('This field is required') }}"></textarea>
                        @error('description')
                            <span class="error">{{ $message }}</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between flex-wrap mt-12">
                        <div class="attach-file attach-print label-top r-w-100">
                            <input class="form-control input-size upload-filed" type="file" name="file" id="file">
                            <p class="mb-0 f-11 gilroy-regular text-gray-100 mt-10">{{ __('Upload your documents (Max: :x mb)', ['x' => preference('file_size')]) }}</p>
                            <span class="file-error" id="fileSpan"></span>
                        </div>
                        <button type="submit" class="r-ticket-btn green-btn ticket-btn-submit d-flex justify-content-center align-items-center bg-primary f-14 leading-17 gilroy-medium text-white" id="disputeReplySubmitBtn">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="disputeReply">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="disputeReplySubmitBtnText">{{ __('Reply') }}</span>
                        </button> 
                    </div>
                </form>
            @endif
            <div class="user-conv-box mt-24 mb-2">
                <div class="d-flex gap-3">
                    <div class="dis-user-img">
                        <img src="{{ image($dispute?->claimant?->picture, 'profile') }}" alt="{{ __('Profile') }}" class="img-fluid">
                    </div>
                <div class="dis-user-conv w-100">
                    <div class="row position-r">
                    <div class="col-md-8">
                        <p class="mb-0 f-16 gilroy-medium text-dark mt-1p">{{ getColumnValue($dispute->claimant) }}</p>
                        <p class="mb-0 f-12 gilroy-medium text-gray mt-6">{{ dateFormat($dispute->created_at) }}</p>
                    </div>
                    <div class="col-md-4">
                        <div class="starter d-flex justify-content-center align-items-center">
                        <span class="f-11 leading-14 gilroy-Semibold text-primary">{{ __('starter') }}</span>
                        </div>
                    </div>
                    </div>
                    <div class="users-conversation mt-12 bg-white-50">
                    <p class="mb-0 f-14 leading-23 gilroy-medium text-gray-100"> {!! $dispute->description !!}</p>
                    </div>
                </div>
                </div>
            </div>

            <div id="dispute-replies"></div>
            
            <div class="text-center">
                <button class="btn btn-primary text-center mt-3" id="load-more" data-paginate="2">{{ __('Load more') }}</button>
                <p class="invisible text-center">{{ __('No more reply') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}" type="text/javascript"></script>
<script>
	'use strict';
    var extensions = JSON.parse(@json(json_encode(getFileExtensions(1))));
    var extensionsValidation = extensions.join(', ');
    var errorMessage = "{{ __('Please select (:x) file.') }}";
    var extensionsValidationMessage = errorMessage.replace(':x', extensionsValidation);
    var disputeReplyLoadUrl = '{{ route("user.disputes.discussion", $dispute->id) }}';
    var loadingText = "{{ __('Loading...') }}";
    var loadMoreText = "{{ __('Load more') }}";
</script>
<script src="{{ asset('public/user/customs/js/dispute.min.js')}}" type="text/javascript"></script>
@endpush