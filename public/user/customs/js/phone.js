var hasPhoneError = false;
var hasEmailError = false;

function enableDisableButton() {
    if (!hasPhoneError && !hasEmailError) {
        $('form').find("button[type='submit']").prop('disabled', false);
    } else {
        $('form').find("button[type='submit']").prop('disabled', true);
    }
}

$("#phone").intlTelInput({
    separateDialCode: true,
    nationalMode: true,
    preferredCountries: [countryShortCode],
    autoPlaceholder: "polite",
    placeholderNumberType: "MOBILE",
    utilsScript: utilsScriptLoadingPath
});

if (formattedPhoneNumber !== null && defaultCountry !== null && carrierCode !== null) {
    $("#phone").intlTelInput("setNumber", formattedPhoneNumber);
    $('#defaultCountry').val(defaultCountry);
    $('#carrierCode').val(carrierCode);
    $('#formattedPhone').val(formattedPhoneNumber);
}

function updatePhoneInfo() {
    let promiseObj = new Promise(function (resolve, reject) {
        hasPhoneError = true;
        enableDisableButton();
        $('#defaultCountry').val($('#phone').intlTelInput('getSelectedCountryData').iso2);
        $('#carrierCode').val($('#phone').intlTelInput('getSelectedCountryData').dialCode);

        if ($('#phone').val != '') {
            $("#formattedPhone").val($('#phone').intlTelInput("getNumber").replace(/-|\s/g, ""));
        }
        resolve();
    });
    hasPhoneError = false;
    enableDisableButton();
    return promiseObj;
}

function checkDuplicatePhoneNumber() {
    $.post({
        url: duplicatePhoneCheckUrl,
        dataType: 'json',
        data: {
            '_token': csrfToken,
            'phone': $.trim($('#phone').val()),
            'carrierCode': $.trim($('#phone').intlTelInput('getSelectedCountryData').dialCode),
            'id': userId,
        }
    })
    .done(function (response) {
        if (response.status) {
            $('#phone-error').show().addClass('error').html(response.fail);
            hasPhoneError = true;
            enableDisableButton();
        } else {
            $('#phone-error').html('');
            hasPhoneError = false;
            enableDisableButton();
        }
    });
}

function validateInternaltionalPhoneNumber() {
    let promiseObj = new Promise(function (resolve, reject) {
        let resolveStatus = false;
        if ($.trim($('#phone').val()) !== '') {
            if (!$('#phone').intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($('#phone').val()))) {
                $('#phone-error').html('');
                $('#tel-error').addClass('error').html(validPhoneNumberErrorText);
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