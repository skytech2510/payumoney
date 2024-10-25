'use strict';

function changeProfile() {
    $('#upload').trigger('click');
}

if ($('.main-containt').find('#profileUpdate').length) {

    $(document).on('click', '#printQrCodeBtn', function (e) {
        e.preventDefault();
        $(this).prop('href', printQrCodeUrl);
        window.open(printQrCodeUrl, '_blank');
    });
    
    // update User's QrCode
    $('#updateQrCodeBtn').on('click', function (e) {
        e.preventDefault();
        let user_id = $('#user_id').val();

        $.post({
            url: updateQrCodeUrl,
            dataType: "json",
            data: {
                '_token': csrfToken,
                'user_id': user_id,
            },
            beforeSend: function () {
                swal(pleaseWaitText, loadingText, {
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    buttons: false,
                    timer: 2000,
                });
            },
        }).done(function (response) {
            if (response.status) {
                $('.qrCodeImage').attr("src", response.imgSource);
            }
        }).fail(function (error) {
            swal({
                title: errorText,
                text: JSON.parse(error.responseText).exception,
                icon: "error",
                closeOnClickOutside: false,
                closeOnEsc: false,
            });
        });
    });
    //start - ajax image upload
    $('#upload').change(function () {
        if ($(this).val() != '') {
            upload(this);
        }
    });
    
    function upload(img) {
        var form_data = new FormData();
        form_data.append('file', img.files[0]);
        form_data.append('_token', csrfToken);
        $('#loading').css('display', 'block');
        $.ajax({
            url: profileImageUploadUrl,
            data: form_data,
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            success: function (data) {
                if (data.fail) {
                    $('#file-error').show().addClass('error').html(data.errors.file);
                } else {
                    $('#file-error').hide();
                    $('#file_name').val(data);
                    location.reload();
                }
                $('#loading').css('display', 'none');
            },
            error: function (xhr, status, error) {
            }
        });
    }

    function phoneValidityCheck() {
        updatePhoneInfo()
        .then(() => {
            validateInternaltionalPhoneNumber()
            .then((status) => {
                if (status) {
                    checkDuplicatePhoneNumber();
                }
            });
        });
    }

    $("#phone").on("countrychange", function () {
        phoneValidityCheck();
    });
    
    $("#phone").on('blur', function () {
        phoneValidityCheck();
    });

    $('#defaultCurrencyForm').on('submit', function() {
        $(".spinner").removeClass('d-none');
        $("#defaultCurrencySubmitBtn").attr("disabled", true);
        $("#defaultCurrencySubmitBtnText").text(submitButtonText);
    });

    $('#profileResetPasswordForm').on('submit', function() {
        $(".spinner").removeClass('d-none');
        $("#profileResetPasswordSubmitBtn").attr("disabled", true);
        $("#profileResetPasswordSubmitBtnText").text(submitButtonText);
    });

    $('#profileUpdateForm').on('submit', function() {
        $(".spinner").removeClass('d-none');
        $("#profileUpdateSubmitBtn").attr("disabled", true);
        $("#profileUpdateSubmitBtnText").text(submitButtonText);
    });
}