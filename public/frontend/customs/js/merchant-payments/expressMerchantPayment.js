'use strict';

$(document).ready(function() {
    $('#expressPaymentLoginForm').on('submit', function() {
        $("#expressPaymentLoginBtn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#expressPaymentLoginBtnText").text(expressPaymentLoginBtnText);
    });

    $('#expressPaymentConfirmForm').on('submit', function() {
        $("#expressPaymentSubmitBtn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#expressPaymentSubmitBtnText").text(expressPaymentSubmitBtnText);
    });
});