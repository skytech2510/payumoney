"use strict";

function restrictNumberToPrefdecimalOnInput(e)
{
    let currencyType = $('select#currency_id option:selected').data('type');
    restrictNumberToPrefdecimal(e, currencyType);
}

function determineDecimalPoint()
{
    let currencyType = $('select#currency_id').find(':selected').data('type')

    if (currencyType == 'crypto') {
        $("#amount").attr('placeholder', CRYPTODP);
    } else if (currencyType == 'fiat') {
        $("#amount").attr('placeholder', FIATDP);
    }
}

if ($('.main-containt').find('#requestMoneyCreate').length) {

    let recipientErrorFlag = false;
    let amountErrorFlag = false;

    function enableDisableButton()
    {
        if (!recipientErrorFlag && !amountErrorFlag) {
            $("#requestMoneyCreateSubmitBtn").attr("disabled", false);
        } else {
            $("#requestMoneyCreateSubmitBtn").attr("disabled", true);
        }
    }

    function requestMoneyGetStringAfterPlusSymbol(str) {
        return str.split('+')[1];
    }

    // Email or Phone validity check
    function emailPhoneValidationCheck(emailOrPhone)
    {
        if (emailOrPhone && emailOrPhone.length != 0) {
            if (processedBy == "email") {
                if (validateEmail(emailOrPhone)) {
                    $('.requestEmailOrPhoneError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                } else {
                    $('.requestEmailOrPhoneError').html(validEmailMessage).insertAfter($('#requestCreatorEmail'));
                    recipientErrorFlag = true;
                    enableDisableButton();
                }
            } else if (processedBy == "phone") {
                if (emailOrPhone.charAt(0) != "+" || !$.isNumeric(requestMoneyGetStringAfterPlusSymbol(emailOrPhone))) {
                    $('.requestEmailOrPhoneError').html(validPhoneMessage).insertAfter($('#requestCreatorEmail'));
                    recipientErrorFlag = true;
                    enableDisableButton();
                } else {
                    $('.requestEmailOrPhoneError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                }
            } else if (processedBy == "email_or_phone") {
                if (emailOrPhone.charAt(0) != "+" || !$.isNumeric(requestMoneyGetStringAfterPlusSymbol(emailOrPhone))) {
                    if (validateEmail(emailOrPhone)) {
                        $('.requestEmailOrPhoneError').html('');
                        recipientErrorFlag = false;
                        enableDisableButton();
                    } else {
                        $('.requestEmailOrPhoneError').html(validEmailOrPhoneMessage).insertAfter($('#requestCreatorEmail'));
                        recipientErrorFlag = true;
                        enableDisableButton();
                    }
                } else {
                    $('.requestEmailOrPhoneError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                }
            }
        } else {
            $('.requestEmailOrPhoneError').html('');
            recipientErrorFlag = false;
            enableDisableButton();
        }
    }

    function requestReceiverAccountStatusCheck(emailOrPhone) {
        $.post({
            url: checkEmailOrPhoneUrl,
            dataType: 'json',
            data: {
                '_token': csrfToken,
                'emailOrPhone': emailOrPhone,
            }
        }).done(function(response) {
            if (response.status === true || response.status === 404) {
                $('.requestEmailOrPhoneError').html(response.message).insertAfter($('#requestCreatorEmail'));
                recipientErrorFlag = true;
                enableDisableButton();
            } else {
                $('.requestEmailOrPhoneError').html('');
                recipientErrorFlag = true;
                enableDisableButton();
            }
        });
    }

    // Event - onload validations
    $(window).on('load', function() {
        let emailOrPhone = $('#requestCreatorEmail').val().trim();
        if (emailOrPhone != null) {
            emailPhoneValidationCheck(emailOrPhone);
        }
    });

    // Event - request receiver input email/phone event
    $(document).on('input',"#requestCreatorEmail",function(e) {
        let emailOrPhone = $('#requestCreatorEmail').val().trim();
        if (emailOrPhone) {
            emailPhoneValidationCheck(emailOrPhone);
            requestReceiverAccountStatusCheck(emailOrPhone);
        }
    });

    var lastCurrencyType, currentCurrencyType;

    $('select').change(function(){
        lastCurrencyType = $(this).attr('data-old') !== typeof undefined? $(this).attr('data-old') : "";
        currentCurrencyType = $("option:selected",this).data('type');
        $(this).attr('data-old',currentCurrencyType)
    }).change();

    // Event - currency id  or wallet onChange event
    $(document).on('change', '#currency_id', function() {
        if (lastCurrencyType !== currentCurrencyType) {
            $('#amount').val('');
        }
        determineDecimalPoint();
    });

    $('#requestMoneyCreateForm').on('submit', function () {

        setTimeout(function()
        {
            $("#requestMoneyCreateSubmitBtn").removeAttr("disabled");
            $(".spinner").addClass('d-done');
            $("#requestMoneyCreateSubmitBtnText").html('Proceed');
            $("#requestMoneySvgIcon").removeClass('d-none');

        },2000);

        $(".spinner").removeClass('d-none');
        $("#requestMoneySvgIcon").addClass('d-none');
        $("#requestMoneyCreateSubmitBtn").attr("disabled", true);
        $("#requestMoneyCreateSubmitBtnText").text(submitBtnText);
    });
}

if ($('.main-containt').find('#requestMoneyConfirm').length) {
    $('#requestMoneyConfirmForm').on('submit', function ()
    {
        $('#requestMoneyConfirmBtn').attr("disabled", true);
        $('#requestMoneyBackButton').removeAttr('href');
    	$(".spinner").removeClass('d-none')
    	$('#requestMoneyConfirmBtnText').text(confirmBtnText);
    });
}