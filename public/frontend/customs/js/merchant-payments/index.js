'use strict';

$(document).ready(function() {
    $('#paymentMethodForm').on('submit', function() {
        $("#paymentMethodSubmitBtn").attr("disabled", true);
        $(".spinner").removeClass('d-none');
        $("#paymentMethodSubmitBtnText").text(paymentMethodSubmitBtnText);

        setTimeout(function () {
            $(".spinner").addClass('d-none');
            $("#paymentMethodSubmitBtn").attr("disabled", false);
            $("#paymentMethodSubmitBtnText").text(pretext);

        }, 2000);
    });
});
