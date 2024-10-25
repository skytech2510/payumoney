'use strict';

$(document).ready(function() {
    $('#walletPaymentForm').on('submit', function() {
        $("#walletSubmitBtn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#walletSubmitBtnText").text(walletSubmitBtnText);
    });
});