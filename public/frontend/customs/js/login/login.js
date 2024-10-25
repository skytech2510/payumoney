'use strict';

if (errorMessage) {
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "showDuration": "700"
    }
    if (errorMessageClass == 'alert-danger') {
        toastr.error(errorMessage);
    } else {
        toastr.success(errorMessage);
    }
}

$(document).ready(function() {
    new Fingerprint2().get(function(result, components)
    {
        $('#browser_fingerprint').val(result);
    });
});

$('#login-form').on('submit', function () {
    $('.spinner').removeClass('d-none');
    $('#rightAngleSvgIcon').addClass('d-none');
    $("#login-btn").attr("disabled", true);
    $("#login-btn-text").text(loginButtonText);
});