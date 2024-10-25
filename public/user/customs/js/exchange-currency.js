"use strict";

function restrictNumberToPrefdecimalOnInput(e)
{
    let currencyType = $('select#fromCurrencyWallet option:selected').data('type');
    restrictNumberToPrefdecimal(e, currencyType);
}

function determineDecimalPoint() {
        
    var currencyType = $('select#fromCurrencyWallet').find(':selected').data('type')

    if (currencyType == 'crypto') {
        $('#formattedFeesPercentage, #formattedFeesFixed, #formattedTotalFees').text(CRYPTODP);
        $("#amount").attr('placeholder', CRYPTODP);
        $("#convertedAmount").attr('placeholder', CRYPTODP);

    } else if (currencyType == 'fiat') {
        
        $('#formattedFeesPercentage, #formattedFeesFixed, #formattedTotalFees').text(FIATDP);
        $("#amount").attr('placeholder', FIATDP);
        $("#convertedAmount").attr('placeholder', FIATDP);
    }
}

function hideExchangeRateGetAmountDiv()
{
    $('#exchangeRateDiv').addClass('d-none');
}

function disableSubmitBtn(btnId, btnTextId, submitButtonText) {
    $(btnId).attr("disabled", true);
    $(btnTextId).text(submitButtonText);
}

