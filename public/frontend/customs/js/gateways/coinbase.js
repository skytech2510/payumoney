'use strict';

$('#payment-form').on('submit', function () {
    $('.spinner').removeClass('d-none');
    $("#coinbase-button-submit").attr("disabled", true);
    $("#coinbaseSubmitBtnText").text(submitting);
});

