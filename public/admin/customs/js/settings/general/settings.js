"use strict"

function updateSideBarCompanySmallLogo(file)
{
    if (file.name.match(/.(png|jpg|jpeg|gif|bmp)$/i)) {
        $.ajax({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:'POST',
            url: ADMIN_URL + "/settings/update-sidebar-company-logo",
            data: new FormData($('#general_settings_form')[0]),
            cache:false,
            contentType: false,
            processData: false,
        })
        .done(function(res)
        {
            $('.company-logo').attr('src', SITE_URL+'/public/uploads/logos/'+ res.filename);
        })
        .fail(function(error)
        {
        alert(JSON.parse(error.responseText).message);
        });
    } else {
        $('.company-logo').attr('src', SITE_URL+'/public/uploads/userPic/default-logo.jpg');
    }
}

$(window).on('load', function()
{
    $(".has_captcha, .login_via, .default_currency, .default_language").select2({});

    // Allowed wallets
    $(".allowed-wallets").select2({});
    let wallets = selectedAllowedWallets.split(',');
    $("#allowed-wallets").select2({
        placeholder: "Selected wallet will be created during registration.",
        allowClear: true
    }).select2().val(wallets).trigger("change");
});

// preview company logo on change
$(document).on('change','#logo', function()
{
    let orginalSource = '{{ url("public/uploads/userPic/default-logo.jpg") }}';
    let logo = $('#logo').attr('data-rel');

    if (logo != '') {
    readFileOnChange(this, $('#logo-preview'), orginalSource);
    $('.remove_img_preview_site_logo').remove();
    updateSideBarCompanySmallLogo(this.files[0]);
    } else {
    readFileOnChange(this, $('#logo-demo-preview'), orginalSource);
    updateSideBarCompanySmallLogo(this.files[0]);
    }
});

// preview company favicon on change
$(document).on('change','#favicon', function()
{
    let orginalSource = defaultImageUrl;
    let favicon = $('#favicon').attr('data-favicon');
    if (favicon != '') {
        readFileOnChange(this, $('#favicon-preview'), orginalSource);
        $('.remove_fav_preview').remove();
    }
    else {
        readFileOnChange(this, $('#favicon-demo-preview'), orginalSource);
    }
});

//Delete logo preview
$(document).on('click','.remove_img_preview_site_logo', function()
{
    let logo = $('#logo').attr('data-rel');
    
    if(logo) {
        $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "POST",
        url: ADMIN_URL + '/settings/delete-logo',
        data: {
            'logo' : logo,
        },
        dataType: 'json',
        success: function(reply)
        {
            if (reply.success == 1) {
            swal({
                title: "", 
                text: reply.message, 
                type: "success"
            }, function(){
                window.location.reload();
            });
            } else{
                alert(reply.message);
                location.reload();
            }
        }
        });
    }
});

//Delete favicon preview
$(document).on('click','.remove_fav_preview', function()
{
    let favicon = $('#favicon').attr('data-favicon');
    
    if(favicon) {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "POST",
        url: ADMIN_URL + "/settings/delete-favicon",
        data: {
        'favicon' : favicon,
        },
        dataType : 'json',
        success: function(reply)
        {
        if (reply.success == 1) {
            swal({
            title: "", 
            text: reply.message, 
            type: "success"
            }, function(){
            location.reload();
            });
        } else {
            alert(reply.message);
            window.location.reload();
        }
        }
    });
    }
});

$(document).on('change','#login_via', function()
{
    if ($(this).val() == 'email_or_phone' || $(this).val() == 'phone_only') {
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        url: ADMIN_URL + "/settings/check-sms-settings",
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
    })
    .done(function(response)
    {
        if (response.status == false) {
            $('#sms-error').addClass('error').html(response.message).css("font-weight", "bold");
            $('form').find("button[type='submit']").prop('disabled',true);
        } else if (response.status == true) {
            $('#sms-error').html('');
            $('form').find("button[type='submit']").prop('disabled',false);
        }
    });
    } else {
    $('#sms-error').html('');
    $('form').find("button[type='submit']").prop('disabled',false);
    }
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

$('#general_settings_form').validate({
    rules: {
        name: {
            required: true,
        },
        "photos[logo]": {
            extension: extensionsValidationRule,
        },
        "photos[favicon]": {
            extension: extensionsValidationRule
        },
    },
    messages: {
    "photos[logo]": {
        extension: extensionsValidationMessage
    },
    "photos[favicon]": {
        extension: extensionsValidationMessage
    }
    },
    submitHandler: function(form)
    {
        $("#general-settings-submit").attr("disabled", true).click(function (e) {
            e.preventDefault();
        });
        $(".fa-spin").removeClass("d-none");
        $("#general-settings-submit-text").text(submittingText);
        form.submit();
    }
});