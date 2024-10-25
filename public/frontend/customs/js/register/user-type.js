'use strict';

$('#register-form').on('submit', function() {
    $("#registrationSubmitBtn").attr("disabled", true);
    $("#rightAngle").addClass('d-none');
    $(".spinner").removeClass('d-none');
    $("#registrationSubmitBtnTxt").text(signingUpText);
});