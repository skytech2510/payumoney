'use strict';

if ($('.content').find('#blockio-asset-create').length) {

    var networkErrorFlag = false;
    var addressErrorFlag = false;

    $(document).on('change','#currency-logo', function() {
        let orginalSource = defaultImageSource;
        readFileOnChange(this, $('#currency-demo-logo-preview'), orginalSource);
    });

    function checkSubmitBtn()
    {
        if (!networkErrorFlag && !addressErrorFlag) {
            $('#blockio-settings-submit-btn').attr("disabled", false);
        } else {
            $('#blockio-settings-submit-btn').attr("disabled", true);
        }
    }

    function checkMerchantAddress()
    {
        var api_key = $('#api_key').val().trim();
        var pin = $('#pin').val().trim();
        var address = $('#address').val().trim();
        var network = $('#network').val().trim();

        if (api_key.length > 0 && pin.length > 0 && address.length > 0 && network.length > 0) {
            swal(pleaseWait, loading, {
                closeOnClickOutside: false,
                closeOnEsc: false,
                buttons: false,
            });
            $.ajax({
                method: "GET",
                url: checkMerchantAddressUrl,
                dataType: "json",
                data: {
                    'api_key': api_key,
                    'pin': pin,
                    'address': address,
                },
            }).done(function(res) {
                swal.close();
                if (res.status == 400) {
                    $('.address-validation-error').text(res.message);
                    addressErrorFlag = true;
                } else if (res.status == 200 && res.network != network) {
                    $('.address-validation-error').text(merchantAddress);
                    addressErrorFlag = true;
                } else {
                    $('.address-validation-error').text('');
                    addressErrorFlag = false;
                }
                checkSubmitBtn();
            }).fail(function(err) {
                err.responseText.hasOwnProperty('message') == true ? alert(JSON.parse(err.responseText).message) : alert(err.responseText);
            });
        } else {
            $('.address-validation-error').text('');
            addressErrorFlag = false;
            checkSubmitBtn();
        }
    }

    function checkDuplicateNetwork(network)
    {
        let promiseObj = new Promise(function(resolve, reject){
            $.ajax({
                method: "GET",
                url: checkDuplicateNetworkUrl,
                dataType: "json",
                data: {
                    'network': network
                },
            })
            .done(function(checkDuplicateNetworkResponse)
            {
                if (checkDuplicateNetworkResponse.status == 400) {
                    $('.network-exist-error').text(checkDuplicateNetworkResponse.message);
                    resolve(checkDuplicateNetworkResponse['status']);
                    networkErrorFlag = true;
                    checkSubmitBtn();
                } else {
                    $('.network-exist-error').text('');
                    resolve(checkDuplicateNetworkResponse['status']);
                    networkErrorFlag = false;
                    checkSubmitBtn();
                }
            })
            .fail(function(err) {
                err.responseText.hasOwnProperty('message') == true ? alert(JSON.parse(err.responseText).message) : alert(err.responseText);
                reject();
            });
        });

        return promiseObj;
    }

    // Network duplicate check
    $('#network').on('keyup', $.debounce(1000, function(e) {

        let network = $(this).val();
        
        checkDuplicateNetwork(network)
        .then((checkDuplicateNetworkResponse) =>
        {
            if (checkDuplicateNetworkResponse == 200) {
                checkMerchantAddress();
            }
        }).catch(error => {
            error.responseText.hasOwnProperty('message') == true ? alert(JSON.parse(error.responseText).message) : alert(error.responseText);
        });
    }));

    // Check Merchant Api Key
    $(document).on('keyup', '#api_key', $.debounce(1000, function() {
        checkMerchantAddress();
    }));

    // Check Merchant Pin
    $(document).on('keyup', '#pin', $.debounce(1000, function() {
        checkMerchantAddress();
    }));

    // Check Merchant Network Address
    $(document).on('keyup', '#address', $.debounce(1000, function() {
        checkMerchantAddress();
    }));

    $(document).on('submit', '#add-blockio-network-form', function() {

        $("#blockio-settings-submit-btn").attr("disabled", true);
        $(".fa-spinner").removeClass('display-spinner');
        $("#blockio-settings-submit-btn-text").text(submitting);

        setTimeout(function(){
            $(".fa-spinner").addClass('display-spinner');
            $("#blockio-settings-submit-btn").attr("disabled", false);
            $("#blockio-settings-submit-btn-text").text(submit);
        }, 10000);
    });
}


if ($('.content').find('#blockio-asset-edit').length) {
            
    var addressErrorFlag = false;
    
    $(document).on('change','#currency-logo', function() {
        let orginalSource = defaultImageSource;
        readFileOnChange(this, $('#currency-demo-logo-preview'), orginalSource);
    });

    function checkSubmitBtn()
    {
        if (!addressErrorFlag) {
            $('#blockio-settings-edit-btn').attr("disabled", false);
        } else {
            $('#blockio-settings-edit-btn').attr("disabled", true);
        }
    }

    function checkMerchantAddress()
    {
        var api_key = $('#api_key').val().trim();
        var pin = $('#pin').val().trim();
        var address = $('#address').val().trim();
        var network = $('#network').val().trim();

        if (api_key.length > 0 && pin.length > 0 && address.length > 0 && network.length > 0) {
            swal(pleaseWait, loading, {
                closeOnClickOutside: false,
                closeOnEsc: false,
                buttons: false,
            });
            $.ajax({
                method: "GET",
                url: checkMerchantAddressUrl,
                dataType: "json",
                data: {
                    'api_key': api_key,
                    'pin': pin,
                    'address': address,
                },
            }).done(function(res) {
                swal.close();
                if (res.status == 400) {
                    $('.address-validation-error').text(res.message);
                    addressErrorFlag = true;
                } else if (res.status == 200 && res.network != network) {
                    $('.address-validation-error').text(merchantAddress);
                    addressErrorFlag = true;
                } else {
                    $('.address-validation-error').text('');
                    addressErrorFlag = false;
                }
                checkSubmitBtn();
            }).fail(function(err) {
                err.responseText.hasOwnProperty('message') == true ? alert(JSON.parse(err.responseText).message) : alert(err.responseText);
            });
        } else {
            $('.address-validation-error').text('');
            addressErrorFlag = false;
            checkSubmitBtn();
        }
    }

    // Check Merchant Api Key
    $(document).on('keyup', '#api_key', $.debounce(1000, function(e) {
        checkMerchantAddress();
    }));

    // Check Merchant Pin
    $(document).on('keyup', '#pin', $.debounce(1000, function(e) {
        checkMerchantAddress();
    }));

    // Check Merchant Network Address
    $(document).on('keyup', '#address', $.debounce(1000, function(e) {
        checkMerchantAddress();
    }));

    $('#network').on('keyup', $.debounce(1000, function(e) {
        checkMerchantAddress();
    }));


    $(document).on('submit', '#edit-blockio-network-form', function() {

        $("#blockio-settings-edit-btn").attr("disabled", true);
        $(".fa-spinner").removeClass('display-spinner');
        $("#blockio-settings-edit-btn-text").text(updating);

        setTimeout(function(){
            $(".fa-spinner").addClass('display-spinner');
            $("#blockio-settings-edit-btn").attr("disabled", false);
            $("#blockio-settings-edit-btn-text").text(update);
        }, 10000);
    });
}