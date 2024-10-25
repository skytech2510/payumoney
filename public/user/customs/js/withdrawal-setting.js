'use strict';

$('#deletemodal').on('show.bs.modal', function (e) {
    // Pass form reference to modal for submission on yes/ok
    var form  = $(e.relatedTarget).closest('form');
    $(this).find('.modals-bottom #delete-modal-yes').data('form', form);
});

$('#deletemodal').find('.modals-bottom #delete-modal-yes').on('click', function(e){
    $(this).data('form').trigger('submit');
});

//Clear validation errors on modal close - starts
$(document).ready(function() {
    $('#addModal').on('hidden.bs.modal', function (e) {
        $('.payoutSettingForm').find('.error').removeClass('error');
        $('.payoutSettingForm').trigger("reset");
        $('#crypto-address-error').text('');
        $('#currency-error').text('');
        $('#modalHeading').text(addModalHeadingText);
        $('#paypalWithdrawalSettingSubmitBtnText').text(addButtonText);
    });
});

function showHidePaymentSettingForm (paymentMethodType) { 

    if (paymentMethodType == paypalPaymentMethod) {
        $('#paypalPayoutSettingForm').removeClass('d-none');
        $('#bankPayoutSettingForm').addClass('d-none');
        $('#cryptoPayoutSettingForm').addClass('d-none');
        if (isActiveMobileMoney) {
            $('#mobileMoneyPayoutSettingForm').addClass('d-none');
        }
    } else if (paymentMethodType == bankPaymentMethod) {
        $('#bankPayoutSettingForm').removeClass('d-none');
        $('#paypalPayoutSettingForm').addClass('d-none');
        $('#cryptoPayoutSettingForm').addClass('d-none');
        if (isActiveMobileMoney) {
            $('#mobileMoneyPayoutSettingForm').addClass('d-none');
        }
    } else if (paymentMethodType == cryptoPaymentMethod) {
        $('#cryptoPayoutSettingForm').removeClass('d-none');
        $('#paypalPayoutSettingForm').addClass('d-none');
        $('#bankPayoutSettingForm').addClass('d-none');
        if (isActiveMobileMoney) {
            $('#mobileMoneyPayoutSettingForm').addClass('d-none');
        }
    } else if (isActiveMobileMoney && paymentMethodType == mobileMoneyPaymentMethod) {
        $('#mobileMoneyPayoutSettingForm').removeClass('d-none');
        $('#bankPayoutSettingForm').addClass('d-none');
        $('#paypalPayoutSettingForm').addClass('d-none');
        $('#cryptoPayoutSettingForm').addClass('d-none');
    }
}

$('#type').on('change', function()
{     
    showHidePaymentSettingForm($(this).val());
});

$('#addBtn').on('click', function(e)
{
    e.preventDefault();
    // if user is suspended
    checkUserSuspended(e);
    // if user is not suspended
    $('.settingId').html('');
    var form = $('.payoutSettingForm');
    form.attr('action', settingStoreUrl);
    $('.payoutSettingForm').trigger("reset");
    $('#type').val(paypalPaymentMethod).change().removeAttr('disabled');
});

