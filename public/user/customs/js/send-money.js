"use strict";

function restrictNumberToPrefdecimalOnInput(e)
{
    let type = $('#wallet').find(':selected').attr('data-type');
    restrictNumberToPrefdecimal(e, type);
}

function determineDecimalPoint() {
    let currencyType = $('#wallet').find(':selected').attr('data-type');

    if (currencyType == 'crypto') {
        $('#formattedFeesPercentage, #formattedFeesFixed, #formattedTotalFees').text(CRYPTODP);
        $("#amount").attr('placeholder', CRYPTODP);

    } else if (currencyType == 'fiat') {
        $('#formattedFeesPercentage, #formattedFeesFixed, #formattedTotalFees').text(FIATDP);
        $("#amount").attr('placeholder', FIATDP);
    }
}

if ($('.main-containt').find('#sendMoneyCreate').length) {

    let recipientErrorFlag = false;
    let amountErrorFlag = false;

    function enableDisableButton()
    {
        if (!recipientErrorFlag && !amountErrorFlag) {
            $("#sendMoneyCreateSubmitBtn").attr("disabled", false);
        } else {
            $("#sendMoneyCreateSubmitBtn").attr("disabled", true);
        }
    }

    function getStringAfterPlusSymbol(str)
    {
        return str.split('+')[1];
    }
    
    // Email or Phone validity check
    function emailPhoneValidationCheck(emailOrPhone, sendOrRequestSubmitButton)
    {
        if (emailOrPhone && emailOrPhone.length != 0) {
            if (processedBy == "email") {
                if (validateEmail(emailOrPhone)) {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                } else {
                    $('.receiverError').html(validEmailMessage).insertAfter($('#receiver'));
                    recipientErrorFlag = true;
                    enableDisableButton();
                }
            } else if (processedBy == "phone") {
                if (emailOrPhone.charAt(0) != "+" || !$.isNumeric(getStringAfterPlusSymbol(emailOrPhone))) {
                    $('.receiverError').html(validPhoneMessage).insertAfter($('#receiver'));
                    recipientErrorFlag = true;
                    enableDisableButton();
                } else {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                }
            } else if (processedBy == "email_or_phone") {
                if (emailOrPhone.charAt(0) != "+" || !$.isNumeric(getStringAfterPlusSymbol(emailOrPhone))) {
                    if (validateEmail(emailOrPhone)) {
                        $('.receiverError').html('');
                        recipientErrorFlag = false;
                        enableDisableButton();
                    } else {
                        $('.receiverError').html(validEmailOrPhoneMessage).insertAfter($('#receiver'));
                        recipientErrorFlag = true;
                        enableDisableButton();
                    }
                } else {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                }
            }
        } else {
            $('.receiverError').html('');
            recipientErrorFlag = false;
            enableDisableButton();
        }
    }

    // Money receiver status check [Active, Suspended, Inactive]
    function receiverAccountStatusCheck()
    {
        let receiver = $('#receiver').val().trim();
        if (receiver != '') {
            $.ajax({
                method: "POST",
                url: receiverStatusUrl,
                dataType: "json",
                data: {
                    '_token': token,
                    'receiver': receiver
                }
            })
            .done(function (response)
            {
                if (response.status || response.status == 404) {
                    $('.receiverError').html(response.message).insertAfter($('#receiver'));
                    recipientErrorFlag = true;
                    enableDisableButton();
                } else {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                }
            });
        } else {
            $('.receiverError').html('');
        }
    }


    function checkAmountLimitAndFeesLimit()
    {
        let amount = $('#amount').val();
        let wallet_id = $('#wallet').val();

        if (amount.length === 0) {
            $('.amount-limit-error').hide();
            determineDecimalPoint();
        } else {
            $('.amount-limit-error').show('d-none');
        
            if (amount > 0 && wallet_id) {
                
                $.ajax({
                    method: "POST",
                    url: checkAmountLimitUrl,
                    dataType: "json",
                    data: {
                        "_token": token,
                        'amount': amount,
                        'wallet_id': wallet_id,
                        'transaction_type_id': transactionTypeId
                    }
                })
                .done(function (response) {
                    receiverAccountStatusCheck();
                    if (response.success.status == 200) {
                        $("#feesFixed").val(response.success.feesFixed);
                        $("#feesPercentage").val(response.success.feesPercentage);
                        $("#totalFees").val(response.success.totalFees);
                        $('#formattedFeesFixed').html(response.success.fFeesHtml);
                        $('#formattedFeesPercentage').html(response.success.pFeesHtml);
                        $('#formattedTotalFees').html(response.success.totalFeesHtml);
                        $('.amount-limit-error').text('');
                        amountErrorFlag = false;
                        enableDisableButton();

                        // Not have enough balance
                        if(response.success.totalAmount > response.success.balance) {
                            $('.amount-limit-error').text(lowBalanceText).insertAfter($('#amount'));
                            amountErrorFlag = true;
                            enableDisableButton();
                        }
                    } else {
                        $('.amount-limit-error').text(response.success.message).insertAfter($('#amount'));
                        amountErrorFlag = true;
                        enableDisableButton();
                    }
                });
            }
        }
    }

    // Event - onload validations
    $(window).on('load', function () {
        let emailOrPhone = $('#receiver').val().trim();
        if (emailOrPhone != null) {
            emailPhoneValidationCheck(emailOrPhone, $("#sendMoneyCreateSubmitBtn"));
        }
        checkAmountLimitAndFeesLimit();
    });

    // Event - recipent input email/phone event
    $(document).on('input', "#receiver", $.debounce(700, function() {
        let emailOrPhone = $('#receiver').val().trim();
        if (emailOrPhone != null) {
            emailPhoneValidationCheck(emailOrPhone, $("#sendMoneyCreateSubmitBtn"));
            receiverAccountStatusCheck();
        }
    }));

    // Event - amount input limit  event
    $('#amount').on('input', $.debounce(1000, function () {
        checkAmountLimitAndFeesLimit();
    }));

    var lastCurrencyType, currenctCurrencyType;

    $('select').change(function(){
        lastCurrencyType = $(this).attr('data-old') !== typeof undefined? $(this).attr('data-old') : "";
        currenctCurrencyType = $("option:selected",this).data('type');
        $(this).attr('data-old',currenctCurrencyType)
    }).change();

    // Code for Fees Limit check
    $('#wallet').on('change', function () {
        
        if (lastCurrencyType !== currenctCurrencyType) {
            $('#amount').val('');
        }
        $('.amount-limit-error').text('');
        determineDecimalPoint();
        checkAmountLimitAndFeesLimit();
    });

    $('#sendMoneyCreateForm').on('submit', function () {
        setTimeout(function()
        {
            $("#sendMoneyCreateSubmitBtn").removeAttr("disabled");
            $(".spinner").addClass('d-done');
            $("#sendMoneyCreateSubmitBtnText").html('Proceed');
            $("#sendMoneySvgIcon").removeClass('d-none');

        },2000);

        $(".spinner").removeClass('d-none');
        $("#sendMoneySvgIcon").addClass('d-none');
        $("#sendMoneyCreateSubmitBtn").attr("disabled", true);
        $("#sendMoneyCreateSubmitBtnText").text(submitBtnText);
    });
}

if ($('.main-containt').find('#sendMoneyConfirm').length) {
    $('#sendMoneyConfirmForm').on('submit', function ()
    {
        $('#sendMoneyConfirmBtn').attr("disabled", true);
        $('#sendMoneyBackButton').removeAttr('href');
    	$(".spinner").removeClass('d-none')
    	$('#sendMoneyConfirmBtnText').text(confirmBtnText);
    });
}