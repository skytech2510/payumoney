'use strict';

$(function () {
    $(".editor").wysihtml5();
});

if ($('.content-wrapper').find('#emailTemplate').length) {
    $('#emailTemplateForm').on('submit', function() {
        $("#emailTemplateUpdateSubmitBtn").attr("disabled", true);
        $(".fa-spin").removeClass('d-none');
        $("#emailTemplateUpdateSubmitBtnText").text(submitBtnText);
    });
}

if ($('.content-wrapper').find('#smsTemplate').length) {
    $('#smsTemplateForm').on('submit', function() {
        $("#smsTemplateUpdateSubmitBtn").attr("disabled", true);
        $(".fa-spin").removeClass('d-none');
        $("#smsTemplateUpdateSubmitBtnText").text(submitBtnText);
    });
}