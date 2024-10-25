"use strict"

$(window).on('load', function() {
    $(".admin_access_ip_setting, .admin_2fa").select2({});
});

var input = document.querySelector('input[name=admin_access_ips]');
new Tagify(input)


function convertToSlug(Text) {
    return Text.toLowerCase()
                .replace(/ /g, '-')
                .replace(/[^\w-]+/g, '');
}

$('#admin-url-prefix').on('input', function() {
    var prefix = convertToSlug($(this).val());
    var url = site_url;
    $('.url').text(url + '/' + prefix);
});


$('#SecuritySettingsForm').validate({
    rules: {
        admin_url_prefix: {
            required: true,
            maxlength: 30,
        },
    },
    submitHandler: function(form) {
        $("#admin-security-settings-submit").attr("disabled", true);
        $(".fa-spin").removeClass('d-none');
        $("#admin-security-settings-submit-text").text(submitText);
        $('#admin-security-settings-submit').click(function(e) {
            e.preventDefault();
        });
        $('#cancel-link').click(function(e) {
            e.preventDefault();
        });
        form.submit();
    }
});
