<?php
/**
 * Created By: TechVillage.net
 * Start Date: 22-Jan-2018
 */
$logo = settings('logo');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="parvez">
    <title>{{ __('Admin') }}</title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- Bootstrap 5.0.2 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/bootstrap-5.0.2/css/bootstrap.min.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/font-awesome-4.7.0/css/font-awesome.min.css')}}">

    <!-- Theme style -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/admin/templates/adminLte/AdminLTE.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/admin/templates/css/style.min.css') }}">
    <link rel="shortcut icon" href="{{ faviconPath() }}" />

</head>

<body class="hold-transition login-page bg-ec">
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url(config('adminPrefix').'/') }}">{!! getSystemLogo('img-responsive log-img') !!}</a>
    </div>

    <div class="login-box-body login-design">

        @if(Session::has('message'))
            <div class="alert {{ Session::get('alert-class') }} text-center d-flex justify-content-between">
                <strong class="f-14">{{ Session::get('message') }}</strong>
                <a type="button" class="close text-decoration-none f-18 ms-2 fw-bold" data-bs-dismiss="alert" aria-hidden="true">&times;</a>
            </div>
        @endif

        <div class="alert alert-danger text-center d-none mb-0" id="error_message_div" role="alert">
            <p class="mb-0 f-14" id="error_message"></p>
            <p><a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a></p>
        </div>
        <!-- /.Flash Message  -->

        <form action="{{ url(config('adminPrefix').'/forget-password') }}" method="post" id="forget-password-form">
            {{ csrf_field() }}

            <div class="form-group has-feedback position-relative {{ $errors->has('email') ? ' has-error' : '' }}">
                <label class="control-label sr-only f-14" for="email">{{ __('Email') }}</label>
                <input type="email" class="form-control f-14" placeholder={{ __('Email') }} name="email" id="email">
                <span class="fa fa-envelope form-control-feedback position-absolute mail-icon"></span>
                @if ($errors->has('email'))
                    <span class="help-block f-14"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-theme f-14 px-4" id="admin-forget-password-submit-btn">
                    <i class="fa fa-spinner fa-spin d-none"></i>
                    <span class="fw-bold" id="admin-forget-password-submit-btn-text">{{ __('Submit') }}</span>
                </button>
                <a href="{{url(config('adminPrefix').'')}}" class="btn btn-theme f-14">{{ __('Back To Login') }}</a>
            </div>
        </form>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 3 -->
<script src="{{ asset('public/dist/libraries/jquery-3.2.1/dist/jquery.min.js') }}" type="text/javascript"></script>

<script src="{{ asset('public/dist/js/popper.min.js') }}" type="text/javascript"></script>
<!-- Bootstrap 5.0.2 -->
<script src="{{ asset('public/dist/libraries/bootstrap-5.0.2/js/bootstrap.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<script>
    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
    });

    $('#forget-password-form').validate({
        errorClass: "has-error",
        rules: {
            email: {
                required: true,
                email: true,
            },
        },
        submitHandler: function(form)
        {
            $("#admin-forget-password-submit-btn").attr("disabled", true).click(function (e)
            {
                e.preventDefault();
            });
            $(".fa-spin").removeClass("d-none");
            $("#admin-forget-password-submit-btn-text").text("Submitting..");
            form.submit();
        }
    });
</script>
</body>
