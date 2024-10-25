'use strict';

$('#reset-form').on('submit', function () {
    $('.spinner').removeClass('d-none');
    $('#rightAngleSvgIcon').addClass('d-none');
    $("#set-password-submit-btn").attr("disabled", true);
    $("#set-password-submit-btn-text").text(resetButtonText);
});