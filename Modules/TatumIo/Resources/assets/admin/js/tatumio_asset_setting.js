'use strict';

if ($('.content').find('#tatumio-asset-create').length) {

    var networkErrorFlag = false;
    var addressErrorFlag = false;

    $(document).on('change','#currency-logo', function() {
        let orginalSource = defaultImageSource;
        readFileOnChange(this, $('#currency-demo-logo-preview'), orginalSource);
    });

    function checkSubmitBtn()
    {
        if (!networkErrorFlag && !addressErrorFlag) {
            $('#tatumio-settings-submit-btn').attr("disabled", false);
        } else {
            $('#tatumio-settings-submit-btn').attr("disabled", true);
        }
    }

    // Network duplicate check
    $('#network').on('keyup', $.debounce(1000, function(e) {


    }));

    // Check Merchant Api Key
    $(document).on('keyup', '#api_key', $.debounce(1000, function() {
        checkApiKey();
    }));


    $(document).on('submit', '#add-tatumio-network-form', function() {

        $("#tatumio-settings-submit-btn").attr("disabled", true);
        $(".fa-spinner").removeClass('display-spinner');
        $("#tatumio-settings-submit-btn-text").text(submitting);

        setTimeout(function(){
            $(".fa-spinner").addClass('display-spinner');
            $("#tatumio-settings-submit-btn").attr("disabled", false);
            $("#tatumio-settings-submit-btn-text").text(submit);
        }, 10000);
    });
}


if ($('.content').find('#tatumio-asset-edit').length) {

    var addressErrorFlag = false;

    $(document).on('change','#currency-logo', function() {
        let orginalSource = defaultImageSource;
        readFileOnChange(this, $('#currency-demo-logo-preview'), orginalSource);
    });

    function checkSubmitBtn()
    {
        if (!addressErrorFlag) {
            $('#tatumio-settings-edit-btn').attr("disabled", false);
        } else {
            $('#tatumio-settings-edit-btn').attr("disabled", true);
        }
    }


    $(document).on('submit', '#edit-tatumio-network-form', function() {

        $("#tatumio-settings-edit-btn").attr("disabled", true);
        $(".fa-spinner").removeClass('display-spinner');
        $("#tatumio-settings-edit-btn-text").text(updating);

        setTimeout(function(){
            $(".fa-spinner").addClass('display-spinner');
            $("#tatumio-settings-edit-btn").attr("disabled", false);
            $("#tatumio-settings-edit-btn-text").text(update);
        }, 10000);
    });
}
