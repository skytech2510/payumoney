"use strict";

$(function () {
    $(".select2").select2({
    });
});

$('#currencyPaymentMethod_form').validate({
    rules: {
        "stripe[secret_key]": {
            required: true,
        },
        "stripe[publishable_key]":{
            required: true,
        },
        "paypal[client_id]": {
            required: true,
        },
        "paypal[client_secret]":{
            required: true,
        },
        "paypal[mode]":{
            required: true,
        },
        "payUmoney[key]":{
            required: true,
        },
        "payUmoney[salt]":{
            required: true,
        },
        "payUmoney[mode]":{
            required: true,
        },
        "coinpayments[merchant_id]":{
            required: true,
        },
        "coinpayments[public_key]":{
            required: true,
        },
        "coinpayments[private_key]":{
            required: true,
        },
        "payeer[merchant_id]":{
            required: true,
        },
        "payeer[secret_key]":{
            required: true,
        },
        "payeer[encryption_key]":{
            required: true,
        },
        "payeer[merchant_domain]":{
            required: true,
        },
        "cash[merchant_reference]": {
            required: true,
        },
        cash_status:{
            required: true,
        },
        "cash[payment_number]": {
            required: true,
        },
        mobile_status:{
            required: true,
        },
        processing_time:{
            required: true,
            number: true,
        },
    },
    messages: {
        "paypal[mode]": {
            required: modeRequire
        },
        "payUmoney[mode]": {
            required: modeRequire
        }
    },
    submitHandler: function(form)
    {
        $("#paymentMethodList_update").attr("disabled", true);
        $('#cancel_anchor').attr("disabled","disabled");
        $(".fa-spinner").removeClass('d-none');
        $("#paymentMethodList_update_text").text(updateText);
        // Click False
        $('#paymentMethodList_update').click(false);
        $('#cancel_anchor').click(false);
        form.submit();
    }
});


$('#add-bank').validate({
    rules: {
        account_name: {
            required: true
        },
        default: {
            required: true
        },
        status: {
            required: true
        },
        account_number: {
            required: true,
        },
        swift_code: {
            required: true,
        },
        bank_name: {
            required: true
        },
        branch_name: {
            required: true
        },
        branch_city: {
            required: true
        },
        branch_address: {
            required: true
        },
        country: {
            required: true
        },
        bank_logo: {
            extension: extensionsValidationRule,
        }
    },
    messages: {
        bank_logo: {
            extension: extensionsValidationMessage
        },
    },
    submitHandler: function (form)
    {
        $('#submit_btn').attr('disabled', true);
        $('.fa-spinner').removeClass('d-none');
        $('#bank-add-submit-btn-text')
        $("#bank-add-submit-btn-text").text(submitText);

        var form_data = new FormData();
        form_data.append('_token', $("input[name=_token]").val());
        form_data.append('currency_id', $('#currency_id').val());
        form_data.append('paymentMethod', $('#paymentMethod').val());
        form_data.append('default', $('#default').val());
        form_data.append('status', $('#status').val());
        form_data.append('account_name', $('#account_name').val());
        form_data.append('account_number', $('#account_number').val());
        form_data.append('swift_code', $('#swift_code').val());
        form_data.append('bank_name', $('#bank_name').val());
        form_data.append('branch_name', $('#branch_name').val());
        form_data.append('branch_city', $('#branch_city').val());
        form_data.append('branch_address', $('#branch_address').val());
        form_data.append('country', $('#country').val());

        let transaction_type = $("input[name='add_transaction_type']:checked").map(function() {
            return $(this).val();
        }).get();

        form_data.append('transaction_type', JSON.stringify(transaction_type));

        var bank_logo = document.getElementById('bank_logo');
        let logo = (typeof bank_logo.files[0] !== 'undefined' ? bank_logo.files[0] : '')
        form_data.append('bank_logo', logo);

        $.ajax({
            method: "POST",
            url: ADMIN_URL+"/settings/payment-methods/add-bank",
            cache: false,
            dataType:'json',
            contentType: false,
            processData: false,
            data: form_data,
        })
        .done(function(response)
        {
            $('#submit_btn').attr('disabled', false);
            if (response.status) {
                $('#edit_currency_id').val(response.currency_id);
                $('#edit_paymentMethod').val(response.paymentMethod);

                $('#addModal').modal('hide');
                swal({title: titleText, text: response.message, type: "success"},
                    function(){
                        window.location.reload();
                    }
                );
            } else {
                var errorMessage = '';
                $.each(response.message, function(key, value)
                {
                    errorMessage += '<li>'+ value+'</li>';
                });
                $('#add-bank-error').css('display', 'block');
                $('#add-bank-error-messages').html(errorMessage);
            }
        });
        return false;
    }
});

