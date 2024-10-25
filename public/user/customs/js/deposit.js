"use strict";

function restrictNumberToPrefdecimalOnInput(e) {
    var type = $('select#currency_id').find(':selected').data('type')
    restrictNumberToPrefdecimal(e, type);
}

function determineDecimalPoint() {
    var currencyType = $('select#currency_id').find(':selected').data('type')
    if (currencyType == 'crypto') {
        $('.pFees, .fFees, .total_fees').text(CRYPTODP);
        $("#amount").attr('placeholder', CRYPTODP);
    } else if (currencyType == 'fiat') {
        $('.pFees, .fFees, .total_fees').text(FIATDP);
        $("#amount").attr('placeholder', FIATDP);
    }
}

// external.js file
function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return (charCode > 31 && (charCode < 48 || charCode > 57)) ? false : true;
}

if ($('.main-containt').find('#depositCreate').length) {
    function activePaymentMethodsList(currency_id) {
        return new Promise((resolve, reject) => {
            let token = $('[name="_token"]').val();
            $.post(paymentMethodListUrl, {
                "_token": token,
                'transaction_type_id': transactionTypeId,
                'currency_id': currency_id,
            })
            .done((response) => {
                if (response.success.paymentMethods != '') {
                    let options = '';
                    $.map(response.success.paymentMethods, (value, index) => {
                        options +=
                            `<option value="${value.id}" ${value.id == selectedPaymentMethod ? 'selected' : ''}>${value.name}</option>`;
                    });
                    $('#payment_method').html(options);
                    $('#paymentMethodSection').removeClass('d-none');
                    $('#paymentMethodEmpty').addClass('d-none');
                    $('#depositCreateSubmitBtn').removeClass('d-none');
                    checkAmountLimitAndFeesLimit();
                    resolve();
                } else {
                    $('#payment_method').val('');
                    $("#percentage_fee").val(0.00);
                    $("#fixed_fee").val(0.00);
                    $("#total_fee").val(0.00);
                    $('.pFees').html('0');
                    $('.fFees').html('0');
                    $(".total_fees").html('0.00');
                    $('#paymentMethodSection').addClass('d-none');
                    $('#paymentMethodEmpty').removeClass('d-none');
                    $('#depositCreateSubmitBtn').addClass('d-none');
                    resolve();
                }
            })
            .fail((error) => {
                reject(error);
            });
        });
    }

    $(window).on('load', function() {
        determineDecimalPoint();
        var currency_id = $('#currency_id').val();
        setTimeout(function() {
            activePaymentMethodsList(currency_id);
        }, 300);
    });

    var lastCurrencyType, currentCurrencyType;
    $('select').change(function() {
        lastCurrencyType = $(this).attr('data-old') !== typeof undefined ? $(this).attr('data-old') : "";
        currentCurrencyType = $("option:selected", this).data('type');
        $(this).attr('data-old', currentCurrencyType)
    }).change();

    // Code for Fees Limit check
    $(document).on('change', '#currency_id', function() {
        if (lastCurrencyType !== currentCurrencyType) {
            $('#amount').val('');
        }
        $('.amount-limit-error').text('');
        determineDecimalPoint();
        var currency_id = $('#currency_id').val();
        activePaymentMethodsList(currency_id);
    });

    //Fees Limit check on payment method change
    $(document).on('change', '#payment_method', function() {
        checkAmountLimitAndFeesLimit();
    });

    //Fees Limit check on amount input
    $(document).on('input', '#amount', $.debounce(1000, function() {
        checkAmountLimitAndFeesLimit();
    }));

    function checkAmountLimitAndFeesLimit() {
        var token = $('[name="_token"]').val();
        var amount = $('#amount').val().trim();
        var currency_id = $('#currency_id').val();
        var payment_method_id = $('#payment_method option:selected').val();

        if (amount != '') {
            $.post({
                url: feesLimitUrl,
                dataType: "json",
                data: {
                    "_token": token,
                    'amount': amount,
                    'currency_id': currency_id,
                    'payment_method_id': payment_method_id,
                    'transaction_type_id': transactionTypeId
                }
            }).done(function(response) {
                if (response.success.status == 200) {
                    $("#percentage_fee").val(response.success.feesPercentage);
                    $("#fixed_fee").val(response.success.feesFixed);
                    $("#total_fee").val(response.success.totalFees);

                    $(".total_fees").html(response.success.formattedTotalFees);
                    $('.pFees').html(response.success.formattedFeesPercentage); //2.3
                    $('.fFees').html(response.success.formattedFeesFixed); //2.3

                    $('.amountLimit').text('');
                    $('#depositCreateSubmitBtn').attr('disabled', false);
                    // return true;
                } else {
                    if (amount == '') {
                        $('.amountLimit').text('');
                        $('#depositCreateSubmitBtn').attr('disabled', false);
                    } else {
                        $('.amountLimit').text(response.success.message);
                        $('#depositCreateSubmitBtn').attr('disabled', true);
                        return false;
                    }
                }
            });
        }
    }

    $('#depositCreateForm').on('submit', function() {
        $(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
        $("#depositCreateSubmitBtn").attr("disabled", true);
        $("#depositCreateSubmitBtnText").text(submitBtnText);
    });
}

if ($('.main-containt').find('#depositStripe').length) {
    $(document).ready(function() {
        $('#stripePaymentForm').submit(function(event) {
            event.preventDefault();
            $("#depositStripeSubmitBtn").attr("disabled", true);
            $(".spinner").removeClass('d-none');
            $("#rightAngleSvgIcon").addClass('d-none');
            $('#depositConfirmBackBtn').removeAttr('href');
            $("#depositStripeSubmitBtnText").text(confirmBtnText);
            confirmPayment();
        });
    });

    function makePayment() {
        var promiseObj = new Promise(function(resolve, reject) {
            var cardNumber = $("#cardNumber").val().trim();
            var month = $("#month").val().trim();
            var year = $("#year").val().trim();
            var cvc = $("#cvc").val().trim();
            $("#stripeError").html('');
            if (cardNumber && month && year && cvc) {
                $.post({
                    url: stripeMakePaymentUrl,
                    data: {
                        "_token": token,
                        'cardNumber': cardNumber,
                        'month': month,
                        'year': year,
                        'cvc': cvc
                    },
                    beforeSend: function(xhr) {
                        $("#depositStripeSubmitBtn").attr("disabled", true);
                    },
                }).done(function(response) {
                    if (response.data.status != 200) {
                        $("#stripeError").html(response.data.message);
                        reject(response.data.status);
                    } else {
                        $("#depositStripeSubmitBtn").attr("disabled", false);
                        resolve(response.data);
                    }
                });
            }
        });
        return promiseObj;
    }

    function confirmPayment() {
        makePayment().then(function(result) {
            $.ajax({
                url: stripeConfirmPaymentUrl,
                method: "POST",
                data: {
                    "_token": token,
                    'paymentIntendId': result.paymentIntendId,
                    'paymentMethodId': result.paymentMethodId,
                },
                dataType: "json",
                beforeSend: function(xhr) {
                    $("#depositStripeSubmitBtn").attr("disabled", true);
                },
            }).done(function(response) {
                if (response.data.status != 200) {
                    $("#depositStripeSubmitBtn").attr("disabled", true);
                    $("#stripeError").html(response.data.message);
                    return false;
                } else {
                    $("#depositStripeSubmitBtn").attr("disabled", false);
                    window.location.replace(stripeSuccessUrl);
                }
            }).fail(function(error) {
                $("#depositStripeSubmitBtn").attr("disabled", true);
                $("#stripeError").html(errorMessage);
            });
        }).catch(function(error) {
            $("#depositStripeSubmitBtn").attr("disabled", true);
            $("#stripeError").html(errorMessage);
        });
    }

    $("#month").on("change", function() {
        $("#depositStripeSubmitBtn").prop("disabled", true);
        makePayment();
    });

    $("#cardNumber, #year, #cvc").on("keyup", $.debounce(1000, function() {
        $("#depositStripeSubmitBtn").prop("disabled", true);
        makePayment();
    }));

    $('#cardNumber').on('input', function (e) {
        var target = e.target, position = target.selectionEnd, length = target.value.length;
        target.value = target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
        target.selectionEnd = position += ((target.value.charAt(position - 1) === ' ' && target.value.charAt(length - 1) === ' ' && length !== target.value.length) ? 1 : 0);
    });
}

if ($('.main-containt').find('#depositPaypal').length) {

    if (typeof paypal === "undefined") {
        $('#paypal-errors').text(paypalErrorText)
    }

    paypal.Buttons({
        createOrder: function (data, actions) {
            // This function sets up the details of the transaction, including the amount and line item details.
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: amount
                    }
                }]
            });
        },
        onApprove: function (data, actions) {
            return actions.order.capture().then(function (details) {
                window.location.replace(SITE_URL + "/deposit/paypal-payment/success/" + btoa(details.purchase_units[0].amount.value));
            });
        }
    }).render('#paypal-button-container');
}

