
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
                        <li class="nav-item border-0"><a class="nav-link" href="{{ url(config('adminPrefix').'/settings/sms/twilio') }}">{{ __('Twilio') }}</a></li>
                        <li class="nav-item border-0"><a class="nav-link active" href="{{ url(config('adminPrefix').'/settings/sms/nexmo')}}">{{ __('Nexmo') }}</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade in show active" id="tab_1">
                            <div>
                                <div class="container-fluid">
                                    <div class="tab-pane" id="tab_2">

                                        <form action="{{ url(config('adminPrefix').'/settings/sms/nexmo') }}" method="POST" class="form-horizontal" id="nexmo_sms_setting_form">
                                            {!! csrf_field() !!}

                                            <div class="box-body">

                                                <input type="hidden" name="type" value="{{ base64_encode($nexmo->type) }}">

                                                {{-- Name --}}
                                                <div class="form-group d-none">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end">{{ __('Name') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="name" class="form-control f-14"
                                                            value="{{ $nexmo->type == 'nexmo' ? 'Nexmo' : '' }}" placeholder="{{ __('Enter Sms Gateway Name') }}" readonly>
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
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end">{{ __('Key') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="nexmo[Key]" class="form-control f-14"
                                                            value="{{ isset($credentials->Key) ? $credentials->Key : '' }}" placeholder="{{ __('Enter Nexmo Key') }}">
                                                            @if ($errors->has('nexmo.Key'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('nexmo.Key') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                {{-- Secret --}}
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end">{{ __('Secret') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="nexmo[Secret]" class="form-control f-14"
                                                            value="{{ isset($credentials->Secret) ? $credentials->Secret : '' }}" placeholder="{{ __('Enter Nexmo Secret') }}">
                                                            @if ($errors->has('nexmo.Secret'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('nexmo.Secret') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                {{-- Secret --}}
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end mt-2">{{ __('Default Phone Number') }}</label>
                                                        <div class="col-md-7">
                                                            <input type="text" name="nexmo[default_nexmo_phone_number]" class="form-control f-14"
                                                            value="{{ isset($credentials->default_nexmo_phone_number) ? $credentials->default_nexmo_phone_number : '' }}" placeholder="{{ __('Enter Nexmo Default Phone Number') }}">
                                                            @if ($errors->has('nexmo.default_nexmo_phone_number'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('nexmo.default_nexmo_phone_number') }}</span>
                                                            @endif
                                                            <div class="clearfix"></div>
                                                            <h6 class="form-text text-muted f-12"><strong>*{{ __('Must enter phone number without(+) symbol.') }}</strong></h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>

                                                {{-- Status --}}
                                                <div class="form-group">
                                                    <div class="row">
                                                        <label class="col-md-3 control-label mt-11 f-14 fw-bold text-sm-end">{{ __('Status') }}</label>
                                                        <div class="col-md-7">
                                                            <select name="status" class="select2 select2-hidden-accessible">
                                                                <option {{ $nexmo->status == 'Active' ? 'selected' : '' }} value="Active">{{ __('Active') }}</option>
                                                                <option {{ $nexmo->status == 'Inactive' ? 'selected' : '' }} value="Inactive">{{ __('Inactive') }}</option>
                                                            </select>
                                                            @if ($errors->has('status'))
                                                                <span class="fw-bold text-danger">{{ $errors->first('status') }}</span>
                                                            @endif
                                                            <div class="clearfix"></div>
                                                            <h6 class="form-text text-muted f-12"><strong>*{{ __('Incoming SMS messages might be delayed by') }} {{ ucfirst($nexmo->type) }}.</strong></h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mt-10">
                                                        <a id="cancel_anchor" href="{{ url(config('adminPrefix').'/settings/sms/nexmo') }}" class="btn btn-theme-danger f-14">{{ __('Cancel') }}</a>
                                                        <button type="submit" class="btn btn-theme pull-right f-14" id="sms-settings-nexmo-submit-btn">
                                                            <i class="fa fa-spinner fa-spin d-none"></i> <span id="sms-settings-nexmo-submit-btn-text">{{ __('Update') }}</span>
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


        $('#nexmo_sms_setting_form').validate({
            rules: {
                "nexmo[Key]": {
                    required: true,
                },
                "nexmo[Secret]": {
                    required: true,
                },
                "nexmo[default_nexmo_phone_number]": {
                    required: true,
                    digits: true,
                },
            },
            messages: {
                "nexmo[Key]": {
                    required: "Nexmo Key is required!",
                },
                "nexmo[Secret]": {
                    required: "Nexmo Secret is required!",
                },
                "nexmo[default_nexmo_phone_number]": {
                    required: "Nexmo Default Phone Number is required",
                },
            },
            submitHandler: function(form)
            {
                $("#sms-settings-nexmo-submit-btn").attr("disabled", true);
                $(".fa-spin").removeClass("d-none");
                $("#sms-settings-nexmo-submit-btn-text").text('Updating...');
                $('#cancel_anchor').attr("disabled",true);
                $('#sms-settings-nexmo-submit-btn').click(false);
                form.submit();
            }
        });

    </script>
@endpush
