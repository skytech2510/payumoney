'use strict';

$('#forget-password-form').on('submit', function () {
    $('.spinner').removeClass('d-none');
    $("#forget-password-submit-btn").attr("disabled", true);
    $("#forget-password-submit-btn-text").text(submitBtnText);
});