$('#edit-bank').validate({
    rules: {
        edit_account_name: {
            required: true
        },
        edit_account_number: {
            required: true,
        },
        edit_swift_code: {
            required: true,
        },
        edit_bank_name: {
            required: true
        },
        edit_branch_name: {
            required: true
        },
        edit_branch_city: {
            required: true
        },
        edit_branch_address: {
            required: true
        },
        edit_country: {
            required: true
        },
        edit_bank_logo: {
            extension: extensionsValidationRule,
        }
    },
    messages: {
        edit_bank_logo: {
            extension: extensionsValidationMessage
        },
    },
    submitHandler: function (form)
    {
        $('#edit_submit_btn').attr('disabled', true);
        $('.fa-spinner').removeClass('d-none');
        $('#bank-edit-submit-btn-text')
        $("#bank-edit-submit-btn-text").text(updateText);

        var form_data = new FormData();
        form_data.append('_token', $("input[name=_token]").val());
        form_data.append('bank_id', $('#bank_id').val());
        form_data.append('file_id', $("#file_id").val());
        form_data.append('currencyPaymentMethodId', $('#currencyPaymentMethodId').val());
        form_data.append('currency_id', $('#edit_currency_id').val());
        form_data.append('paymentMethod', $('#edit_paymentMethod').val());
        form_data.append('default', $('#edit_default').val());
        form_data.append('status', $('#edit_status').val());
        form_data.append('account_name', $('#edit_account_name').val());
        form_data.append('account_number', $('#edit_account_number').val());
        form_data.append('swift_code', $('#edit_swift_code').val());
        form_data.append('bank_name', $('#edit_bank_name').val());
        form_data.append('branch_name', $('#edit_branch_name').val());
        form_data.append('branch_city', $('#edit_branch_city').val());
        form_data.append('branch_address', $('#edit_branch_address').val());
        form_data.append('country', $('#edit_country').val());
        let transaction_type = $("input[name='update_transaction_type']:checked").map(function() {
            return $(this).val();
        }).get();

        form_data.append('transaction_type', JSON.stringify(transaction_type));

        var edit_bank_logo = document.getElementById('edit_bank_logo');
        let logo = (typeof edit_bank_logo.files[0] !== 'undefined' ? edit_bank_logo.files[0] : '')
        form_data.append('bank_logo', logo);

        $.ajax({
            method: "POST",
            url: ADMIN_URL+"/settings/payment-methods/update-bank",
            cache: false,
            dataType:'json',
            contentType: false,
            processData: false,
            data: form_data,
        })
        .done(function(response)
        {
            $('#edit_submit_btn').attr('disabled', false);

            if (response.status) {
                $('#editModal').modal('hide');
                swal({title: titleText, text: response.message, type: "success"},
                    function(){
                        window.location.reload();
                    }
                );
            } else {
                var errorMessage = '';
                $.each(response.message, function(key, value)
                {
                    errorMessage += '<li>'+ value+'</li>';
                });
                $('#bank-error').css('display', 'block');
                $('#bank-error-messages').html(errorMessage);
            }
        })
        .fail(function(){
            swal(errorTitle, errorText, 'error');
        });
        return false;
    }
});


