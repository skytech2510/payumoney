@extends('admin.layouts.master')
@section('title', __('SMS Settings'))

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
                        <li class="nav-item border-0"><a class="nav-link active" href="{{ url(config('adminPrefix').'/settings/sms/twilio') }}">{{ __('Twilio') }}</a></li>
                        <li class="nav-item border-0"><a class="nav-link" href="{{ url(config('adminPrefix').'/settings/sms/nexmo')}}">{{ __('Nexmo') }}</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade in show active" id="tab_1">
                            <div>
                                <div class="container-fluid">
                                    <div class="tab-pane" id="tab_2">

                                        <form action="{{ url(config('adminPrefix').'/settings/sms/twilio') }}" method="POST" class="form-horizontal" id="twilio_sms_setting_form">
                                            {!! csrf_field() !!}

                                            <input type="hidden" name="type" value="{{ base64_encode($twilio->type) }}">

                                            <div class="box-body">

                                                {{-- Name --}}
                                                <div class="form-group d-none">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="name" class="form-control f-14" value="{{ $twilio->type == 'twilio' ? 'Twilio' : '' }}" placeholder="{{ __('Enter Twilio Sms Gateway Name') }}" id="name" readonly>
                                                            @if ($errors->has('name'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('name') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                {{-- Key --}}
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end" for="twilio_account_sid">{{ __('Account SID') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="twilio[account_sid]" class="form-control f-14" value="{{ isset($credentials->account_sid) ? $credentials->account_sid : '' }}" placeholder="{{ __('Enter Twilio Account SID') }}" id="twilio_account_sid">
                                                            @if ($errors->has('twilio.account_sid'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('twilio.account_sid') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                {{-- Secret --}}
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end" for="twilio_auth_token">{{ __('Auth Token') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="twilio[auth_token]" class="form-control f-14" value="{{ isset($credentials->auth_token) ? $credentials->auth_token : '' }}" placeholder="{{ __('Enter Twilio Auth Token') }}" id="twilio_auth_token">
                                                            @if ($errors->has('twilio.auth_token'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('twilio.auth_token') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                {{-- Secret --}}
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end mt-2" for="twilio_default_twilio_phone_number">{{ __('Default Phone Number') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="twilio[default_twilio_phone_number]" class="form-control f-14"
                                                            value="{{ isset($credentials->default_twilio_phone_number) ? $credentials->default_twilio_phone_number : '' }}" placeholder="{{ __('Enter Twilio Default Phone Number') }}" id="twilio_default_twilio_phone_number">
                                                            @if ($errors->has('twilio.default_twilio_phone_number'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('twilio.default_twilio_phone_number') }}</span>
                                                            @endif
                                                            <div class="clearfix"></div>
                                                            <h6 class="form-text text-muted f-12 mt-2"><strong>*{{ __('Must enter phone number without(+) symbol.') }}</strong></h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                {{-- Status --}}
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end" for="status">{{ __('Status') }}</label>
                                                        <div class="col-md-7">
                                                            <select name="status" class="select2 select2-hidden-accessible" id="status">
                                                                <option {{ $twilio->status == 'Active' ? 'selected' : '' }} value="Active">{{ __('Active') }}</option>
                                                                <option {{ $twilio->status == 'Inactive' ? 'selected' : '' }} value="Inactive">{{ __('Inactive') }}</option>
                                                            </select>
                                                            @if ($errors->has('status'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('status') }}</span>
                                                            @endif
                                                            <div class="clearfix"></div>
                                                            <h6 class="form-text text-muted f-12"><strong>*{{ __('Incoming SMS messages might be delayed by') }} {{ ucfirst($twilio->type) }}.</strong></h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mt-10">
                                                        <a id="cancel_anchor" href="{{ url(config('adminPrefix').'/settings/sms/twilio') }}" class="btn btn-theme-danger f-14">{{ __('Cancel') }}</a>
                                                        <button type="submit" class="btn btn-theme pull-right f-14" id="sms-settings-twilio-submit-btn">
                                                            <i class="fa fa-spinner fa-spin d-none"></i> <span id="sms-settings-twilio-submit-btn-text">{{ __('Update') }}</span>
                                                        </button>
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

    <!-- jquery.validate -->
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        $(function () {
            $(".select2").select2({
            });
        });

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


        $('#twilio_sms_setting_form').validate({
            rules: {
                "twilio[account_sid]": {
                    required: true,
                },
                "twilio[auth_token]": {
                    required: true,
                },
                "twilio[default_twilio_phone_number]": {
                    required: true,
                    digits: true,
                },
            },
            messages: {
                "twilio[account_sid]": {
                    required: "Twilio Account SID is required!",
                },
                "twilio[auth_token]": {
                    required: "Twilio Auth Token is required!",
                },
                "twilio[default_twilio_phone_number]": {
                    required: "Twilio Default Phone Number is required",
                },
            },
            submitHandler: function(form)
            {
                $("#sms-settings-twilio-submit-btn").attr("disabled", true);
                $(".fa-spin").removeClass("d-none");
                $("#sms-settings-twilio-submit-btn-text").text('Updating...');
                $('#cancel_anchor').attr("disabled",true);
                $('#sms-settings-twilio-submit-btn').click(false);
                form.submit();
            }
        });

    </script>
@endpush
