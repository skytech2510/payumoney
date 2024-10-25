@extends('admin.layouts.master')
@section('title', __('Email Notification Settings'))

@section('head_style')
    <!-- bootstrap-toggle -->
    <link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
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
                        <li class="nav-item"><a class="nav-link" href="{{ url(config('adminPrefix').'/settings/notification-types') }}">{{ __('Notification Types') }}</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ url(config('adminPrefix').'/settings/notification-settings/email')}}">{{ __('Email Notification Settings') }}</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade in show active" id="tab_1">
                            <div>
                                <div class="container-fluid">
                                    <div class="tab-pane" id="tab_2">

                                        <form action="{{ url(config('adminPrefix').'/settings/notification-settings/update') }}" method="POST" class="form-horizontal" id="email_notification_setting_form">
                                            {!! csrf_field() !!}

                                            <div class="box-body">
                                                @foreach ($notificationSettings as $notificationEmailSetting)

                                                    <input type="hidden" name="notification[{{ $notificationEmailSetting->recipient_type }}][{{ $notificationEmailSetting->notification_type?->alias }}][id]" value="{{ $notificationEmailSetting->id }}">

                                                    <div class="form-group">
                                                        <div class="row">
                                                            {{-- Name --}}
                                                            <label class="col-md-3 control-label mt-11 f-12 fw-bold text-sm-end">{{ $notificationEmailSetting->name }}</label>

                                                            {{-- Checkbox --}}
                                                            <div class="col-md-2 switch">
                                                                <input type="checkbox" data-toggle="toggle" name="notification[{{  $notificationEmailSetting->recipient_type }}][{{ $notificationEmailSetting->alias }}][status]" {{ isset($notificationEmailSetting->status) && $notificationEmailSetting->status == 'Yes' ? 'checked' : '' }}
                                                                    class="email_checkbox"
                                                                    data-rel="{{ $notificationEmailSetting->alias }}"
                                                                    id="notification[{{  $notificationEmailSetting->recipient_type }}][{{ $notificationEmailSetting->alias }}][status]" >
                                                            </div>

                                                            {{--  Email --}}
                                                            <div class="col-md-7">
                                                                <input type="text" name="notification[{{ $notificationEmailSetting->recipient_type }}][{{ $notificationEmailSetting->alias }}][recipient]" class="form-control f-14"
                                                                value="{{ isset($notificationEmailSetting->recipient) ? $notificationEmailSetting->recipient : '' }}"
                                                                placeholder="Enter email for {{ $notificationEmailSetting->name }}"
                                                                id="email_{{ $notificationEmailSetting->alias }}" {{ isset($notificationEmailSetting->status) && $notificationEmailSetting->status == 'No' ? 'readonly' : ''}}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                @endforeach
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mt-10">
                                                        <a href="{{ url(config('adminPrefix').'/settings/notification-types') }}" class="btn btn-theme-danger f-14">{{ __('Cancel') }}</a>
                                                        <button class="btn btn-theme pull-right f-14" type="submit">{{ __('Update') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
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
    <!-- bootstrap-toggle -->
    <script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script>

    <!-- jquery.validate -->
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        var investment = "{{ isActive('Investment') ? true : false }}";

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

        // Email validation
        $('#email_notification_setting_form').validate({
            rules: {
                "notification[email][deposit][recipient]": {
                    email: true,
                    required: function(element){
                        var statusCheck = $('input[name="notification[email][deposit][status]"]:checked').length;
                        if(statusCheck == 1)
                        {
                            return $("#email_deposit").val()=="";
                        }
                        else
                        {
                            return false;
                        }
                    },
                },
                "notification[email][payout][recipient]": {
                    email: true,
                    required: function(element){
                        var statusCheck = $('input[name="notification[email][payout][status]"]:checked').length;
                        if(statusCheck == 1)
                        {
                            return $("#email_payout").val()=="";
                        }
                        else
                        {
                            return false;
                        }
                    },
                },
                "notification[email][send][recipient]": {
                    email: true,
                    required: function(element){
                        var statusCheck = $('input[name="notification[email][send][status]"]:checked').length;
                        if(statusCheck == 1)
                        {
                            return $("#email_send").val()=="";
                        }
                        else
                        {
                            return false;
                        }
                    },
                },
                "notification[email][request][recipient]": {
                    email: true,
                    required: function(element){
                        var statusCheck = $('input[name="notification[email][request][status]"]:checked').length;
                        if(statusCheck == 1)
                        {
                            return $("#email_request").val()=="";
                        }
                        else
                        {
                            return false;
                        }
                    },
                },
                "notification[email][exchange][recipient]": {
                    email: true,
                    required: function(element){
                        var statusCheck = $('input[name="notification[email][exchange][status]"]:checked').length;
                        if(statusCheck == 1)
                        {
                            return $("#email_exchange").val()=="";
                        }
                        else
                        {
                            return false;
                        }
                    },
                },
                "notification[email][payment][recipient]": {
                    email: true,
                    required: function(element){
                        var statusCheck = $('input[name="notification[email][payment][status]"]:checked').length;
                        if(statusCheck == 1)
                        {
                            return $("#email_payment").val()=="";
                        }
                        else
                        {
                            return false;
                        }
                    },
                },
                "notification[email][investment][recipient]": {
                    email: investment ? true : false,
                    required: function(element) {
                        var statusCheck = $('input[name="notification[email][investment][status]"]:checked').length;
                        if(statusCheck == 1) {
                            return $("#email_investment").val() == "";
                        } else {
                            return false;
                        }
                    },
                },
            },
        });

        // Email - on change due to http://www.bootstraptoggle.com/
        $(document).on('change', '.email_checkbox', function() {
            var inputName = $(this).attr("data-rel");
            if (this.checked == true) {
                $("#email_"+inputName).prop('readonly',false);
            } else {
                $("#email_"+inputName).prop('readonly',true);

            }
        });

    </script>
@endpush

