'use strict';

if ($('.content-wrapper').find('#user-create').length) {
    $(function () {
        $(".select2").select2({});
    });

    var hasPhoneError = false;
    var hasEmailError = false;
    function enableDisableButton()
    {
        if (!hasPhoneError && !hasEmailError) {
            $('form').find("button[type='submit']").prop('disabled',false);
        } else {
            $('form').find("button[type='submit']").prop('disabled',true);
        }
    }

    $(document).on('submit', '#user_form', function() {
        $('#users_creat').attr('disabled', true);
        $('#users_cancel').attr('disabled',true);
        $('.fa-spin').removeClass('d-none');
        $('#users_create_text').text(creatingText);
    });

    $("#phone").intlTelInput({
        separateDialCode: true,
        nationalMode: true,
        preferredCountries: [countryShortCode],
        autoPlaceholder: "polite",
        placeholderNumberType: "MOBILE",
        utilsScript: utilsScriptLoadingPath
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
            url: ADMIN_URL + '/duplicate-phone-number-check',
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

    function validateInternaltionalPhoneNumber()
    {
        let promiseObj = new Promise(function(resolve, reject)
        {
            let resolveStatus = false;
            if ($.trim($('#phone').val()) !== '') {
                if (!$('#phone').intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($('#phone').val()))) {
                    $('#duplicate-phone-error').html('');
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

    function phoneValidityCheck()
    {
        updatePhoneInfo()
        .then(() => 
        {
            validateInternaltionalPhoneNumber()
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
        $("#email").on('blur', function(e) {
            var email = $('#email').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: ADMIN_URL + "/email_check",
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
                        $('#email_error').addClass('error').html(response.fail).css("font-weight", "bold");
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
}
// user edit
if ($('.content-wrapper').find('#user-edit').length) {
    var hasPhoneError = false;
    var hasEmailError = false;

    $(function () {
        $(".select2").select2({});
    });
    
    function enableDisableButton()
    {
        if (!hasPhoneError && !hasEmailError) {
            $('form').find("button[type='submit']").prop('disabled',false);
        } else {
            $('form').find("button[type='submit']").prop('disabled',true);
        }
    }

    $("#phone").intlTelInput({
        separateDialCode: true,
        nationalMode: true,
        preferredCountries: ["us"],
        autoPlaceholder: "polite",
        placeholderNumberType: "MOBILE",
        formatOnDisplay: false,
        utilsScript: utilsScriptLoadingPath
    })
    
    if (formattedPhoneNumber !== null && carrierCode !== null && defaultCountry !== null) {
        $("#phone").intlTelInput("setNumber", formattedPhoneNumber);
        $('#user_defaultCountry').val(defaultCountry);
        $('#user_carrierCode').val(carrierCode);
    }

    function updatePhoneInfo()
    {
        let promiseObj = new Promise(function(resolve, reject)
        {
            $('#user_defaultCountry').val($('#phone').intlTelInput('getSelectedCountryData').iso2);
            $('#user_carrierCode').val($('#phone').intlTelInput('getSelectedCountryData').dialCode);

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
            url: ADMIN_URL + '/duplicate-phone-number-check',
            dataType: 'json',
            cache: false,
            data: {
                'phone': $.trim($('#phone').val()),
                'carrierCode': $.trim($('#phone').intlTelInput('getSelectedCountryData').dialCode),
                'id': $('#id').val(),
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

    function validateInternaltionalPhoneNumber()
    {
        let promiseObj = new Promise(function(resolve, reject)
        {
            let resolveStatus = false;
            if ($.trim($('#phone').val()) !== '') {
                if (!$('#phone').intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($('#phone').val()))) {
                    $('#duplicate-phone-error').html('');
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

    function phoneValidityCheck()
    {
        updatePhoneInfo()
        .then(() => 
        {
            validateInternaltionalPhoneNumber()
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

    // Validate email via Ajax
    $(document).ready(function()
    {
        $("#email").on('input', function(e) {
            var email = $(this).val();
            var id = $('#id').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: ADMIN_URL + "/email_check",
                dataType: "json",
                data: {
                    'email': email,
                    'user_id': id,
                }
            })
            .done(function(response)
            {
                emptyEmail(email);
                if (response.status) {
                    if (validateEmail(email)) {
                        $('#emailError').addClass('error').html(response.fail).css("font-weight", "bold");
                        $('#email-ok').html('');
                        hasEmailError = true;
                        enableDisableButton();
                    } else {
                        $('#emailError').html('');
                    }
                } else {
                    hasEmailError = false;
                    enableDisableButton();
                    if (validateEmail(email)) {
                        $('#emailError').html('');
                    } else {
                        $('#email-ok').html('');
                    }
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
                function emptyEmail(email) {
                    if( email.length === 0 ) {
                        $('#emailError').html('');
                        $('#email-ok').html('');
                    }
                }
            });
        });
    });

    // show warnings on user status change
    $(document).on('change', '#status', function() {
        let status = $('#status').val();
        if (status == 'Inactive') {
            $('#user-status').text(inactiveWarning);
        } else if (status == 'Suspended') {
            $('#user-status').text(suspendWarning);
        } else {
            $('#user-status').text('');
        }
    });

    $(document).on('submit', '#user_form', function() {
        $('#users_edit').attr('disabled', true);
        $('#users_cancel').attr('disabled',true);
        $('.fa-spin').removeClass('d-none');
        $('#users_edit_text').text(updatingText);
    });
}