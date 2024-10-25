@extends('admin.layouts.master')
@section('title', __('Email Settings'))

@section('page_content')

    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Email Settings @if(@$result['status']==1)(<span style="color: green"><i class="fa fa-check" aria-hidden="true"></i>
               Verified</span>) @endif </h3>
                </div>

                <form action="{{ url(config('adminPrefix').'/settings/email') }}" method="post" class="form-horizontal"
                      id="emai_settings">
                {!! csrf_field() !!}

                <!-- box-body -->
                    <div class="box-body">
                        <!-- driver -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="driver">{{ __('Driver') }}</label>
                            <div class="col-sm-6">
                                <select id="driver" name="driver" class="form-control f-14 select2">
                                    <option value="smtp" {{ $result['email_protocol'] == "smtp" ? "selected" : "" }}>{{ __('SMTP') }}</option>
                                    <option value="sendmail" {{ $result['email_protocol'] == "sendmail" ? "selected" : "" }}>{{ __('Send Mail') }}</option>
                                </select>
                                @if($errors->has('email_protocol'))
                                    <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('email_protocol') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div id="smtpFields" @if($result['email_protocol'] <> "smtp") class="d-none" @endif>
                            <!-- host -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="host">{{ __('Host') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="host" class="form-control f-14" value="{{ $result['smtp_host'] ?: 'sandbox.smtp.mailtrap.io' }}" placeholder="{{ __('Host') }}" id="host">

                                    @if($errors->has('smtp_host'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_host') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- port -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="port">{{ __('Port') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="port" class="form-control f-14" value="{{ $result['smtp_port'] ?: '587' }}" placeholder="{{ __('Port') }}" id="port">

                                    @if($errors->has('smtp_port'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_port') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- from_address -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="from_address">{{ __('From Address') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="from_address" class="form-control f-14" value="{{ $result['from_address'] ?: auth('admin')->user()->email }}" placeholder="{{ __('From Address (ex - example@mail.com)') }}" id="from_address">
                                    @if($errors->has('from_address'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('from_address') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- from_name -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="from_name">{{ __('From Name') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="from_name" class="form-control f-14" value="{{ $result['from_name'] ?: settings('name') }}" placeholder="{{ __('From Name (ex - Paymoney)') }}" id="from_name">
                                    @if($errors->has('from_name'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('from_name') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- encryption -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="encryption">{{ __('Encryption') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="encryption" class="form-control f-14" value="{{ $result['email_encryption'] ?: 'tls' }}" placeholder="{{ __('Encryption') }}" id="encryption">

                                    @if($errors->has('email_encryption'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('email_encryption') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- username -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="username">{{ __('Username') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="username" class="form-control f-14" value="{{ $result['smtp_username'] ?? '' }}" placeholder="{{ __('Username') }}" id="username">

                                    @if($errors->has('smtp_username'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_username') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- password -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="password">{{ __('Password') }}</label>
                                <div class="col-sm-6">
                                    <input type="password" name="password" class="form-control f-14" value="{{ $result['smtp_password'] ?? '' }}" placeholder="{{ __('Password') }}" id="password">

                                    @if($errors->has('smtp_password'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_password') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- box-footer -->
                    @if(Common::has_permission(auth('admin')->user()->id, 'edit_email_setting'))
                    <div class="box-footer">
                        <button class="btn btn-theme pull-right f-14" type="submit">{{ __('Submit') }}</button>
                    </div>
                    @endif
                    <!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

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

    $('#emai_settings').validate({
        rules: {
            driver: {
                required: true,
            },
            host: {
                required: true,
            },
            port: {
                required: true,
                number: true,
            },
            from_address: {
                required: true,
                email: true,
            },
            from_name: {
                required: true,
            },
            encryption: {
                required: true,
            },
            username: {
                required: true,
            },
            password: {
                required: true,
            },
        },
    });

    $("#driver").on('change',function(e){
        e.preventDefault();
        smtpfield=$("#smtpFields");
        if($(this).val() == "smtp"){
            smtpfield.removeClass('d-none');
        } else {
            smtpfield.addClass('d-none');
        }
    });
</script>

@endpush
