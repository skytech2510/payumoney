"use strict";

function restrictNumberToPrefdecimalOnInput(e) {
    var type = $('select#currency_id').find(':selected').data('type')
    restrictNumberToPrefdecimal(e, type);
}

function determineDecimalPoint() {

    var currencyType = $('select#currency_id').find(':selected').data('type');

    if (currencyType == 'crypto') {
        $('.pFees, .fFees, .total_fees').text(CRYPTODP);
        $("#amount").attr('placeholder', CRYPTODP);

    } else if (currencyType == 'fiat') {

        $('.pFees, .fFees, .total_fees').text(FIATDP);
        $("#amount").attr('placeholder', FIATDP);
    }
}

if ($('.main-containt').find('#withdrawalCreate').length) {
    $(window).on('load', function () {
        var paymentMethodId = JSON.parse($('option:selected', '#withdrawal_method_id').attr('data-type'));

        getFeesLimitsPaymentMethodsCurrencies(paymentMethodId)
            .then((data) => {
                determineDecimalPoint();
            }).then((data) => {
                withdrawalAmountLimitCheck(paymentMethodId);
            })
            .catch((error) => {
                console.log(error)
            });

        var paymentMethodObject = JSON.parse($('option:selected', '#withdrawal_method_id').attr('data-obj'));

        if (paymentMethodObject.email != null) {
            var p = '<input value="' + paymentMethodObject.email + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        } else if (paymentMethodObject.account_name != null) {
            var p = '<input value="' + paymentMethodObject.account_name + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        } else if (paymentMethodObject.account_number != null) {
            var p = '<input value="' + paymentMethodObject.account_number + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        } else if (paymentMethodObject.crypto_address != null) {
            var p = '<input value="' + paymentMethodObject.crypto_address + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        } else if (isActiveMobileMoney && paymentMethodObject.mobilemoney_id != null && paymentMethodObject.mobile_number != null) {
            var p = '<input value="' + paymentMethodObject.mobile_number + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        }
        $('#withdrawalMethodInfo').html(p);
        //bug fix finished
    });

    var lastPaymentMethod, currentPaymentMethod;

    $("select[name=withdrawal_method_id]").focus(function () {
        lastPaymentMethod = $('select#withdrawal_method_id').find(':selected').data('type');
    }).change(function () {
        currentPaymentMethod = $(this).find(':selected').data('type');
    });

    $(document).ready(function () {
        $("#withdrawal_method_id").on('change', function () {
            // Payment method (crypto to fiat or fiat to crypto)
            if (lastPaymentMethod != currentPaymentMethod) {
                $('#amount').val('');
                $('#amountLimit').text('');
            }
            lastPaymentMethod = currentPaymentMethod;

            $("#bank").css("display", "none");

            var paymentMethodObject = JSON.parse($('option:selected', '#withdrawal_method_id').attr('data-obj'));

            if (paymentMethodObject.email != null) {
                var p = '<input value="' + paymentMethodObject.email + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
            }
            else if (paymentMethodObject.account_name != null) {
                var p = '<input value="' + paymentMethodObject.account_name + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
            }
            else if (paymentMethodObject.account_number != null) {
                var p = '<input value="' + paymentMethodObject.account_number + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
            }
            else if (paymentMethodObject.crypto_address != null) {
                var p = '<input value="' + paymentMethodObject.crypto_address + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
            }
            $('#withdrawalMethodInfo').html(p);

            var paymentMethodId = JSON.parse($('option:selected', '#withdrawal_method_id').attr('data-type'));
            getFeesLimitsPaymentMethodsCurrencies(paymentMethodId).then((data) => {
                determineDecimalPoint();
            }).then((data) => {
                withdrawalAmountLimitCheck(paymentMethodId);
            })
            .catch((error) => {
                console.log(error)
            });
        });

        $('#currency_id, #amount').on('change keyup', $.debounce(1000, function (e) {
            var paymentMethodId = JSON.parse($('option:selected', '#withdrawal_method_id').attr('data-type'));
            withdrawalAmountLimitCheck(paymentMethodId);
        }));
    });

    function getFeesLimitsPaymentMethodsCurrencies(paymentMethodId) {
        $('#payment_method_id').val(paymentMethodId);
        var paymentMethodObject = JSON.parse($('option:selected', '#withdrawal_method_id').attr('data-obj'));

        var cryptoCurrencyId = paymentMethodObject != null ? paymentMethodObject.currency_id : null;

        return new Promise((resolve, reject) => {
            $.ajax({
                method: 'post',
                url: withdrawalActiveCurrency,
                data: {
                    "_token": csrfToken,
                    'transaction_type_id': transactionTypeId,
                    'payment_method_id': paymentMethodId,
                    'currencyId': cryptoCurrencyId
                },
                dataType: "json",
                success: function (response) {
                    if (response.success.status == 'success') {
                        let options = '';
                        $.map(response.success.currencies, function (value, index) {
                            options += `<option data-type="${value.type}" value="${value.id}" ${value.id == sessionCurrencyId ? 'selected' : ''} >${value.code}</option>`;
                        });
                        
                        $('#currency_id').html(options);
                        resolve(response.success.status);
                    }
                },
                error: function (error) {
                    reject(error)
                },
            });
        })
    }

    function withdrawalAmountLimitCheck(paymentMethodId) {
        $('#payment_method_id').val(paymentMethodId);
        var amount = $('#amount').val().trim();

        var currency_id = $('#currency_id').val();
        if (currency_id == '') {
            $('#walletHelp').hide();
        } else {
            $('#walletHelp').show();
        }

        if (currency_id && amount != '') {

            $.ajax({
                method: 'post',
                url: withdrawalAmountLimit,
                data: {
                    "_token": csrfToken,
                    'payment_method_id': paymentMethodId,
                    'currency_id': currency_id,
                    'transaction_type_id': transactionTypeId,
                    'amount': amount,
                },
                dataType: "json",
                success: function (res) {
                    if (res.success.status == 200) {
                        $('.total_fees').html(res.success.totalHtml);
                        $('.pFees').html(res.success.pFeesHtml);
                        $('.fFees').html(res.success.fFeesHtml);

                        //checking balance
                        if (res.success.totalAmount > res.success.balance) {
                            $('#amountLimit').html(notEnoughBalanceText);
                            $('#withdrawalCreateSubmitBtn').attr('disabled', true);
                        } else {
                            $('#amountLimit').html('');
                            $('#withdrawalCreateSubmitBtn').removeAttr('disabled');
                        }
                    } else {
                        if (amount == '') {
                            $('#amountLimit').text('');
                        } else {
                            $('#amountLimit').text(res.success.message);
                        }

                        $('#withdrawalCreateSubmitBtn').attr('disabled', true);
                        return false;
                    }
                }
            });
        }
    }

    $('#withdrawalCreateForm').on('submit', function () {
        setTimeout(function()
        {
            $("#withdrawalCreateSubmitBtn").removeAttr("disabled");
            $(".spinner").addClass('d-done');
            $("#withdrawalCreateSubmitBtnText").html('Proceed');
            $("#withdrawalSvgIcon").removeClass('d-none');

        },2000);

        $(".spinner").removeClass('d-none');
        $("#withdrawalSvgIcon").addClass('d-none');
        $("#withdrawalCreateSubmitBtn").attr("disabled", true);
        $("#withdrawalCreateSubmitBtnText").text(submitButtonText);
    });
}
if ($('.main-containt').find('#withdrawalConfirm').length) {

    $(document).on('click', '#withdrawalConfirmSubmitBtn', function (e) {
        $(".spinner").removeClass('d-none');
        $('#withdrawalConfirmSubmitBtnText').text(submitButtonText);
        $('#withdrawalConfirmSubmitBtn').attr("disabled", true);
        $('#withdrawalConfirmSubmitBtn').click(function (e) {
            e.preventDefault();
        });

        //Make back button disabled and prevent click
        $('#withdrawalConfirmBackBtnText').attr("disabled", true).click(function (e) {
            e.preventDefault();
        });

        //Make back anchor prevent click
        $('#withdrawalConfirmBackBtn').click(function (e) {
            e.preventDefault();
        });
    });
}
if ($('.main-containt').find('#withdrawalSuccess').length) {

    $(document).ready(function() {
        window.history.pushState(null, "", window.location.href);
        $(window).on("popstate", function() {
            window.history.pushState(null, "", window.location.href);
        });
    });

    // disabling F5
    $(document).on("keydown", function (e) {
        if ((e.which || e.keyCode) == 116) {
            e.preventDefault();
        }
    });

    // disabling ctrl+r
    $(document).on("keydown", function (e) {
        if (e.keyCode == 82 && e.ctrlKey) {
            e.preventDefault();
        }
    });
}