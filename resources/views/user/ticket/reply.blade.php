@extends('user.layouts.app')

@section('content')
<div class="text-center disput-parent-content">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20">{{ __('TICKETS') }}</p>
    <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title">{{ __('Detailed information of ticket.') }}</p>
</div>
 @include('user.common.alert')
<div class="row mt-24" id="ticketReply">
    <div class="col-lg-4">
        <div class="sticky-mode">
            <div class="d-flex align-items-center back-direction">
                <a href="{{ route('user.tickets.index') }}" class="text-gray-100 f-16 leading-20 gilroy-medium d-inline-flex align-items-center position-relative back-btn">
                {!! svgIcons('left_angle') !!}
                <span class="ms-1 back-btn f-16 leading-20 gilroy-medium text-gray-100">{{ __('Back') }}</span>
                </a>
            </div>
            <div class="accordion" id="accordionExample">
                <div class="accordion-item mt-12 details-info-left-box details-info bg-white" >
                    <div class="d-flex justify-content-between" id="headingOne">
                        <p class="mb-0 f-20 gilroy-Semibold text-dark">{{ __('Detailed Information') }}</p>
                        <a href="javascript:void(0)" class="d-arrow-div rotate" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <img class="d-arrow cursor-pointer" src="{{ asset('public/user/templates/images/d-arrow.png') }}">
                        </a>
                    </div>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="dis-details-body">
                            <div class="borders-bottom mt-12"></div>
                            <div class="d-flex justify-content-between mt-24">
                                <div class="d-flex dispute-id">
                                <p class="mb-0 text-gray-100 f-13 gilroy-medium">{{ __('Ticket ID') }}:</p> 
                                <p class="text-primary f-13 gilroy-medium">&nbsp;{{ $ticket->code }}</p>
                                </div>
                                <div class="details-custom-badge d-flex justify-content-center align-items-center bg-{{ getBgColor($ticket->ticket_status?->name) }} mt-n3p">
                                <span class="text-white f-11 gilroy-medium">{{ $ticket->ticket_status?->name }}</span>
                                </div>
                            </div>
                            <div class="dis-title-box">
                                <p class="mb-0 text-gray-100 f-13 gilroy-medium">{{ __('Subject') }}</p>
                                <p class="mb-0 text-dark f-16 gilroy-medium mt-6">{{ $ticket->subject }}</p>
                            </div>
                            <div class="d-flex argument">
                                <div class="claimant w-100">
                                <p class="mb-0 f-13 gilroy-medium text-gray-100">{{ __('Priority') }}</p>
                                <p class="mb-0 f-15 gilroy-medium mt-8 text-dark">{{ $ticket->priority }}</p>
                                </div>
                            </div>
                            <div class="dis-time-div mt-24">
                                <p class="mb-0 f-13 gilroy-medium text-gray-100">{{ __('Time') }}</p>
                                <p class="mb-0 f-15 gilroy-medium mt-8 text-dark">{{ dateFormat($ticket->created_at) }}</p>
                            </div>
                            @if($ticket->ticket_status?->name != 'Closed')
                            <form action="{{ route('user.tickets.change_status') }}" method="post" id="ticketStatusChangeForm">
                                @csrf
                                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

                                <div class="d-flex st-height gap-10">
                                    <div class="param-ref money-ref r-select dispute-select">
                                        <label class="gilroy-medium text-B87 mb-2 f-15 mt-4 r-mt-0" for="status_id">{{ __('Change Status') }}</label>
                                        <select class="select2" data-minimum-results-for-search="Infinity" name="status_id" id="status_id">
                                            @foreach($ticketStatuses as $ticketStatus)
                                                <option value="{{ $ticketStatus->id }}" {{ $ticketStatus->id == $ticket->ticket_status_id ? 'selected' : '' }}>{{ $ticketStatus->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-end d-update">
                                        <button type="submit" class="cursor-pointer bg-primary text-white green-btn see-deatils-btn d-flex align-items-center justify-content-center mt-2p" id="ticketStatusChangeSubmitBtn">
                                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="ticketStatusChangeLoader">
                                                <span class="visually-hidden"></span>
                                            </div>
                                            <span id="ticketStatusChangeSubmitBtnText">{{ __('Update') }}</span>
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
    <div class="col-lg-8 dis-mb-top">
        <div class="details-info-left-box details-info bg-white mt-32">
            <div class="dis-details-title borders-bottom">
                <p class="mb-0 f-20 gilroy-Semibold text-dark">{{ __('Conversations') }}</p>
            </div>
            @if($ticket->ticket_status?->name != 'Closed')
                <form method="post" action="{{ route('user.tickets.reply.store') }}" id="ticketReplyForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    <input type="hidden" name="status_id" value="{{ $ticket->ticket_status_id }}">
                    <div class="label-top">
                        <label class="gilroy-medium text-gray-100 f-15 mt-4 r-mt-amount" for="floatingTextarea">{{ __('Write Message') }}</label>
                        <textarea class="form-control l-s0 f-14 input-form-control h-100p mt-6 f" name="message" id="message" required data-value-missing="{{ __('This field is required.') }}"></textarea>
                        @error('message')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-between flex-wrap mt-12">
                        <div class="attach-file attach-print label-top r-w-100">
                            <input class="form-control input-size upload-filed" type="file" name="file" id="file">
                            <span class="file-error" id="fileSpan"></span>
                            <p class="mb-0 f-11 gilroy-regular text-gray-100 mt-10"> {{ __('Upload your documents (Max: :x mb)', ['x' => preference('file_size')]) }} </p>
                        </div>
                        <button type="submit" class="r-ticket-btn green-btn ticket-btn-submit d-flex justify-content-center align-items-center bg-primary f-14 leading-17 gilroy-medium text-white" id="ticketReplySubmitBtn">
                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="ticketReplyLoader">
                                <span class="visually-hidden"></span>
                            </div>
                            <span id="ticketReplySubmitBtnText">{{ __('Reply') }}</span>
                        </button> 
                    </div>
                </form>
            @endif

            <div class="user-conv-box mt-24">
                <div class="d-flex gap-3">
                    <div class="dis-user-img">
                        <img src="{{ image($ticket->user?->picture, 'profile') }}" alt="{{ __('Profile') }}">
                    </div>
                    <div class="dis-user-conv w-100">
                        <div class="row position-r">
                            <div class="col-md-8">
                                <p class="mb-0 f-16 gilroy-medium text-dark mt-1p">{{ getColumnValue($ticket->user) }}</p>
                                <p class="mb-0 f-12 gilroy-medium text-gray mt-6">{{ dateFormat($ticket->created_at) }}</p>
                            </div>
                            <div class="col-md-4">
                                <div class="starter d-flex justify-content-center align-items-center">
                                <span class="f-11 leading-14 gilroy-Semibold text-primary">{{ __('starter') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="users-conversation mt-12 bg-white-50">
                            <p class="mb-0 f-14 leading-23 gilroy-medium text-gray-100"> {{ $ticket->message }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="ticket-replies"></div>
            
            <div class="text-center">
                <button class="btn btn-primary text-center" id="load-more" data-paginate="2">{{ __('Load more') }}</button>
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
    var csrfToken = $('[name="_token"]').val();
    var extensions = JSON.parse(@json(json_encode(getFileExtensions(1))));
    var extensionsValidation = extensions.join(', ');
    var errorMessage = "{{ __('Please select (:x) file.') }}";
    var extensionsValidationMessage = errorMessage.replace(':x', extensionsValidation);
    var ticketChangeReplyStatusUrl = "{{ route('user.tickets.change_status') }}";
    var submitButtonText = "{{ __('Submitting...') }}";
    var ticketReplyLoadUrl = '{{ route("user.tickets.reply", $ticket->id) }}';
    var loadingText = "{{ __('Loading...') }}"
    var loadMoreText = "{{ __('Load More') }}"
</script>
<script src="{{ asset('public/user/customs/js/ticket.min.js')}}" type="text/javascript"></script>
@endpush