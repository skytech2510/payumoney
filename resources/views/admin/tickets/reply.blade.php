@php
    $extensions = json_encode(getFileExtensions(1));
@endphp

@extends('admin.layouts.master')
@section('title', __('Ticket Reply'))
@section('head_style')
  <link rel="stylesheet" type="text/css" href="{{  asset('public/dist/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
@endsection

@section('page_content')
<div id="ticket-reply">
<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <div class="col-md-9">
                <div class="top-bar-title padding-bottom">{{ __('Ticket Reply') }}</div>
            </div>
            <div class="col-md-3">
             <h4 class="pull-right f-18">{{ __('Ticket Status') }}: {!! getStatusLabel($ticket->ticket_status?->name) !!}</h4>

            </div>
        </div>
    </div>
</div>

<!-- Reply Form -->
<div class="box">
    <div class="box-header with-border"><h4 class="f-18"> <strong>{{ __('Subject') }}  : </strong> {{ $ticket->subject }}</h4></div>

    <div class="row px-4 py-3 align-items-center">
        <div class="col-md-10">
            <span class="label label-default px-2 py-1 f-14 rounded fw-bold">{{ __('Priority') }} : {{ $ticket->priority }}</span>
            @if(isset($ticket->admin_id))
                <span class="label label-warning px-2 py-1 f-14 rounded fw-bold">{{ __('Assignee') }} : {{ getColumnValue($ticket->admin) }}</span>
            @endif
        </div>

        <div class="col-md-2">
            <span>
                <select id="status_ticket" class="form-control f-14 select2">
                    @foreach($ticket_status as $status)
                        <option {{ $status->id == $ticket->ticket_status_id ? 'selected':'' }}  value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </span>
        </div>
    </div>


    <div class="box-body">
        <form class="form-horizontal" id="reply_form" action="{{url(config('adminPrefix').'/tickets/reply/store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <input type="hidden" name="user_id" value="{{ $ticket->user_id }}">
            <input type="hidden" name="name" value="{{ getColumnValue($ticket->user) }}">
            <input type="hidden" name="email" value="{{ getColumnValue($ticket->user, 'email', 'email') }}">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-sm-1 control-label f-14 fw-bold text-sm-end require" for="message">{{ __('Reply') }}</label>
                        <div class="col-sm-11">
                            <textarea name="message" id="message" class="message form-control f-14" cols="30" rows="1"></textarea>
                            @if($errors->has('message'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('message') }}</strong>
                                </span>
                            @endif
                            <div id="error-message"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                 <div class="row">
                  <div class="col-md-4">
                      <div class="form-group row align-items-center">
                          <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="status_id">{{ __('Status') }}</label>
                          <div class="col-sm-6">
                              <select name="status_id" class="form-control f-14 select2" id="status_id">
                                @foreach($ticket_status as $status)
                                    <option {{ $status->id == $ticket->ticket_status_id ? 'selected':'' }}  value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                              </select>
                          </div>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="form-group row align-items-center">
                          <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="file">{{ __('File') }}</label>
                          <div class="col-sm-9">
                            <input type="file" name="file" class="form-control f-14 input-file-field" id="file">
                            @if($errors->has('file'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('file') }}</strong>
                                </span>
                            @endif
                          </div>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <button type="submit" class="btn btn-primary pull-right btn-flat f-14 rounded" id="reply"><i class="fa fa-spinner fa-spin d-none"></i> <span id="reply_text">{{ __('Reply') }}</span></button>
                  </div>
                </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Show Customer Query -->