$(document).ready(function() {
    $('#addModal').on('hidden.bs.modal', function (e) {
        $('#add-bank').validate().resetForm();
        $('#add-bank').find('.error').removeClass('error');
        $('#submit_btn').prop('disabled',false);
        $('#bank_logo').val('');
        $('#bank-demo-logo-preview').attr({src: bankLogo});
    });

    $('#editModal').on('hidden.bs.modal', function (e) {
        $('#edit-bank').validate().resetForm();
        $('#edit-bank').find('.error').removeClass('error');
        $('#submit_btn').prop('disabled',false);
        $('#edit_bank_logo').val('');
    });
});

function handleFileSelect()
{
    var input = this;
    if (input.files && input.files.length) {
        var reader = new FileReader();
        this.enabled = false;
        reader.onload = (function (e)
        {
            if (!input.files[0].name.match(/.(png|jpg|jpeg|gif|bmp|ico)$/i)) {
                $('#bank-demo-logo-preview').attr({src: bankLogo});
                $('#submit_btn').prop('disabled',true);
            } else {
                $('#bank-demo-logo-preview').attr({src: reader.result});
                $('#submit_btn').prop('disabled',false);
            }
        });
        reader.readAsDataURL(input.files[0]);
    }
}

$('#bank_logo').change(handleFileSelect);

function handleFileSelectEdit()
{
    var input = this;
    if (input.files && input.files.length) {
        var reader = new FileReader();
        this.enabled = false;
        reader.onload = (function (e)
        {
            if (!input.files[0].name.match(/.(png|jpg|jpeg|gif|bmp|ico)$/i)) {
                $('.thumb-bank-logo').attr({src: bankLogo});
                $('.remove_edit_bank_logo_preview').remove();
                $('#edit_submit_btn').prop('disabled',true);
            } else {
                let logo = $('.thumb-bank-logo').attr('data-bank-logo');
                if (logo != '') {
                    $('.thumb-bank-logo').attr({src: reader.result});
                    $('.remove_edit_bank_logo_preview').remove();
                }
                $('.thumb-bank-logo').attr({src: reader.result});
                $('#edit_submit_btn').prop('disabled',false);
            }
        });
        reader.readAsDataURL(input.files[0]);
    }
}

$('#edit_bank_logo').change(handleFileSelectEdit);

//edit-setting
$(document).on('click','.edit-setting', function (e)
{
    e.preventDefault();
    var bank_id = $(this).data('id');
    var currency_id = $('#currency_id').val();
    var paymentMethod = $('#paymentMethod').val();
    if (bank_id && currency_id) {
        getCpmId(bank_id,currency_id,paymentMethod);
    }
});


