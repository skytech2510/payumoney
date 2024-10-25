@extends('admin.layouts.master')
@section('title', __('Sms Notification Settings'))

@section('head_style')
<!-- custom-checkbox -->
<link rel="stylesheet" type="text/css" href="{{ asset('public/admin/customs/css/custom-checkbox.min.css') }}">
@endsection

@section('page_content')
<!-- Main content -->
<div class="row">
    <div class="col-md-3 settings_bar_gap">
        @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
        <div class="box box-info">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" id="tabs">
                    <li class="item-nav"><a class="nav-link" href="{{ url(config('adminPrefix').'/settings/notification-types') }}">{{ __('Notification Types') }}</a></li>
                    <li><a href="{{ url(config('adminPrefix').'/settings/notification-settings/email')}}">{{ __('Email Notification Settings') }}</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade in show active" id="tab_1">
                        <div>
                            <div class="table-responsive">
                                <div class="tab-pane" id="tab_2">

                                <form action="{{ url(config('adminPrefix').'/settings/notification-settings/update') }}" method="POST" class="form-horizontal" id="sms_notification_setting_form">
                                    {!! csrf_field() !!}

                                    <div class="box-body">

                                        @foreach ($notificationSettings as $notificationSmsSetting)

                                        <input type="hidden" name="notification[{{ $notificationSmsSetting->recipient_type }}][{{ $notificationSmsSetting->notification_type?->alias }}][id]" value="{{ $notificationSmsSetting->id }}">

                                        <div class="form-group">

                                            {{-- Name --}}
                                            <label class="col-sm-3 control-label">{{ $notificationSmsSetting->name }}</label>
                                            <div class="col-sm-6">

                                                {{-- Checkbox --}}
                                                <div class="col-sm-3">
                                                    <label class="checkbox-container">
                                                        <input type="checkbox" name="notification[{{  $notificationSmsSetting->recipient_type }}][{{ $notificationSmsSetting->alias }}][status]" {{ isset($notificationSmsSetting->status) && $notificationSmsSetting->status == 'Yes' ? 'checked' : '' }} class="sms_checkbox" data-rel="{{$notificationSmsSetting->alias}}">
                                                        <span class="checkmark"></span>
                                                    </label>
                                                </div>

                                                {{--  Sms --}}
                                                <div class="col-sm-9">
                                                    <input class="form-control" name="notification[{{ $notificationSmsSetting->recipient_type }}][{{ $notificationSmsSetting->alias }}][recipient]" type="text" value="{{ isset($notificationSmsSetting->recipient) ? $notificationSmsSetting->recipient : '' }}" placeholder="Enter number for {{ $notificationSmsSetting->name }}" id="sms_{{$notificationSmsSetting->alias}}" {{ isset($notificationSmsSetting->status) && $notificationSmsSetting->status == 'No' ? 'readonly' : ''}}>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        @endforeach
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mt-10">
                                                <a href="{{ url(config('adminPrefix').'/settings/notification-types') }}" class="btn btn-danger btn-flat">{{ __('Cancel') }}</a>
                                                <button class="btn btn-primary pull-right btn-flat" type="submit">{{ __('Update') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-footer">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>


<script type="text/javascript">

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    // sms validation
    $('#sms_notification_setting_form').validate({

        rules: {
            "notification[sms][deposit][recipient]": {
                minlength: 10,
            },
            "notification[sms][payout][recipient]": {
                minlength: 10,
            },
            "notification[sms][send][recipient]": {
                minlength: 10,
            },
            "notification[sms][request][recipient]": {
                minlength: 10,
            },
            "notification[sms][exchange][recipient]": {
                minlength: 10,
            },
            "notification[sms][payment][recipient]": {
                minlength: 10,
            },
        },
    });

    // Sms
    $(document).on('click', '.sms_checkbox', function() {
        var inputName = $(this).attr("data-rel");
        if (this.checked == true) {
            $("#sms_"+inputName).prop('readonly',false);
        } else {
            $("#sms_"+inputName).prop('readonly',true);

        }
    });
</script>

@endpush
