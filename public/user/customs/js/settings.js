'use strict';

if ($('.main-containt').find('#identitiyVerify').length) {

    // Disable Submit Button
    $('#identitiyVerifyForm').on('submit', function () {
        $(".spinner").removeClass('d-none');
        $("#identitiyVerifySubmitBtn").attr("disabled", true);
        $("#identitiyVerifySubmitBtnText").text(submitButtonText);
    });
}

if ($('.main-containt').find('#addressVerify').length) {
    
    // Disable Submit Button
    $('#addressVerifyForm').on('submit', function () {
        $(".spinner").removeClass('d-none');
        $("#addressVerifySubmitBtn").attr("disabled", true);
        $("#addressVerifySubmitBtnText").text(submitButtonText);
    });
}

if ($('.main-containt').find('#twofaVerification').length) {
    //check phone
    $(document).on('ready', function() {
        $("#two_step_verification_type").change(function() {
            if ($(this).val() == 'phone') {
                $.post({
                    url: twofaVerifyPhoneUrl,
                    data: {
                        '_token': token,
                    },
                    beforeSend: function() {
                        $('#2fa_submit').prop('disabled', false);
                    }
                })
                .done(function(response) {
                    if (!response.status) {
                        $('#2fa-error').text(response.message);
                        $('#2fa_submit').prop('disabled', true);
                    } else {
                        $('#2fa-error').text('');
                        $('#2fa_submit').prop('disabled', false);
                    }
                });
            } else {
                $('#2fa-error').text('');
                $('#2fa_submit').prop('disabled', false);
            }
        });
    });

    function disableSubmitButton(text) {
        $('#2fa_submit').prop('disabled', true);
        $(".spinner").removeClass('d-none');
        $('#2fa_submit_text').text(text);
    }

    function enableSubmitButton(text) {
        $('#2fa_submit').prop('disabled', false);
        $(".spinner").addClass('d-none');
        $('#2fa_submit_text').text(text);
    }

    function handleSuccess(response) {
        const { twoFaVerificationTypeForResponse, twoFa_type } = response;
        $('#twoFaVerificationType').val(twoFaVerificationTypeForResponse);
        if (twoFaVerificationTypeForResponse === 'google_authenticator') {
            $('#section_2fa_form').addClass('d-none');
            $('#section_2fa_verify').addClass('d-none');
            $('#section_google2fa').removeClass('d-none');
            $('#qrsecret').html(response.secret).addClass('d-none');
            $('#qr_image').attr('src', response.QR_Image);
        } else if (twoFaVerificationTypeForResponse === 'disabled') {
            $('#section_2fa_form').removeClass('d-none');
            $('#section_2fa_verify').addClass('d-none');
            $('#twoFaVerificationType').val(response.twoFaVerificationTypeForResponse);
            showSwal(successText, successMessageText, 'success');
        } else {
            $('#section_2fa_form').addClass('d-none');
            $('#section_2fa_verify').removeClass('d-none');
            $('#type').html(twoFa_type);
        }
    }

    function handleError(response) {
        let message;

        if (response.two_step_verification_type === 'email') {
            message = '2-FA is already set by email!';
        } else if (response.two_step_verification_type === 'phone') {
            message = '2-FA is already set by phone!';
        } else if (response.two_step_verification_type === 'google_authenticator') {
            message = '2-FA is already set by Google Authenticator!';
        }

        showSwal(errorText, message, 'error');
    }

    function showSwal(title, text, type) {
        swal({
            title: title,
            text: text,
            type: type
        });
    }

    // 2fa verifying on submit
    $('#2fa_update').on('submit', function(event) {
        event.preventDefault();
        const is_demo = $('#is_demo').val();
        
        if (is_demo === 'true') {
            showSwal(errorText, demoCheckText, 'error');
        } else {
            var two_step_verification_type = $('#two_step_verification_type').val();
            
            if (two_step_verification_type === 'google_authenticator') {
                var url = twofaVerifyGoogleUrl;
            } else if (two_step_verification_type === 'disabled') {
                var url = twofaVerifyDisabledUrl;
            } else {
                var url = twofaVerifyCreateUrl;
            }
            
            $.post({
                url: url,
                dataType: 'json',
                data: {
                    '_token': token,
                    'two_step_verification_type': two_step_verification_type,
                },
                beforeSend: function() {
                    disableSubmitButton(submitBtnText);
                }
            }).done(function(response) {
                if (response.status) {
                    handleSuccess(response);
                } else {
                    handleError(response);
                }
            }).always(function() {
                enableSubmitButton('Submit');
            });
        }
    });

    $('#2fa_verify_form').on('submit', function(event) {
        event.preventDefault();

        const twoFaVerificationType = $("#twoFaVerificationType").val();
        const twoStepVerificationCode = $("#two_step_verification_code").val();
        const rememberMe = $("#remember_me").prop('checked');

        //Fingerprint2
        new Fingerprint2().get(function(result, components) {
            $.post({
                url: twofaVerifySettingsUrl,
                data: {
                    "_token": token,
                    'two_step_verification_code': twoStepVerificationCode,
                    'twoFaVerificationType': twoFaVerificationType,
                    'remember_me': rememberMe,
                    'browser_fingerprint': result,
                },
                beforeSend: function() {
                    $('#verify_code').prop('disabled', true);
                    $(".spinner").removeClass('d-none');
                    $('#verify_code_text').text(submitBtnText);
                }
            })
            .fail(function(xhr, status, error) {
                const data = xhr.responseJSON;
                if (!data.status || data.status == 404) {
                    showSwal(errorText, data.message, 'error');
                }
            })
            .done(function(data) {
                if (data.status) {
                    $('#section_2fa_form').removeClass('d-none');
                    $('#section_2fa_verify').addClass('d-none');
                    showSwal(successText, data.message, 'success');
                    $('#two_step_verification_code').val('');
                } else {
                    showSwal(errorText, data.message, 'error');
                }
                $('#verify_code').prop('disabled', false);
                $(".spinner").addClass('d-none');
                $('#verify_code_text').text('Verify');
            });
        });
    });

    $(document).on('click', '.completeVerification', function() {
        const google2fa_secret = $("#qrsecret").html();

        const $section2faForm = $('#section_2fa_form');
        const $section2faVerify = $('#section_2fa_verify');
        const $sectionGoogle2fa = $('#section_google2fa');
        const $section2faOtp = $('#section_2fa_otp');

        $.post({
            url: twofaVerifyCompleteGoogleUrl,
            data: {
                '_token': token,
                'google2fa_secret': google2fa_secret,
            },
            dataType: 'json',
            beforeSend: function() {
                $('#completeVerificationBtn').prop('disabled', true);
                $(".spinner").removeClass('d-none');
                $('#completeVerification_text').text(submitBtnText);
            }
        })
        .done(function(response) {
            if (response.status) {
                $section2faForm.addClass('d-none');
                $section2faVerify.addClass('d-none');
                $sectionGoogle2fa.addClass('d-none');
                $section2faOtp.removeClass('d-none');
            }
            $('#completeVerificationBtn').prop('disabled', false);
            $(".spinner").addClass('d-none');
            $('#completeVerification_text').text('Submit');
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", errorThrown);
        });
    });

    //google 2fa verifying OTP on submit
    $('#otp_form').on('submit', function(event) {
        event.preventDefault();

        var one_time_password = $("#one_time_password").val();
        var two_step_verification_type = $('#two_step_verification_type').val();
        var remember_otp = $("#remember_otp").is(':checked');

        new Fingerprint2().get(function(result, components) {
            $.post({
                url: twofaVerifyGoogleOtpUrl,
                dataType: 'json',
                data: {
                    "_token": token,
                    'one_time_password': one_time_password,
                    'two_step_verification_type': two_step_verification_type,
                    'remember_otp': remember_otp,
                    'browser_fingerprint': result,
                },
                beforeSend: function() {
                    $('#verify_otp').prop('disabled', true);
                    $(".spinner").removeClass('d-none');
                    $('#verify_otp_text').text(submitBtnText);
                }
            })
            .done(function(data) {
                if (data.status) {
                    $('#section_2fa_form').removeClass('d-none');
                    $('#section_2fa_verify').addClass('d-none');
                    $('#section_google2fa').addClass('d-none');
                    $('#section_2fa_otp').addClass('d-none');
                    showSwal(successText, data.message, 'success');
                    $('#one_time_password').val('');
                } else {
                    showSwal(errorText, oneTimeMessageText, 'error');
                }
                $("#verify_otp").attr("disabled", false);
                $(".spinner").addClass('d-none');
                $("#verify_otp_text").text('Proceed To Verification');
            });
        });
    });
}