$('.edit-setting').on('click', function(e)
{
    e.preventDefault();
    checkUserSuspended(e);
    //if user is not suspended
    let obj = JSON.parse($(this).attr('data-obj'));
    let settingId = $(this).attr('data-id');
    let html = '<input type="hidden" name="setting_id" value="' + settingId + '">';
    let form;

    showHidePaymentSettingForm(obj.type);

    $('#type').val(obj.type);
    $('#type').trigger('change').attr('disabled', 'true');
    $('#modalHeading').text(updateModalHeadingText);

    if (obj.type == bankPaymentMethod) {
        form = $('#bankPayoutSettingForm');
        form.attr('action', settingUpdateUrl);
        $('#bankSettingId').html(html);
        $("#bankWithdrawalSettingSubmitBtnText").text(updateButtonText);
        $.each(form[0].elements, function(index, elem)
        {
            switch (elem.name)
            {
                case "type":
                    $(this).val(obj.type).change().attr('disabled', 'true');
                    break;
                case "account_name":
                    $(this).val(obj.account_name);
                    break;
                case "account_number":
                    $(this).val(obj.account_number);
                    break;
                case "branch_address":
                    $(this).val(obj.bank_branch_address);
                    break;
                case "branch_city":
                    $(this).val(obj.bank_branch_city);
                    break;
                case "branch_name":
                    $(this).val(obj.bank_branch_name);
                    break;
                case "bank_name":
                    $(this).val(obj.bank_name);
                    break;
                case "country":
                    $(this).val(obj.country);
                    break;
                case "swift_code":
                    $(this).val(obj.swift_code);
                    break;
                default:
                    break;
            }
        })
        $('#country').trigger("change");
    } else if (obj.type == paypalPaymentMethod) {
        
        form = $('#paypalPayoutSettingForm');
        form.attr('action', settingUpdateUrl);
        $('#paypalSettingId').html(html);
        $("#paypalWithdrawalSettingSubmitBtnText").text(updateButtonText);
        $.each(form[0].elements, function(index, elem)
        {
            if (elem.name == 'email') {
                $(this).val(obj.email);
            } else if (elem.name == 'type') {
                $(this).val(obj.type).change().attr('disabled', 'true');
            }
        })
    } else if (obj.type == cryptoPaymentMethod) {
        form = $('#cryptoPayoutSettingForm');
        form.attr('action', settingUpdateUrl);
        $('#cryptoSettingId').html(html);
        $("#cryptoWithdrawalSettingSubmitBtnText").text(updateButtonText);
        $.each(form[0].elements, function(index, elem)
        {
            switch (elem.name)
            {
                case "type":
                    $(this).val(obj.type).change().attr('disabled', 'true');
                    break;
                case "crypto_address":
                    $(this).val(obj.crypto_address);
                    break;
                case "currency":
                    $(this).val(obj.currency_id);
                    break;
                default:
                    break;
            }
        })
    } else if (isActiveMobileMoney && obj.type == mobileMoneyPaymentMethod) {
        form = $('#mobileMoneyPayoutSettingForm');
        form.attr('action', settingUpdateUrl);
        $('#mobileMoneySettingId').html(html);
        $("#mobileMoneyWithdrawalSettingSubmitBtnText").text(updateButtonText);
        $.each(form[0].elements, function(index, elem)
        {
            switch (elem.name)
            {
                case "type":
                    $(this).val(obj.type).change().attr('disabled', 'true');
                    break;
                case "mobilemoney_id":
                    $(this).val(obj.mobilemoney_id);
                    break;
                case "mobile_number":
                    $(this).val(obj.mobile_number);
                    break;
                default:
                    break;
            }
        })
    }
    setTimeout(()=>{
        $('#addModal').modal('show');
    }, 400)

});

$('.delete-setting').on('click', function(e)
{
    e.preventDefault();
    checkUserSuspended(e);
});

$('#paypalPayoutSettingForm').on('submit', function () {
    $("#paypalWithdrawalSettingSpinner").removeClass('d-none');
    $("#paypalWithdrawalSettingSubmitBtn").attr("disabled", true);
    $("#paypalWithdrawalSettingSubmitBtnText").text(submitButtonText);
});

$('#bankPayoutSettingForm').on('submit', function () {
    $("#bankWithdrawalSettingSpinner").removeClass('d-none');
    $("#bankWithdrawalSettingSubmitBtn").attr("disabled", true);
    $("#bankWithdrawalSettingSubmitBtnText").text(submitButtonText);
});

$('#cryptoPayoutSettingForm').on('submit', function () {
    $("#cryptoWithdrawalSettingSpinner").removeClass('d-none');
    $("#cryptoWithdrawalSettingSubmitBtn").attr("disabled", true);
    $("#cryptoWithdrawalSettingSubmitBtnText").text(submitButtonText);
});

$('#mobileMoneyPayoutSettingForm').on('submit', function () {
    $("#mobileMoneyWithdrawalSettingSpinner").removeClass('d-none');
    $("#mobileMoneyWithdrawalSettingSubmitBtn").attr("disabled", true);
    $("#mobileMoneyWithdrawalSettingSubmitBtnText").text(submitButtonText);
});

$('#payment_method').on('change', function() {
    $('#withdrawalSettingSearchForm').trigger('submit');
});