function getCpmId(bank_id,currency_id,paymentMethod)
{
    $.ajax({
        headers:
        {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        url: ADMIN_URL+"/settings/payment-methods/getCpmId",
        cache: false,
        dataType:'json',
        data: {
            'bank_id': bank_id,
            'currency_id': currency_id,
        },
    })
    .done(function(response)
    {
        if (response.status) {
            $('#bank_id').val(bank_id);
            $('#edit_currency_id').val(currency_id);
            $('#edit_paymentMethod').val(paymentMethod);
            $('#currencyPaymentMethodId').val(response.cpmId);
            $('#edit_default').val(response.is_default);
            $('#edit_default').trigger('change');
            var activated_for = JSON.parse(response.cpmActivatedFor);
            $('input:checkbox').prop('checked', false);
                for (let key in activated_for) {
                    if (activated_for.hasOwnProperty(key)) {
                        $("input[value='" + key + "']").prop('checked', true);
                    }
                }
            $('#edit_account_name').val(response.account_name);
            $('#edit_account_number').val(response.account_number);
            $('#edit_branch_address').val(response.bank_branch_address);
            $('#edit_branch_city').val(response.bank_branch_city);
            $('#edit_branch_name').val(response.bank_branch_name);
            $('#edit_bank_name').val(response.bank_name);
            $('#edit_country').val(response.country_id);
            $('#edit_country').trigger('change');
            $('#edit_swift_code').val(response.swift_code);

            if (response.bank_logo && response.file_id) {
                //et file ID of bank logo
                $("#file_id").val(response.file_id);

                $(".preview_edit_bank_logo").html(`<img class="img-w80 thumb-bank-logo remove-bank-logo" data-bank-logo="${response.bank_logo}" data-file-id="${response.file_id}" src="${SITE_URL}/public/uploads/files/bank_logos/${response.bank_logo}" /><span class="remove_edit_bank_logo_preview"></span>`);
            } else {
                $(".preview_edit_bank_logo").html(`<img class="thumb-bank-logo" src="${bankLogo}" class="img-w80" />`);
            }

            $('#editModal').show();
        } else {
            swal(errorTitle, noResponseText, 'error');
        }
    });
}

//remove_edit_bank_logo_preview
$('.preview_edit_bank_logo').on('click', '.remove_edit_bank_logo_preview', function ()
{
    var file_id = $('.remove-bank-logo').attr('data-file-id');
    if(file_id) {
        $.ajax(
        {
            headers:
            {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type : "POST",
            url : ADMIN_URL+"/settings/payment-methods/delete-bank-logo",
            data: {
                'file_id' : file_id,
            },
            dataType : 'json',
            success: function(response)
            {
                if (response.success == 1) {
                    swal(
                        deleteTitle,
                        response.message,
                        'success'
                    )
                    $(".preview_edit_bank_logo").html(`<img class="thumb-bank-logo" src="${bankLogo}" class="img-w80" />`);
                } else {
                    swal(errorTitle, response.message);
                }
            }
        });
    } else {
        $(".preview_edit_bank_logo").empty();
        $("#edit_bank_logo").val("");
    }
});

//DELETE
$(document).on('click', '.delete', function()
{
    var bank_id = $(this).data('id');

    swal({
        title: deleteAlert,
        text: alertText.replace('&#039;', '\''),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: confirmBtnText,
        cancelButtonText: cancelBtnText,
        closeOnConfirm: false,
        showLoaderOnConfirm: true,
        closeOnCancel: true
    },
    function(isConfirm)
    {
        if (!isConfirm) return;

        if (isConfirm) {
            $.ajax({
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: ADMIN_URL+"/settings/payment-methods/delete-bank",
                dataType: "json",
                cache: false,
                data: {
                    'bank_id': bank_id,
                }
            })
            .done(function(response)
            {
                if (response.status ) {
                    swal({title: deleteTitle, text: response.message, type:response.type},
                        function(){
                        window.location.reload();
                        }
                    );
                } else {
                    swal(errorTitle, response.message, response.type);
                }
            })
            .fail(function(){
                swal(errorTitle, errorText, 'error');
            });
        } else {
            swal(cancelTitle, cancelAlert, "error");
        }
    });
});

$(window).on('load',function()
{
    var previousUrl = document.referrer;
    var urlByOwn    = ADMIN_URL+'/settings/currency';
    if(previousUrl==urlByOwn) {
        localStorage.removeItem('currencyId');
        localStorage.removeItem('currencyName');
    } else {
        if ((localStorage.getItem('currencyName')) && (localStorage.getItem('currencyId'))) {
            $('.currencyName').text(localStorage.getItem('currencyName'));
            $('#currency_id').val(localStorage.getItem('currencyId'));
            getPaymentMethodsDetails();
        } else {
            getPaymentMethodsSpecificCurrencyDetails();
        }
    }
});


$('.listItem').on('click',function()
{
    var currencyId       = $(this).attr('data-rel');
    var currencyName     = $(this).text();

    localStorage.setItem('currencyId',currencyId);
    localStorage.setItem('currencyName',currencyName);

    $('.currencyName').text(currencyName);
    $('#currency_id').val(currencyId);
    getPaymentMethodsDetails();
});


//Window on load/click on list item get fees limit details
function getPaymentMethodsDetails()
{
    var currencyId = $('#currency_id').val();
    var paymentMethod = $('#paymentMethod').val();
    var token = $("input[name=_token]").val();
    var url = ADMIN_URL+'/settings/get-payment-methods-details';

    $.ajax({
        url : url,
        type : "post",
        data : {
            'currency_id':currencyId,
            'paymentMethod':paymentMethod,
            '_token':token
        },
        dataType : 'json',
        success:function(data)
        {
            if (data.flag  && data.methodTitle == 'Bank') {
                let tr = '';
                $.each(data.banks, function(key, value)
                {
                    tr += 	'<tr>'+
                                '<td class="d-none">'+ value.id + '</td>'+
                                '<td>'+ value.bank_name +'</td>'+
                                '<td>'+ value.account_name  + '&nbsp;&nbsp;' + '(*****' + value.account_number.substr(-4) + ')&nbsp;&nbsp;' + value.bank_name +'</td>'+
                                '<td>'+ checkBankDefault(value.is_default) +'</td>'+
                                '<td>'+
                                    '<a data-id="'+value.id+'" data-bs-toggle="modal" data-bs-target="#editModal" class="btn btn-xs btn-primary edit-setting"><i class="fa fa-edit"></i></a> '+
                                    '<button data-id="'+value.id+'" type="button" class="btn btn-xs btn-danger delete"><i class="fa fa-trash"></i></button>' +
                                '</td>'+
                            '</tr>';
                });
                $('#bank_body').html(tr);
            } else if (isActiveMobileMoney  && data.flag  && data.methodTitle == 'MobileMoney') {
                let tr = '';
                $.each(data.mobileMoneys, function(key, value)
                {
                    tr += 	'<tr>'+
                                '<td class="d-none">'+ value.id + '</td>'+
                                '<td>'+ value.mobilemoney_name +'</td>'+
                                '<td>'+ value.mobilemoney_number +'</td>'+
                                '<td>'+ value.holder_name +'</td>'+
                                '<td>'+ value.merchant_code +'</td>'+
                                '<td>'+ checkBankDefault(value.is_default) +'</td>'+
                                '<td>'+ checkBankStatus(value.activated_for) +'</td>'+
                                '<td>'+
                                    '<a data-id="'+value.id+'" class="btn btn-xs btn-primary mobile-money-edit-setting"><i class="fa fa-edit"></i></a> '+
                                    '<button data-id="'+value.id+'" type="button" class="btn btn-xs btn-danger mobilemoney_delete"><i class="fa fa-trash"></i></button>' +
                                '</td>'+
                            '</tr>';
                });
                $('#mobilemoney_body').html(tr);
            } else {
                $('#bank_body').html('');
                if (isActiveMobileMoney ) {
                    $('#mobilemoney_body').html('');
                }
            }

            if (data.status == 200) {
                $('#id').val(data.currencyPaymentMethod.id);

                $('#stripe_secret_key').val(JSON.parse(data.currencyPaymentMethod.method_data).secret_key);
                $('#stripe_publishable_key').val(JSON.parse(data.currencyPaymentMethod.method_data).publishable_key);

                $('#paypal_client_id').val(JSON.parse(data.currencyPaymentMethod.method_data).client_id);
                $('#paypal_client_secret').val(JSON.parse(data.currencyPaymentMethod.method_data).client_secret);
                $('#paypal_mode').val(JSON.parse(data.currencyPaymentMethod.method_data).mode);

                $('#payUMoney_key').val(JSON.parse(data.currencyPaymentMethod.method_data).key);
                $('#payUMoney_salt').val(JSON.parse(data.currencyPaymentMethod.method_data).salt);
                $('#payUMoney_mode').val(JSON.parse(data.currencyPaymentMethod.method_data).mode);

                $('#coinPayments_merchant_id').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_id);
                $('#coinPayments_public_key').val(JSON.parse(data.currencyPaymentMethod.method_data).public_key);
                $('#coinPayments_private_key').val(JSON.parse(data.currencyPaymentMethod.method_data).private_key);

                $('#payeer_merchant_id').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_id);
                $('#payeer_secret_key').val(JSON.parse(data.currencyPaymentMethod.method_data).secret_key);
                $('#payeer_encryption_key').val(JSON.parse(data.currencyPaymentMethod.method_data).encryption_key);
                $('#payeer_merchant_domain').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_domain);

                $('#cash_merchant_reference').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_reference);

                $('#mobile_payment_number').val(JSON.parse(data.currencyPaymentMethod.method_data).payment_number);

                $('#processing_time').val(data.currencyPaymentMethod.processing_time);

                var activated_for = JSON.parse(data.currencyPaymentMethod.activated_for);
                $('input:checkbox').prop('checked', false);
                for (let key in activated_for) {
                    if (activated_for.hasOwnProperty(key)) {
                        $("input[value='" + key + "']").prop('checked', true);
                    }
                }

                if (activated_for.hasOwnProperty('deposit')) {
                    $('#stripe_status').val('Active');
                    $('#paypal_status').val('Active');
                    $('#payUMoney_status').val('Active');
                    $('#coinPayments_status').val('Active');
                    $('#payeer_status').val('Active');
                    $('#cash_status').val('Active');
                    $('#mobile_status').val('Active');
                } else {
                    $('#stripe_status').val('Inactive');
                    $('#paypal_status').val('Inactive');
                    $('#payUMoney_status').val('Inactive');
                    $('#coinPayments_status').val('Inactive');
                    $('#payeer_status').val('Inactive');
                    $('#cash_status').val('Inactive');
                    $('#mobile_status').val('Inactive');
                }
            } else {
                $('#id').val('');
                $('#stripe_secret_key').val('');
                $('#stripe_publishable_key').val('');

                $('#paypal_client_id').val('');
                $('#paypal_client_secret').val('');
                $('#paypal_mode').val('');

                $('#payUMoney_key').val('');
                $('#payUMoney_salt').val('');
                $('#payUMoney_mode').val('');

                $('#coinPayments_merchant_id').val('');
                $('#coinPayments_public_key').val('');
                $('#coinPayments_private_key').val('');

                $('#payeer_merchant_id').val('');
                $('#payeer_secret_key').val('');
                $('#payeer_encryption_key').val('');
                $('#payeer_merchant_domain').val('');

                $('#cash_merchant_reference').val('');

                $('#mobile_payment_number').val('');

                $('#processing_time').val('');

                $('#stripe_status').val('');
                $('#paypal_status').val('');
                $('#payUMoney_status').val('');
                $('#coinPayments_status').val('');
                $('#payeer_status').val('');
                $('#cash_status').val('');
                $('#mobile_status').val('');
                $('input:checkbox').prop('checked', false);
            }
        },
        error: function(error){
            swal(
                failedText,
                JSON.parse(error.responseText).message,
                'error'
            )
        }
    });
}