@if($ticket->admin_id != NULL)
    <div class="box">
        <div class="row m-0 p-3 bg-E6">
            <div class="col-sm-1">
              <h5 class="f-14"><a href="{{ url(config('adminPrefix').'/users/edit/'. $ticket->user?->id)}}">{{ (getColumnValue($ticket->user)) }}</a></h5>

              <img alt="User profile picture" src="{{ image($ticket?->user?->picture, 'profile') }}" class="img-responsive img-circle asa img-fluid">

            </div>
            <div class="col-sm-11">
                <p class="f-14 text-justify">{!! ucfirst($ticket->message) !!}</p>
                <hr class="hr-dotted">
            </div>
        </div>
        <div class="box-footer">
            <span class="f-14"><i class="fa fa-fw fa-clock-o"></i><small><i>{{ dateFormat($ticket->created_at) }}</i></small></span>
        </div>
    </div>
@else
  <!-- Show Admin Query -->
   <div class="box">
      <div class="row m-0 p-3 bg-F4">
        <div class="col-sm-11">
           <p class="f-14 mt-2 text-justify">{!! ucfirst($ticket->message) !!}</p>
           <hr class="hr-dotted">
        </div>
        <div class="col-sm-1 text-center">

          <span><a href="{{ url(config('adminPrefix').'/admin-user/edit/'. $ticket->admin->id)}}">{{ (getColumnValue($ticket->admin)) }}</a></span>
          
          <img alt="Admin profile picture" src="{{ image($ticket?->admin?->picture, 'profile') }}" class=" img-responsive img-circle asa img-fluid">

        </div>
      </div>
       <div class="box-footer">
          <span class="pull-right f-14"><i class="fa fa-fw fa-clock-o"></i><small><i>{{ dateFormat($ticket->created_at) }}</i></small></span>
      </div>
   </div>
@endif


@foreach($ticket_replies as $ticket_reply)
    <!-- Show Customer Reply -->
    @if($ticket_reply->user_type == 'user')
        <div class="box">
            <div class="row m-0 p-3 bg-E6">
              <div class="col-sm-1 text-center">

                  <h5 class="f-14"><a href="{{ url(config('adminPrefix').'/users/edit/'. $ticket_reply->user?->id)}}">{{ (getColumnValue($ticket_reply->user) ) }}</a></h5>

                  <img alt="User profile picture" src="{{ image($ticket_reply->user?->picture, 'profile') }}" class="img-responsive img-circle asa img-fluid">

                  <hr class="hr-margin">
                    <form class="d-inline-block" action="{{ url(config('adminPrefix').'/tickets/reply/delete/') }}" accept-charset="UTF-8" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $ticket_reply->id }}">
                        <input type="hidden" name="ticket_id" value="{{ $ticket_reply->ticket_id }}">
                        <button class="btn btn-xs btn-danger btn-flat" data-message="{{ __('Are you sure you want to delete this reply?') }}" data-bs-target="#confirmDelete" data-title="Delete Reply" data-bs-toggle="modal" title="{{ __('Delete') }}" type="button" id="customer_reply_button">{{ __('Delete') }}</button>
                    </form>
              </div>
              <div class="col-sm-10">
                 <p class="f-14 mt-2 text-justify">{!! ucfirst($ticket_reply->message) !!}</p>

                 <hr class="hr-dotted">

                 @if(optional($ticket_reply->file)->filename)
                     <a href="{{ url(config('adminPrefix').'/ticket/download', $ticket_reply->file->filename) }}" class="pull-right"><i class="fa fa-fw fa-download"></i>{{ optional($ticket_reply->file)->originalname }}</a>
                 @endif
              </div>
              <div class="col-sm-1">
                <span class="btn btn-xs btn-primary pull-right edit-btn" data-id="{{ $ticket_reply->id }}" data-message="{{ $ticket_reply->message }}" data-bs-toggle="modal" data-bs-target="#modal-default"><i class="fa fa-edit"></i></span>
              </div>
            </div>
            <div class="box-footer">
                <span class="f-14" ><i class="fa fa-fw fa-clock-o"></i><small><i>{{ dateFormat($ticket_reply->created_at) }}</i></small></span>
            </div>
        </div>
    @else
      <!--  Show Admin Reply -->
      <div class="box">
          <div class="row m-0 p-3 bg-F4">

              <div class="col-sm-1">
                <span class="btn btn-xs btn-primary btn-flat edit-btn mt-3" data-bs-toggle="modal" data-id="{{ $ticket_reply->id }}" data-message="{{ $ticket_reply->message }}" data-bs-target="#modal-default" ><i class="fa fa-edit"></i></span>
              </div>

              <div class="col-sm-10">
                  <p class="f-14 mt-2 text-justify"> {!! ucfirst($ticket_reply->message) !!} </p>
                  <hr class="hr-dotted">

                  @if(optional($ticket_reply->file)->filename)
                      <a href="{{ url(config('adminPrefix').'/ticket/download', $ticket_reply->file?->filename) }}" class="pull-right"><i class="fa fa-fw fa-download"></i>{{ optional($ticket_reply->file)->originalname }}</a>
                  @endif
              </div>


              <div class="col-sm-1 text-center">
                  <h5 class="f-14"><a href="{{ url(config('adminPrefix').'/admin-user/edit/'. $ticket_reply->admin?->id)}}">{{ (getColumnValue($ticket_reply->admin)) }}</a></h5>

                  <img alt="Admin profile picture" src="{{ image($ticket_reply?->admin?->picture, 'profile') }}" class=" img-responsive img-circle asa img-fluid">

                  <hr class="hr-margin">
                      <form class="d-inline-block" action="{{ url(config('adminPrefix').'/tickets/reply/delete') }}" accept-charset="UTF-8" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $ticket_reply->id }}">
                        <input type="hidden" name="ticket_id" value="{{ $ticket_reply->ticket_id }}">

                        <button class="btn btn-xs btn-danger btn-flat f-14" data-message="{{ __('Are you sure you want to delete this reply?') }}" data-bs-target="#confirmDelete" data-title="{{ __('Delete Reply') }}" data-bs-toggle="modal" title="{{ __('Delete') }}" type="button" id="admin_reply_button">{{ __('Delete') }}</button>

                      </form>
              </div>
          </div>

          <div class="box-footer">
              <span class="pull-right f-14"><i class="fa fa-fw fa-clock-o"></i><small><i>{{ dateFormat($ticket_reply->created_at) }}</i></small></span>
          </div>
      </div>
    @endif
