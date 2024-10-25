@extends('admin.layouts.master')

@section('title', __('Edit Nofication Type'))

@section('page_content')

<div class="row">
    <div class="col-md-3 settings_bar_gap">
        @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
        <!-- Horizontal Form -->
        <div class="box box-info">
            <div class="box-header with-border text-center">
                <h3 class="box-title">{{ __('Edit Notification Type') }}</h3>
            </div>

            <!-- form start -->
            <form method="POST" action="{{ url(config('adminPrefix').'/settings/notification-types/update/'.$notificationType->id) }}" class="form-horizontal" id="edit_notification_form">
                {{ csrf_field() }}

                <input type="hidden" name="notification_type_id" value="{{ base64_encode($notificationType->id) }}" id="notification_type_id">

                <div class="box-body">
                    {{-- Notification Type Name --}}
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="notification_type_name">{{ __('Name') }}</label>
                        <div class="col-sm-6">
                            <input type="text" name="notification_type_name" class="form-control f-14" value="{{ $notificationType->name }}" id="notification_type_name" autocomplete="off">
                            <span id="type_error"></span>
                            @if($errors->has('notification_type_name'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('notification_type_name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Notification Type Status --}}
                    <div class="form-group row align-items-center">
                        <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="notification_type_status">{{ __('Status') }}</label>
                        <div class="col-sm-6 f-14">
                            <select class="select2" name="notification_type_status" id="notification_type_status">
                                <option value='Active' {{ isset($notificationType->status) && $notificationType->status == 'Active' ? 'selected':"" }}>{{ __('Active') }}</option>
                                <option value='Inactive' {{ isset($notificationType->status) && $notificationType->status == 'Inactive' ? 'selected':"" }}>{{ __('Inactive') }}</option>
                            </select>
                            @if($errors->has('notification_type_status'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('notification_type_status') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a class="btn btn-danger f-14" href="{{ url(config('adminPrefix').'/settings/notification-types') }}">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary pull-right f-14" id="updateNotification">&nbsp; {{ __('Update') }} &nbsp;</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')
    <!-- jquery.validate -->
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        $(window).on('load', function()
        {
            $(".select2").select2({});
        });

        function checkDuplicateNotificationTypeName()
        {
            //event.preventDefault();
            var notification_type_name = $('#notification_type_name').val();
            var notification_type_id   = $('#notification_type_id').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: SITE_URL+"/"+ADMIN_PREFIX+"/settings/notification-type-name/check",
                dataType: "json",
                data: {
                    'notification_type_name': notification_type_name,
                    'notification_type_id'  : notification_type_id,
                    '_token':"{{csrf_token()}}"
                }
            })
            .done(function(response) {
                if (response.status == false) {
                    $('#type_error').text(response.fail).css({"font-weight":"bold", "color":"red"});
                    $('form').find("button[type='submit']").prop('disabled',true);
                    $('#updateNotification').prop('disabled',true);
                } else {
                    $('#type_error').text('');
                    $('#updateNotification').prop('disabled',false);
                }
            });
        }

        //Notification Type Name check
        $(document).on('input','#notification_type_name', function()
        {
            checkDuplicateNotificationTypeName();
        });

        $.validator.setDefaults({
            highlight: function(element) {
                $(element).parent('div').addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).parent('div').removeClass('has-error');
            },
        });

        $('#edit_notification_form').validate({
            rules: {
                notification_type_name: {
                    required: true,
                },
                notification_type_status: {
                    required: true,
                },
            },
            messages: {
                notification_type_name: {
                    required: "Please enter notification type name.",
                },
            },
        });

    </script>
@endpush