if ($('.main-containt').find('#depositBank').length) {
    function getBanks() {
        const bank = $('#bank').val();
        if (bank) {
            $.post({
                url: bankDetailsUrl,
                data: {
                    '_token': token,
                    'bank': bank
                }
            })
            .done(function({ status, bank: { bank_name, account_name, account_number }, bank_logo }) {
                if (status) {
                    $('#bank_name').html(bank_name);
                    $('#account_name').html(account_name);
                    $('#account_number').html(account_number);

                    const logoSrc = bank_logo ? `${bankLogoPath}/${bank_logo}` : `${defaultBankLogoPath}`;
                    $("#bank_logo").html(`<img src="${logoSrc}" class="img-fluid" width="120" height="80"/>`);
                } else {
                    $('#bank_name, #account_name, #account_number').html('');
                }
            });
        }
    }

    $(window).on('load change', function() {
        setTimeout(function() {
            getBanks();
        }, 300);
    });

    $('#depositBankForm').on('submit', function() {
        $("#depositBankSubmitBtn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
        $('#depositConfirmBackBtn').removeAttr('href');
        $("#depositBankSubmitBtnText").text(confirmBtnText);
    });
}
if ($('.main-containt').find('#depositConfirm').length) {
    $('#depositConfirmForm').on('submit', function () {
        $('#depositConfirmBtn').attr("disabled", true);
        $('#depositConfirmBackBtn').removeAttr('href');
    	$(".spinner").removeClass('d-none');
        $("#rightAngleSvgIcon").addClass('d-none');
    	$('#depositConfirmBtnText').text(confirmBtnText);

        setTimeout(function() {
            $('#depositConfirmBtn').attr("disabled", false);
    	    $(".spinner").addClass('d-none');
            $("#rightAngleSvgIcon").addClass('d-none');
    	    $('#depositConfirmBtnText').text(pretext);
        }, 300);

    });
}
if ($('.main-containt').find('#depositSuccess').length) {
    $(document).ready(function () {
        // disable browser back button
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, "", window.location.href);
        };

        // disable F5
        $(document).on("keydown", function (e) {
            if ((e.which || e.keyCode) == 116) {
                e.preventDefault();
            }
        });

        // disable Ctrl+R
        $(document).on("keydown", function (e) {
            if (e.keyCode == 82 && e.ctrlKey) {
                e.preventDefault();
            }
        });
    });
}
