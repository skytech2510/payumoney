"use strict";

$( window ).on( "load", function() {
    window.localStorage.removeItem('user_id');
    window.localStorage.removeItem('crypto-sent-amount');
    window.localStorage.removeItem('previousCrytoSentUrl');
    window.localStorage.removeItem('priority');
});


$(".crypto-dropdown").click(function() {
    $(this).toggleClass('crypto-dropdown-active');
});

$(document).click(function(e) {
    $(".crypto-dropdown").removeClass('crypto-dropdown-active');
});

$("#crypto-search-icon").click(function() {
    if (!$("#crypto-search-input").val().length) {
        $(".container-search, .crypto-search-input").toggleClass("active bg-white");
        $(this).toggleClass("color-black");
        $("input[type='text']").focus();
    }
});

$(document).click(function(e) {
    var container = $("#container-search");
    if (!container.is(e.target) && container.has(e.target).length === 0 && !$("#crypto-search-input").val().length) {
        $(".container-search, .crypto-search-input").removeClass("active");
        $(".container-search").removeClass('bg-white');
        $("#crypto-search-icon").removeClass('color-black');
    }
});

// Crypto Provider status change (BlockIo)
$(".provider-status").click(function() {

    let providerName = $(this).val();
    let providerStatus = $(this).prop('checked');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "POST",
        url: ADMIN_URL + "/crypto-provider/" + providerName + "/status-change",
        data: {
            'provider_status': providerStatus,
            'provider_name': providerName,
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 200) {
                swal({
                    title: updateText,
                    text: response.message,
                    type: "success"
                }, function() {
                    window.location.reload();
                });
            } else if (response.status == 400) {
                swal({
                    title: notFound,
                    text: response.message,
                    type: "error"
                }, function() {
                    window.location.reload();
                });
            } else {
                swal({
                    title: wrongInput,
                    text: wentWrong,
                    type: "error"
                }, function() {
                    window.location.reload();
                });
            }
        }
    });
});

// Network or Asset status change
$('.network').on('click', function(e) {
    e.preventDefault();
    let network = $(this).attr('data-network');
    let networkStatus = $(this).attr('data-status');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: "POST",
        url: assetStatusChangeUrl,
        data: {
            'network': network,
            'network_status': networkStatus,
        },
        dataType: 'json',
        success: function(response) {
            if (response.status == 200) {
                swal({
                    title: updateText,
                    text: response.message,
                    type: "success"
                }, function() {
                    window.location.reload();
                });
            } else if (response.status == 400) {
                swal({
                    title: notFound,
                    text: response.message,
                    type: "error"
                }, function() {
                    window.location.reload();
                });
            } else {
                swal({
                    title: wrongInput,
                    text: wentWrong,
                    type: "error"
                }, function() {
                    window.location.reload();
                });
            }
        }
    });

});

$('.validate-network').on('click', function(){
    let network = $(this).attr('data-network');
    $('.modal-title').text(validateCryptoAddress.replace(":x", network));
    $('#address').attr('placeholder', networkAddress.replace(":x", network));
    $('#address-validation-network').val(network);
});

// BlockIo address validate
$('#addressValidationModal form').on('submit', function(e) {
    e.preventDefault();
    let address = $('#address').val();
    let network = $('#address-validation-network').val();

    if (address.length > 0 && network.length > 0) {
        $('.fa-spin').removeClass('d-none');
        $("#address-validate-button").attr("disabled", true);
        $('#address-validate-button-text').text(checking);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "GET",
            url: validateAddressUrl,
            data: {
                'network': network,
                'address': address,
            },
            dataType: 'json',
            success: function(response) {
                $('.fa-spin').addClass('d-none');
                $("#address-validate-button").attr("disabled", false);
                $('#address-validate-button-text').text(validateAddress);
                let message = response.message;
                let status = response.status;
                $('.alert-class').removeClass('alert alert-success alert-danger');
                if (status == 200) {
                    $('.alert-class').addClass('alert alert-success');
                    $('#validate-address-error').css('display', 'block');
                    $('#validate-address-error-message').html('<span>'+message+'</span>');
                } else if (response.status == 400) {
                    $('.alert-class').addClass('alert alert-danger');
                    $('#validate-address-error').css('display', 'block');
                    $('#validate-address-error-message').html('<span>'+message+'</span>');
                } else {
                    $('.alert-class').addClass('alert alert-danger');
                    $('#validate-address-error').css('display', 'block');
                    $('#validate-address-error-message').html('<span>'+message+'</span>');
                }
            }
        });
    } else {
        $('.alert-class').addClass('alert alert-danger');
        $('#validate-address-error').css('display', 'block');
        $('#validate-address-error-message').html('<span>'+emptyAddress+'</span>');
    }
});

$('#addressValidationModal').on('hidden.bs.modal', function (e) {
    $('#crypto-address-check-form #address').val('');
    $('#validate-address-error').css('display', 'none');
    $('.alert-class').removeClass('alert alert-success alert-danger');
});