//Get Specific Currency Details
function getPaymentMethodsSpecificCurrencyDetails()
{
    var currencyId    = $('#currency_id').val();
    var paymentMethod = $('#paymentMethod').val();
    var token         = $("input[name=_token]").val();
    var url           = ADMIN_URL+'/settings/get-payment-methods-specific-currency-details';

    $.ajax({
        url : url,
        type : "post",
        data : {
            'currency_id':currencyId,
            'paymentMethod':paymentMethod,
            '_token':token
        },
        dataType : 'json',
        success:function(data)
        {
            if (data.flag  && data.methodTitle == 'Bank') {
                var tr = '';
                $.each(data.banks, function(key, value)
                {
                    tr += 	'<tr>'+
                                '<td class="d-none">'+ value.id + '</td>'+
                                '<td>'+ value.bank_name +'</td>'+
                                '<td>'+ value.account_name  + '&nbsp;&nbsp;' + '(*****' + value.account_number.substr(-4) + ')&nbsp;&nbsp;' + value.bank_name +'</td>'+
                                '<td>'+ checkBankDefault(value.is_default) +'</td>'+
                                '<td>'+
                                    '<a data-id="'+value.id+'" data-bs-toggle="modal" data-bs-target="#editModal" class="btn btn-xs btn-primary edit-setting"><i class="fa fa-edit"></i></a> '+
                                    '<button data-id="'+value.id+'" type="button" class="btn btn-xs btn-danger delete"><i class="fa fa-trash"></i></button>' +
                                '</td>'+
                            '</tr>';
                });
                $('#bank_body').html(tr);
            } else if (isActiveMobileMoney  && data.flag  && data.methodTitle == 'MobileMoney') {
                var tr = '';
                $.each(data.mobileMoneys, function(key, value)
                {
                    tr += 	'<tr>'+
                                '<td class="d-none">'+ value.id + '</td>'+
                                '<td>'+ value.mobilemoney_name +'</td>'+
                                '<td>'+ value.mobilemoney_number +'</td>'+
                                '<td>'+ value.holder_name +'</td>'+
                                '<td>'+ value.merchant_code +'</td>'+
                                '<td>'+ checkBankDefault(value.is_default) +'</td>'+
                                '<td>'+ checkBankStatus(value.activated_for) +'</td>'+
                                '<td>'+
                                    '<a data-id="'+value.id+'" class="btn btn-xs btn-primary mobile-money-edit-setting"><i class="fa fa-edit"></i></a> '+
                                    '<button data-id="'+value.id+'" type="button" class="btn btn-xs btn-danger mobilemoney_delete"><i class="fa fa-trash"></i></button>' +
                                '</td>'+
                            '</tr>';
                });
                $('#mobilemoney_body').html(tr);
            } else {
                $('#bank_body').html('');
                if (isActiveMobileMoney ) {
                    $('#mobilemoney_body').html('');
                }
            }

            if (data.status == 200) {
                $('.currencyName').text(data.currency.name);
                $('#currency_id').val(data.currency.id);

                $('#stripe_secret_key').val(JSON.parse(data.currencyPaymentMethod.method_data).secret_key);
                $('#stripe_publishable_key').val(JSON.parse(data.currencyPaymentMethod.method_data).publishable_key);

                $('#paypal_client_id').val(JSON.parse(data.currencyPaymentMethod.method_data).client_id);
                $('#paypal_client_secret').val(JSON.parse(data.currencyPaymentMethod.method_data).client_secret);
                $('#paypal_mode').val(JSON.parse(data.currencyPaymentMethod.method_data).mode);

                $('#payUMoney_key').val(JSON.parse(data.currencyPaymentMethod.method_data).key);
                $('#payUMoney_salt').val(JSON.parse(data.currencyPaymentMethod.method_data).salt);
                $('#payUMoney_mode').val(JSON.parse(data.currencyPaymentMethod.method_data).mode);

                $('#coinPayments_merchant_id').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_id);
                $('#coinPayments_public_key').val(JSON.parse(data.currencyPaymentMethod.method_data).public_key);
                $('#coinPayments_private_key').val(JSON.parse(data.currencyPaymentMethod.method_data).private_key);

                $('#payeer_merchant_id').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_id);
                $('#payeer_secret_key').val(JSON.parse(data.currencyPaymentMethod.method_data).secret_key);
                $('#payeer_encryption_key').val(JSON.parse(data.currencyPaymentMethod.method_data).encryption_key);
                $('#payeer_merchant_domain').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_domain);

                $('#cash_merchant_reference').val(JSON.parse(data.currencyPaymentMethod.method_data).merchant_reference);

                $('#mobile_payment_number').val(JSON.parse(data.currencyPaymentMethod.method_data).payment_number);

                $('#processing_time').val(data.currencyPaymentMethod.processing_time);

                var activated_for = JSON.parse(data.currencyPaymentMethod.activated_for);
                $('input:checkbox').prop('checked', false);
                for (let key in activated_for) {
                    if (activated_for.hasOwnProperty(key)) {
                        $("input[value='" + key + "']").prop('checked', true);
                    }
                }

                if (activated_for.hasOwnProperty('deposit')) {
                    $('#stripe_status').val('Active');
                    $('#paypal_status').val('Active');
                    $('#payUMoney_status').val('Active');
                    $('#coinPayments_status').val('Active');
                    $('#payeer_status').val('Active');
                    $('#cash_status').val('Active');
                    $('#mobile_status').val('Active');
                } else {
                    $('#stripe_status').val('Inactive');
                    $('#paypal_status').val('Inactive');
                    $('#payUMoney_status').val('Inactive');
                    $('#coinPayments_status').val('Inactive');
                    $('#payeer_status').val('Inactive');
                    $('#cash_status').val('Inactive');
                    $('#mobile_status').val('Inactive');
                }
            } else {
                $('#id').val('');
                $('.currencyName').text(data.currency.name);
                $('#currency_id').val(data.currency.id);

                $('#stripe_secret_key').val('');
                $('#stripe_publishable_key').val('');

                $('#paypal_client_id').val('');
                $('#paypal_client_secret').val('');
                $('#paypal_mode').val('');

                $('#payUMoney_key').val('');
                $('#payUMoney_salt').val('');
                $('#payUMoney_mode').val('');

                $('#coinPayments_merchant_id').val('');
                $('#coinPayments_public_key').val('');
                $('#coinPayments_private_key').val('');

                $('#payeer_merchant_id').val('');
                $('#payeer_secret_key').val('');
                $('#payeer_encryption_key').val('');
                $('#payeer_merchant_domain').val('');

                $('#cash_merchant_reference').val('');
                $('#mobile_payment_number').val('');

                $('#processing_time').val('');

                $('#stripe_status').val('');
                $('#paypal_status').val('');
                $('#payUMoney_status').val('');
                $('#coinPayments_status').val('');
                $('#payeer_status').val('');
                $('#cash_status').val('');
                $('#mobile_status').val('');
                $('input:checkbox').prop('checked', false);
            }
        },
        error: function(error){
            swal(
                failedText,
                JSON.parse(error.responseText).message,
                'error'
            )
        }
    });
}


