'use strict';

let hasPhoneError = false;
let hasEmailError = false;

function enableDisableButton()
{
    if (!hasPhoneError && !hasEmailError) {
        $('form').find("button[type='submit']").prop('disabled',false);
    } else {
        $('form').find("button[type='submit']").prop('disabled',true);
    }
}

$('#personal-info-form').on('submit', function() {
    $("#registrationSubmitBtn").attr("disabled", true);
    $("#rightAngle").addClass('d-none');
    $(".spinner").removeClass('d-none');
    $("#registrationSubmitBtnTxt").text(signingUpText);
});

$("#phone").intlTelInput({
    separateDialCode: true,
    nationalMode: true,
    preferredCountries: [countryShortCode],
    autoPlaceholder: "polite",
    placeholderNumberType: "MOBILE",
    utilsScript: utilsJsScript
});

function updatePhoneInfo()
{
    let promiseObj = new Promise(function(resolve, reject)
    {
        $('#defaultCountry').val($('#phone').intlTelInput('getSelectedCountryData').iso2);
        $('#carrierCode').val($('#phone').intlTelInput('getSelectedCountryData').dialCode);

        if ($('#phone').val != '') {
            $("#formattedPhone").val($('#phone').intlTelInput("getNumber").replace(/-|\s/g,""));
        }
        resolve();
    });  
    return promiseObj;  
}

function checkDuplicatePhoneNumber()
{
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: 'POST',
        url: SITE_URL + '/register/duplicate-phone-number-check',
        dataType: 'json',
        cache: false,
        data: {
            'phone': $.trim($('#phone').val()),
            'carrierCode': $.trim($('#phone').intlTelInput('getSelectedCountryData').dialCode),
        }
    })
    .done(function(response)
    {
        if (response.status) {
            $('#duplicate-phone-error').show().addClass('error').html(response.fail);
            hasPhoneError = true;
            enableDisableButton();
        } else {
            $('#duplicate-phone-error').html('');
            hasPhoneError = false;
            enableDisableButton();
        }
    });
}

function validateInternationalPhoneNumber()
{
    let promiseObj = new Promise(function(resolve, reject)
    {
        let resolveStatus = false;
        if ($.trim($('#phone').val()) !== '') {
            if (!$('#phone').intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($('#phone').val()))) {
                $('#duplicate-phone-error').html('');
                $('#tel-error').addClass('error').html(validPhoneNumberText);
                hasPhoneError = true;
                enableDisableButton();
            } else {
                resolveStatus = true;
                $('#tel-error').html('');
                hasPhoneError = false;
                enableDisableButton();
            }
        } else {
            $('#tel-error').html('');
            hasPhoneError = false;
            enableDisableButton();
        }
        resolve(resolveStatus);
    });  
    return promiseObj;  
}

function phoneValidityCheck()
{
    updatePhoneInfo()
    .then(() => 
    {
        validateInternationalPhoneNumber()
        .then((status) => 
        {
            if (status) {
                checkDuplicatePhoneNumber();
            }
        });
    });
}

$("#phone").on("countrychange", function()
{
    phoneValidityCheck();
});

$("#phone").on('blur', function()
{
    phoneValidityCheck();
});

$(document).ready(function()
{
    $("#email").on('input', function()
    {
        var email = $('#email').val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: "POST",
            url: SITE_URL + '/user-registration-check-email',
            dataType: "json",
            data: {
                'email': email,
            }
        })
        .done(function(response)
        {
            if (response.status) {
                emptyEmail();
                if (validateEmail(email)) {
                    $('#email_error').addClass('custom-error').html(response.fail);
                    $('#email_ok').html('');
                    hasEmailError = true;
                    enableDisableButton();
                } else {
                    $('#email_error').html('');
                }
            } else {
                emptyEmail();
                if (validateEmail(email)) {
                    $('#email_error').html('');
                } else {
                    $('#email_ok').html('');
                }
                hasEmailError = false;
                enableDisableButton();
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
                if( email.length === 0 ) {
                    $('#email_error').html('');
                    $('#email_ok').html('');
                }
            }
        });
    });
});
