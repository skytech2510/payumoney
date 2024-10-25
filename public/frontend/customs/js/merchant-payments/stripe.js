'use strict';

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return (charCode > 31 && (charCode < 48 || charCode > 57)) ? false : true;
}

$(document).ready(function() {
    $('#stripePaymentForm').on('submit', function(event) {
        event.preventDefault();
        $("#stripeSubmitBtn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#stripeSubmitBtnText").text(stripeSubmitBtnText);
        confirmPayment();
    });
});

function makePayment()
{
    var promiseObj = new Promise(function(resolve, reject) {
        let cardNumber = $("#cardNumber").val();
        let month      = $("#month").val();
        let year       = $("#year").val();
        let cvc        = $("#cvc").val();
        let currency   = $('#currency').val();
        let merchant   = $('#merchant').val();

        $("#stripeError").html('');
        if (cardNumber && month && year && cvc) {
            $.ajax({
                type: "POST",
                url: SITE_URL + "/standard-merchant/stripe-make-payment",
                data:
                {
                    "_token": token,
                    'cardNumber': cardNumber,
                    'month': month,
                    'year': year,
                    'cvc': cvc,
                    'currency': currency,
                    'merchant': merchant,
                    'amount': amount,
                },
                dataType: "json",
                beforeSend: function (xhr) {
                    $("#stripeSubmitBtn").attr("disabled", true);
                },
            }).done(function(response)
            {
                if (response.data.status != 200) {
                    $("#stripeError").html(response.data.message);
                    $("#stripeSubmitBtn").attr("disabled", true);
                    reject(response.data.status);
                    return false;
                } else {
                    $("#stripeSubmitBtn").attr("disabled", false);
                    resolve(response.data);
                }
            });
        }
    });
    return promiseObj;
}

function confirmPayment()
{
    makePayment().then(function(result) {
        var form = $('#stripePaymentForm')[0];
        var formData = new FormData(form);
        formData.append('_token', token);
        formData.append('paymentIntendId', result.paymentIntendId);
        formData.append('paymentMethodId', result.paymentMethodId);
        $.ajax({
            type: "POST",
            url: SITE_URL + "/payment/stripe",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function (xhr) {
                $("#stripeSubmitBtn").attr("disabled", true);
                $(".spinner").removeClass('d-none');
            },
        }).done(function(response)
        {
            $(".spinner").addClass('d-none');
            if (response.data.status != 200) {
                $("#stripeSubmitBtn").attr("disabled", true);
                $("#stripeError").html(response.data.message);
                return false;
            } else {
                window.location.replace(SITE_URL + '/payment/success');
            }
        });
    });
}

$("#month").change(function() {
    makePayment();
});

$("#year, #cvc, #cardNumber").on('keyup', $.debounce(1000, function() {
    makePayment();
}));

// For card number design
document.getElementById('cardNumber').addEventListener('input', function (e) {
    var target = e.target, position = target.selectionEnd, length = target.value.length;
    target.value = target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
    target.selectionEnd = position += ((target.value.charAt(position - 1) === ' ' && target.value.charAt(length - 1) === ' ' && length !== target.value.length) ? 1 : 0);
});