function checkBankDefault(is_default)
{
    var cell = '';
    if (is_default == "Yes") {
        cell = '<span class="label label-success">' + yesText + '</span>';
    } else if (is_default == "No") {
        cell = '<span class="label label-danger">' + noText + '</span>';
    }
    return cell;
}

function checkBankStatus(activated_for)
{
    var cell = '';
    var activated = JSON.parse(activated_for);
    if (activated.hasOwnProperty('deposit')) {
        cell = '<span class="label label-success">' + activeText + '</span>';
    } else {
        cell = '<span class="label label-danger">' + inactiveText + '</span>';
    }
    return cell;
}

$(document).on('click', '#coinpayments_copy_button', function(e) {
    e.preventDefault();
    $('.coinpayments_ipn_url').select();
    document.execCommand('copy');
    swal({
        title: copyTitle,
        text: copyText,
        type: "success",
        icon: "success",
        closeOnClickOutside: false,
        closeOnEsc: false,
    });
})

$(document).on('click', '#coinbase_commerce_copy_button', function(e) {
    e.preventDefault();
    $('.coinpayments_ipn_url').select();
    document.execCommand('copy');
    swal({
        title: linkCopiedText,
        text: coinbaseCopyText,
        type: "success",
        icon: "success",
        closeOnClickOutside: false,
        closeOnEsc: false,
    });
})