if ($('.main-containt').find('#2faVerification').length) {
    //verifying on submit
    $('#2faVerificationForm').on('submit', function(event) {
        event.preventDefault();
        var two_step_verification_code = $("#two_step_verification_code").val();
        var remember_me = $("#remember_me").prop('checked');

        //Fingerprint2
        new Fingerprint2().get(function(result, components) {
            $.post({
                url: twofaVerifyUrl,
                dataType: 'json',
                data: {
                    "_token": token,
                    'two_step_verification_code': two_step_verification_code,
                    'remember_me': remember_me,
                    'browser_fingerprint': result,
                },
                beforeSend: function() {
                    $('#2faVerifyCode').prop('disabled', true);
                    $(".spinner").removeClass('d-none');
                    $('#2faVerifyCode_text').text(submitBtnText);
                }
            })
            .done(function(data) {
                if (!data.status || data.status == 404) {
                    $('#message').html(data.message);
                    $('#2faVerifyCode').prop('disabled', false);
                    $(".spinner").addClass('d-none');
                    $('#2faVerifyCode_text').text(btnText);
                } else {
                    $('#message').text('');
                    window.location.href = dashboardUrl;
                }
            });
        });
    });
}
if ($('.main-containt').find('#google2faVerifySection').length) {
    function showSwal(title, text, type) {
        swal({
            title: title,
            text: text,
            type: type
        });
    }

    //google 2fa on submit
    $(document).on('click', '.completeVerification', function(e) {
        e.preventDefault();
        $.post({
            url: twofaVerifyGoogleAuthenticatorUrl,
            dataType: "json",
            data: {
                '_token': token,
                'google2fa_secret': google2fa_secret,
            },
            beforeSend: function() {
                $('#completeVerificationBtn').prop('disabled', true);
                $(".spinner").removeClass('d-none');
                $('#completeVerification_text').text(submitBtnText);
            }
        })
        .done(function(response) {
            if (response.status) {
                $('#google2faVerify').addClass('d-none');
                $('#section_2fa_otp').removeClass('d-none');
            }
            $('#completeVerificationBtn').prop('disabled', false);
            $(".spinner").addClass('d-none');
            $('#completeVerification_text').text('Submit');
        });
    });

    //verifying OTP on submit
    $('#otp_form').submit(function(event) {
        event.preventDefault();
        var one_time_password = $("#one_time_password").val();
        var two_step_verification_type = $('#two_step_verification_type').val();
        var remember_otp = $("#remember_me").prop('checked');

        new Fingerprint2().get(function(result, components) {
            $.post({
                url: twofaVerifyGoogleOtpUrl,
                dataType: 'json',
                data: {
                    "_token": token,
                    'one_time_password': one_time_password,
                    'two_step_verification_type': two_step_verification_type,
                    'remember_otp': remember_otp,
                    'browser_fingerprint': result,
                },
                error: function(msg) {
                    if (msg.status != 200) {
                        showSwal(errorText, JSON.parse(msg.responseText).message, 'error');
                    }
                },
                beforeSend: function() {
                    $('#verify_otp').prop('disabled', true);
                    $(".spinner").removeClass('d-none');
                }
            })
            .done(function(data) {
                if (!data.status) {
                    showSwal(errorText, invalidCodeText, 'error');
                } else {
                    window.location.href = dashboardUrl;
                }
            });
        });
    });
}