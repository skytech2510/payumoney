"use strict";

$(document).on('submit', '#walletPaymentForm', function () {
    $("#walletSubmitBtn").attr("disabled", true);
    $(".spinner").removeClass('d-none');
    $("#walletSubmitBtnText").text(submitText);
});


