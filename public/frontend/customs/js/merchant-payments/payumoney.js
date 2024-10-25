'use strict';

$(document).ready(function() {
    $('#payUMoneyPaymentForm').on('submit', function() {
        $("#payUMoneySubmitBtn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#payUMoneySubmitBtnText").text(payUMoneySubmitBtnText);
    });
});

$('#payumoney-submit-button').trigger('click');

