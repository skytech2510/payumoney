@extends('admin.layouts.master')

@section('title', __('Add Admin'))

@section('page_content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Add Admin') }}</h3>
                </div>
                <form action="{{ url(config('adminPrefix').'/admin-users/store') }}" class="form-horizontal" id="user_form" method="POST">
                    <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">
                    <div class="box-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="first_name">{{ __('First Name') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter First Name') }}" name="first_name" type="text" id="first_name">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="last_name">{{ __('Last Name') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter Last Name') }}" name="last_name" type="text" id="last_name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="email">{{ __('Email') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter a valid email') }}" name="email" type="email" id="email">
                                <span id="email_error"></span>
                                <span id="email_ok" class="text-success"></span>
                            </div>
                        </div>

                        {{-- Role --}}
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="role">{{ __('Group') }}</label>
                            <div class="col-sm-6">
                                <select class="select2" name="role" id="role">
                                    @foreach ($roles as $role)
                                        <option value='{{ $role->id }}'> {{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="password">{{ __('Password') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Enter new Password (min 6 characters)') }}" name="password" type="password" id="password">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end require" for="password_confirmation">{{ __('Confirm Password') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14" placeholder="{{ __('Confirm password (min 6 characters)') }}" name="password_confirmation" type="password" id="password_confirmation">
                            </div>
                        </div>

                        <!-- box-footer -->
                        <div class="row">
                            <div class="col-sm-6 offset-md-3">
                                <a class="btn btn-theme-danger me-1 f-14" href="{{ url(config('adminPrefix').'/admin_users') }}" id="users_cancel">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-theme" id="users_create"><i class="fa fa-spinner fa-spin d-none"></i> <span class="f-14" id="users_create_text">{{ __('Create') }}</span></button>
                            </div>
                        </div>
                        <!-- /.box-footer -->
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

    $(function () {
        $(".select2").select2({});
    })

    $.validator.setDefaults({
        highlight: function (element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    $('#user_form').validate({
        rules: {
            first_name: {
                required: true,
            },
            last_name: {
                required: true,
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            },
            password_confirmation: {
                required: true,
                minlength: 6,
                equalTo: "#password"
            }
        },
        messages: {
            password_confirmation: {
                equalTo: "Please enter same value as the password field!"
            }
        },
        submitHandler: function (form) {
            $("#users_create").attr("disabled", true);
            $(".fa-spin").removeClass("d-none");
            $("#users_create_text").text('Creating...');
            $('#users_cancel').attr("disabled", "disabled");
            form.submit();
        }
    });

    // Validate Emal via Ajax
    $(document).ready(function () {
        $("#email").on('keyup keypress', function (e) {
            if (e.type == "keyup" || e.type == "keypress") {
                var email = $('#email').val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: SITE_URL+"/"+ADMIN_PREFIX+"/email_check",
                    dataType: "json",
                    data: {
                        'email': email,
                        'type': 'admin-email'
                    }
                })
                    .done(function (response) {
                        // console.log(response);
                        if (response.status == true) {
                            emptyEmail();
                            if (validateEmail(email)) {
                                $('#email_error').addClass('error').html(response.fail).css("font-weight", "bold");
                                $('#email_ok').html('');
                                $('form').find("button[type='submit']").prop('disabled',true);
                            } else {
                                $('#email_error').html('');
                            }
                        }
                        else if (response.status == false) {
                            emptyEmail();
                            if (validateEmail(email)) {
                                $('#email_error').html('');
                            } else {
                                $('#email_ok').html('');
                            }
                            $('form').find("button[type='submit']").prop('disabled',false);
                        }

                        /**
                         * [validateEmail description]
                         * @param  {null} email [regular expression for email pattern]
                         * @return {null}
                         */
                        function validateEmail(email) {
                            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return re.test(email);
                        }

                        /**
                         * [checks whether email value is empty or not]
                         * @return {void}
                         */
                        function emptyEmail() {
                            if (email.length === 0) {
                                $('#email_error').html('');
                                $('#email_ok').html('');
                            }
                        }
                    });
            }
        });
    });


</script>
@endpush