@endforeach

<!-- Modal Start -->
<div class="modal fade" id="modal-default">
    <div class="modal-dialog">

        <form  method="POST" action="{{ url(config('adminPrefix').'/tickets/reply/update') }}" id="replyModal">
            {{ csrf_field() }}

            <input type="hidden" name="id" id="reply_id">

            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title f-18">{{ __('Update Reply') }}</h4>
                <a type="button" class="close f-18" data-bs-dismiss="modal">×</a>
              </div>

              <div class="modal-body">
                <div class="form-group row align-items-center">

                  <div class="modal_editor_textarea">
                      <textarea name="message" class="form-control f-14 editor h-200"></textarea>
                  </div>

                  <div id="error-message-modal"></div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-flat pull-left f-14 rounded" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary btn-flat f-14 rounded">{{ __('Update') }}</button>
              </div>
            </div>
        </form>

    </div>
</div>
</div>

@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    'use strict';
    var ticket_id = "{{ $ticket->id }}";
    var token = "{!! csrf_token() !!}";
    var extensions = JSON.parse(@json($extensions)).join('|');
    var extensionsMessage = JSON.parse(@json($extensions)).join(', ');
    var errorMessage = '{{ __("Please select (:x) file.") }}';
    var fileErrorMessage = errorMessage.replace(':x', extensionsMessage);
    var ticketStatusChangeUrl = "{{ url(config('adminPrefix').'/tickets/change_ticket_status') }}";
</script>
<script src="{{ asset('public/admin/customs/js/ticket/ticket.min.js') }}" type="text/javascript"></script>

@endpush