if ($('.main-containt').find('#exchangeMoneyCreate').length) {

    function checkAmountLimitAndFeesLimit()
    {
        let amount = $('#amount').val().trim();
        let fromCurrencyId = $('#fromCurrencyWallet').val();

        if (amount > 0 && fromCurrencyId) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    method: "POST",
                    url: amountLimitCheckUrl,
                    dataType: "json",
                    data: {
                        "_token": csrfToken,
                        'amount': amount,
                        'currency_id': fromCurrencyId,
                        'transaction_type_id': transactionTypeId
                    },
                })
                .done(function (response)
                {
                    if (response.success.status == 200) {
                        $("#feesPercentage").val(response.success.feesPercentage);
                        $("#feesFixed").val(response.success.feesFixed);
                        $("#totalFees").val(response.success.totalFees);
                        $('#formattedFeesPercentage').html(response.success.pFeesHtml);
                        $('#formattedFeesFixed').html(response.success.fFeesHtml);
                        $('#formattedTotalFees').html(response.success.totalFeesHtml);

                        //checking wallet balance
                        if ((response.success.totalAmount > response.success.balance) || amount == '') {
                            $('#amountLimitError').html(lowBalanceText);
                            $('#exchangeMoneyCreateSubmitBtn').attr('disabled', true);
                            $('#convertedAmount').val('');
                            hideExchangeRateGetAmountDiv();
                            resolve(false);
                        } else {
                            $('#amountLimitError').html('');
                            $('#exchangeMoneyCreateSubmitBtn').removeAttr('disabled');
                            resolve(true);
                        }
                    } else {
                        $('#amountLimitError').text(response.success.message);
                        $('#exchangeMoneyCreateSubmitBtn').attr('disabled', true);
                        $('#convertedAmount').val('');
                        hideExchangeRateGetAmountDiv();
                        resolve(false);
                    }
                });
            });
        }
    }

    function getConvertedCurrencies(fromCurrencyId) 
    {
        return new Promise(function(resolve, reject) {
            $.post({
                url: currencyListExceptSelectedUrl,
                dataType: "json",
                data: {
                    "_token": csrfToken,
                    'currency_id': fromCurrencyId,
                }
            }).done(function (response) {
                let options = '';
                $.each(response.currencies, function (key, value) {
                    options += `<option value="${value.id}" ${value.id == convertedCurrency ? 'selected' : ''}   data-toWalletCode="${value.code}">${value.code}</option>`;
                });
                $('#toCurrencyWallet').html(options);
                resolve();
            });
        });
        
    }


    function getBalanceOfSelectedCurrencyWallet(currencyId) 
    {
        return new Promise(function(resolve, reject){
            $.post({
                url: walletBalanceUrl,
                dataType: "json",
                data: {
                    "_token": csrfToken,
                    'currency_id': currencyId,
                }
            }).done(function (response) {
                resolve(response);
            });
        });
        
    }

    function getAmountFromGive() {
        let amount = $('#amount').val().trim();
        let fromWalletCurrencyId = $('#fromCurrencyWallet').val();
        let toWalletCurrencyId = $('#toCurrencyWallet').val();
        let fromWalletCurrencyCode = $('#fromCurrencyWallet').find(':selected').text();
        let toWalletCurrencyCode = $('#toCurrencyWallet').find(':selected').text();
        let fromWalletCurrencyIdLstorage = $('#fromCurrencyWallet').find(':selected').val();
        let toWalletCurrencyIdLstorage = $('#toCurrencyWallet').find(':selected').val();

        if (toWalletCurrencyId && fromWalletCurrencyCode && $.isNumeric(amount)) {
            $.post({
                url: exchangeRateUrl,
                dataType: "json",
                data: {
                    "_token": csrfToken,
                    'amount': amount,
                    'fromWallet': fromWalletCurrencyId,
                    'toWallet': toWalletCurrencyId,
                    'fromWalletCode': fromWalletCurrencyCode,
                    'toWalletCode': toWalletCurrencyCode,
                },
                success: function (response) {
                    if (response.status) {
                        if ((amount > 0 || amount != '') && fromWalletCurrencyId != '' && toWalletCurrencyId != '') {
                            $('#exchangeRateDiv').removeClass('d-none');
                            $('#exchangeRateFromWalletCode').text(fromWalletCurrencyCode);
                            $('#convertedAmount').val(response.getAmountMoneyFormatHtml);
                            $('#exchangeRate').text(response.destinationCurrencyRate);
                            $('#exchangeRateToWalletCode').text(response.destinationCurrencyCode);
                            $('#finalAmount').val(amount * response.destinationCurrencyRate);
                            //setting to wallet value to local storage for window load
                            localStorage.setItem('fromWalletCurrencyIdLstorage', fromWalletCurrencyIdLstorage);
                            localStorage.setItem('toWalletCurrencyIdLstorage', toWalletCurrencyIdLstorage);
                            localStorage.setItem('amountLstorage', amount);
                        }
                    } else {
                        determineDecimalPoint();
                        hideExchangeRateGetAmountDiv();
                    }
                },
                error: function (error) {
                    Swal.fire(
                        failedText,
                        JSON.parse(error.responseText).message,
                        'error'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                },
            });
        } else {
            $('#exchangeRateDiv').addClass('d-none');
            $('#convertedAmount').text('');
        }
    }

    $(window).on('load', function () {

        determineDecimalPoint();
        hideExchangeRateGetAmountDiv();

        let fromCurrencyWalletId = $('#fromCurrencyWallet').val();

        if (fromCurrencyWalletId != '') {
            getBalanceOfSelectedCurrencyWallet(fromCurrencyWalletId)
            .then((object) => 
            {
                $('#fromCurrencyWalletBalanceDiv').removeClass('d-none');
                $('#fromWalletCurrencyBalance').text(object.balance);
                $('#fromWalletCurrencyCode').text(object.currencyCode);
                
                
            }).then(() => {
                getConvertedCurrencies(fromCurrencyWalletId)
                .then(() => {
                    let toCurrencyWalletId = $('#toCurrencyWallet').val();
                    if (toCurrencyWalletId != '') {
                        getBalanceOfSelectedCurrencyWallet(toCurrencyWalletId)
                        .then((object) => 
                        {
                            if (object.status) {
                                $('#toCurrencyWalletBalanceDiv').removeClass('d-none');
                                $('#toWalletCurrencyBalance').text(object.balance);
                                $('#toWalletCurrencyCode').text(object.currencyCode);
                            } else {
                                $('#toCurrencyWalletBalanceDiv').addClass('d-none');
                            }
                        });
                    }
                });
            }).then(() => {
                let amount = $('#amount').val().trim();
                let toCurrencyWalletId = $('#toCurrencyWallet').val();
                if (($.isNumeric(amount) && amount > 0) && fromCurrencyWalletId != '' ) {
                    checkAmountLimitAndFeesLimit()
                    .then((status) => {
                        if (status && toCurrencyWalletId != '') {
                            getAmountFromGive();
                        }
                    });
                } 
            });
        }
    });

    // Event: from currency wallet change
    $('#fromCurrencyWallet').on('change', function ()
    {

        let fromCurrencyWalletId = $(this).val();

        if (fromCurrencyWalletId != '') {
            getBalanceOfSelectedCurrencyWallet(fromCurrencyWalletId)
            .then((object) => 
            {
                $('#fromCurrencyWalletBalanceDiv').removeClass('d-none');
                $('#fromWalletCurrencyBalance').text(object.balance);
                $('#fromWalletCurrencyCode').text(object.currencyCode);
                
            }).then(() => {
                getConvertedCurrencies(fromCurrencyWalletId)
                .then(() => {

                    let toCurrencyWalletId = $('#toCurrencyWallet').val();

                    if (toCurrencyWalletId != '') {
                        getBalanceOfSelectedCurrencyWallet(toCurrencyWalletId)
                        .then((object) => 
                        {
                            if (object.status) {
                                $('#toCurrencyWalletBalanceDiv').removeClass('d-none');
                                $('#toWalletCurrencyBalance').text(object.balance);
                                $('#toWalletCurrencyCode').text(object.currencyCode);
                            } else {
                                $('#toCurrencyWalletBalanceDiv').addClass('d-none');
                            }
                        });
                    }
                });
            });
        }

        let amount = $('#amount').val().trim();

        if (($.isNumeric(amount) && amount > 0) && fromCurrencyWalletId != '' ) {
            checkAmountLimitAndFeesLimit()
            .then((status) => {
                let toCurrencyWalletId = $('#toCurrencyWallet').val();
                if (status && toCurrencyWalletId != '') {
                    getAmountFromGive();
                }
            });
        } 

    });

    // Event: converted currency wallet change
    $('#toCurrencyWallet').on('change', function ()
    {
        let toCurrencyWalletId = $(this).val();
        if (toCurrencyWalletId != '') {
            getBalanceOfSelectedCurrencyWallet(toCurrencyWalletId)
            .then((object) => 
            {
                if (object.status) {
                    $('#toCurrencyWalletBalanceDiv').removeClass('d-none');
                    $('#toWalletCurrencyBalance').text(object.balance);
                    $('#toWalletCurrencyCode').text(object.currencyCode);
                } else {
                    $('#toCurrencyWalletBalanceDiv').addClass('d-none');
                }
            });
        }

        let amount = $('#amount').val().trim();
        let fromCurrencyWalletId = $('#fromCurrencyWallet').val();

        if (($.isNumeric(amount) && amount > 0) && fromCurrencyWalletId != '' ) {
            checkAmountLimitAndFeesLimit()
            .then((status) => {
                if (status && toCurrencyWalletId != '') {
                    getAmountFromGive();
                }
            });
        }
    });

    // Event: amount on input
    $('#amount').on('input', $.debounce(800, function ()
    {
        let amount = $(this).val().trim();
        let fromCurrencyWalletId = $('#fromCurencyWallet').val();
        let toCurrencyWalletId = $('#toCurencyWallet').val();

        if (($.isNumeric(amount) && amount > 0) && fromCurrencyWalletId != '') {
            checkAmountLimitAndFeesLimit()
            .then((status) => {
                if (status && toCurrencyWalletId != '') {
                    getAmountFromGive();
                }
            });
        } else if (amount == '') {
            $('#convertedAmount').val('');
            $('#amountLimitError').text('');
            determineDecimalPoint();
            hideExchangeRateGetAmountDiv();
        }
    }));


    $('#exchangeMoneyCreateForm').on('submit', function () {
        setTimeout(function()
        {
            $("#exchangeMoneyCreateSubmitBtn").removeAttr("disabled");
            $(".spinner").addClass('d-done');
            $("#exchangeMoneyCreateSubmitBtnText").html('Proceed');
            $("#sendMoneySvgIcon").removeClass('d-none');

        },2000);

        $(".spinner").removeClass('d-none');
        $("#exchangeMoneySvgIcon").addClass('d-none');
        $("#exchangeMoneyCreateSubmitBtn").attr("disabled", true);
        $("#exchangeMoneyCreateSubmitBtnText").text(submitBtnText);
    });
}

if ($('.main-containt').find('#exchangeMoneyConfirm').length) {
    $('#exchangeMoneyConfirmForm').on('submit', function ()
    {
        $('#exchangeMoneyConfirmBtn').attr("disabled", true);
        $('#exchangeMoneyBackButton').removeAttr('href');
    	$(".spinner").removeClass('d-none')
    	$('#exchangeMoneyConfirmBtnText').text(confirmBtnText);
    });
}