"use strict";

function readFileOnChange(element, previewElement, merchantDefaultLogo) {
    var file, reader;
    if (file = element.files[0]) {
        reader = new FileReader();
        reader.onload = function () {
            if (file.name.match(/.(png|jpg|jpeg|gif|bmp)$/i)) {
                previewElement.attr({ src: reader.result });
            }
            else {
                previewElement.attr({ src: merchantDefaultLogo });
            }
        }
        reader.readAsDataURL(file);
    }
}

function restrictNumberToPrefdecimalOnInput(e)
{
    var type = $('#currency_type').val();
    restrictNumberToPrefdecimal(e, type);
}

if ($('.main-containt').find('#merchantCreate').length) {
    // preview currency logo on change
    $(document).on('change', '#logo', function () {
        readFileOnChange(this, $('#merchantLogoPreview'), merchantDefaultLogo);
    });

    $(document).on("submit", "#merchantCreateForm", function () {
        $(".spinner").removeClass('d-none');
        $('#merchantCreateSubmitBtn').attr("disabled", true);
        $('#merchantCreateSubmitBtnText').text(submitButtonText);
    });
}

if ($('.main-containt').find('#merchantUpdate').length) {
    // preview currency logo on change
    $(document).on('change', '#logo', function () {
        if (merchantLogo != null) {
            readFileOnChange(this, $('#merchantLogoPreviewEdit'), merchantDefaultLogo);
        }
        readFileOnChange(this, $('#merchantLogoPreview'), merchantDefaultLogo);
    });

    $('#currency').on('change', function () {
        let currencyId = $(this).val();
        if (currentStatus == 'Approved' && currencyCurrencyId != currencyId) {
            $('#currencyChangeWarning').removeClass('d-none');
        } else {
            $('#currencyChangeWarning').addClass('d-none');
        }
    });

    $(document).on("submit", "#merchantUpdateForm", function () {
        $(".spinner").removeClass('d-none');
        $('#merchantUpdateSubmitBtn').attr("disabled", true);
        $('#merchantUpdateSubmitBtnText').text(submitButtonText);
    });
}

if ($('.main-containt').find('#merchantIndex').length) {

    jQuery.fn.delay = function (time, func) {
        return this.each(function () {
            setTimeout(func, time);
        });
    };

    $(document).on('click','.generate-standard-payment-form', function() {
        var merchant_id = $('#merchant_id').val(),
            item_name = $('#item_name').val(),
            order = $('#order').val(),
            paymentAmount = $('#amount').val(),
            custom = $('#custom').val(),
            merchant_main_id = $('#merchant_main_id').val(),
            merchantDefaultCurrency = $('#currency_id').val();

        var result = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>'+ appName +'</title><script src="'+ flasheshDarkPath +'"></script><link rel="stylesheet" href="'+ bootstrapCssPath +'"><link rel="stylesheet" href="'+ styleCssPath +'"><link rel="shortcut icon" href="'+ favIcon +'"/><link rel="stylesheet" href="'+ fontLink +'"><style>body{font-family: "Plus Jakarta Sans", sans-serif;}h3 {font-weight: 500;font-family: "Plus Jakarta Sans", sans-serif !important;}p {font-weight: 400;font-family: "Plus Jakarta Sans", sans-serif !important;}button {font-weight: 400; font-family: "Plus Jakarta Sans", sans-serif !important;}</style></head><body class="bg-body-muted"><div class="container-fluid container-layout px-0"><div class="section-payment"><div class="payment-main-module"><h3>'+ payButtonTitle +'</h3><p>'+ payButtonTxt +'</p><form method="POST" action="' + paymentFormUrl + '"><input type="hidden" name="merchant" value="' + merchant_id + '" /><input type="hidden" name="merchant_id" value="'+ merchant_main_id + '" /><input type="hidden" name="item_name" value="'+ item_name + '" /><input type="hidden" name="currency_id" value="'+ merchantDefaultCurrency + '" /><input type="hidden" name="order" value="'+ order + '" /><input type="hidden" name="amount" value="' + paymentAmount+ '" /><input type="hidden" name="custom" value="' + custom + '" /><button type="submit" class="btn btn-lg btn-primary"><strong>'+ payNowText +'</strong></button></form></div></div></div><script src="'+ jqueryPath +'"></script><script src="'+ bootstrapJsPath +'"></script><script src="'+ mainJsPath +'"></script></body></html>';

        $('#result').val(result);

        if (item_name != '' && order != '' && paymentAmount != '' && custom != '' && merchant_main_id != '' && merchantDefaultCurrency != '') {
            //generate qr-code for above form
            $.ajax({
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: standardMerchantQrCodeUrl,
                dataType: "json",
                data: {
                    'merchantId': merchant_main_id,
                    'merchantDefaultCurrency': merchantDefaultCurrency,
                    'paymentAmount': paymentAmount,
                },
                beforeSend: function () {
                    $('.merchant-qr-section').removeClass('d-none');
                    $('.loader-img').removeClass('d-none');
                    $('.merchant-qr-div').addClass('d-none');
                },
            })
            .done(function(response)
            {
                if (response.status) {
                    $('.loader-img').addClass('d-none');
                    $('#generate-standard-payment-form').addClass('d-none');
                    $('.merchant-qr-div').removeClass('d-none');
                    $('#qrCodeImg').attr('src', response.imgSource);
                }
            })
            .fail(function(error)
            {
                Swal.fire(
                    failedText,
                    JSON.parse(error.responseText).message,
                    'error'
                ).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            });
        } else {
            alert('Please fill out the form inputs.');
            $('.payment-form-qr-code').html('');
        }
    });

    function determineDecimalPoint() {
        
        let currencyType =  $('#currency_type').val();

        if (currencyType == 'crypto') {
            $("#amount").attr('placeholder', CRYPTODP);

        } else if (currencyType == 'fiat') {
            $("#amount").attr('placeholder', FIATDP);
        }
    }

    $(document).on('ready', function () {
        determineDecimalPoint();
    });

    //modal on show - show merchant details
    $('#merchantModaldetails').on('show.bs.modal', function (e)
    {
        let merchantEditUrl = SITE_URL + '/merchant/edit/' + $(e.relatedTarget).attr('data-id');
        let note = $(e.relatedTarget).attr('data-note');
        let logo = $(e.relatedTarget).attr('data-logo');
        let name = $(e.relatedTarget).attr('data-name');
        let status = $(e.relatedTarget).attr('data-status');
        let siteUrl = $(e.relatedTarget).attr('data-site_url');
        let merchantUuid = $(e.relatedTarget).attr('data-merchant_uuid');
        let createdAt = $(e.relatedTarget).attr('data-created_at');
        let merchantCurrencyCode = $(e.relatedTarget).attr('data-merchantCurrencyCode');
        let bgColor = $(e.relatedTarget).attr('data-statusColor')

        $('#merchant-img').attr('src', logo);
        $('#merchant-uuid').text(merchantUuid);
        $('#merchant-edit-route').attr('href', merchantEditUrl);
        $('#merchant-name').text(name);
        $('#merchant-status').text(status);
        $("#merchant-status-bg").removeClass (function (index, className) {
            return (className.match (/(^|\s)bg-\S+/g) || []).join(' ');
        });
        $('#merchant-status-bg').addClass('bg-' + bgColor);
        $('#merchant-site-url').text(siteUrl);
        $('#merchant-currency').text(merchantCurrencyCode);
        $('#merchant-created-at').text(createdAt);
        $('#merchant-note').text(note);
    });

    $(document).on('click','.gearBtn',function(e)
    {
        e.preventDefault();
        if ($(this).attr('data-type') == 'standard') {
            $('#item_name, #order, #amount, #custom').val('');
            $('#result').val('<form method="POST" action="'+ paymentFormUrl +'"><input type="hidden" name="order" id="result_order" value="#"/><input type="hidden" name="merchant" id="result_merchant" value="#"/><input type="hidden" name="merchant_id" id="result_merchant_id" value="#"/><input type="hidden" name="item_name" id="result_item_name" value="Testing payment"/><input type="hidden" name="amount" id="result_amount" value="#"/><input type="hidden" name="custom" id="result_custom" value="comment"/><button type="submit">' + submitText + '</button></form>');
            $('#generate-standard-payment-form').removeClass('d-none');
            $('.merchant-qr-section').addClass('d-none');
            // if not suspended
            let merchant = $(this).attr('data-marchant');
            $('#merchant_id').val(merchant);

            let merchantMainId = $(this).attr('data-marchantID');
            $('#merchant_main_id').val(merchantMainId);
            let merchantCurrencyCode = $(this).attr('data-merchantCurrencyCode'); //new
            if (merchantCurrencyCode) {
                $('#merchantCurrencyCode').html(merchantCurrencyCode);//new
            }
            let merchantCurrencyId = $(this).attr('data-merchantCurrencyId'); //new
            $('#currency_id').val(merchantCurrencyId);

            let merchantCurrencyType = $(this).attr('data-currencyType'); //new
            $('#currency_type').val(merchantCurrencyType);
            $('#merchantModal').modal('show');
        } else {
            let clientId = $(this).attr('data-client-id');
            let clientSecret = $(this).attr('data-client-secret');

            $('#client_id').val(clientId);
            $('#client_secret').val(clientSecret);

            let merchantCurrencyId = $(this).attr('data-merchantCurrencyId'); //new
            $('#currency_id').val(merchantCurrencyId);
        }
    });

    //on click - update express merchant qr code
    $(document).on('click','.update-express-merchant-qr-code',function(e)
    {
        e.preventDefault();
        let clientId = $('#client_id').val();
        let merchantId = $('#merchant_id').val();
        let merchantDefaultCurrencyId = $('#currency_id').val();
        executeExpressMerchantQrCode(clientId, merchantId, merchantDefaultCurrencyId);
    });

    function executeExpressMerchantQrCode(clientId, merchantId, merchantDefaultCurrencyId)
    {
        if (clientId != '' && merchantId != '' && merchantDefaultCurrencyId != '') {
            $.ajax({
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: expressMerchantQrCodeUrl,
                dataType: "json",
                data: {
                    'merchantId': merchantId,
                    'merchantDefaultCurrencyId': merchantDefaultCurrencyId,
                    'clientId': clientId,
                },
                beforeSend: function () {
                    $('.express-merchant-qr-div').addClass('d-none');
                    $('.loader-img').removeClass('d-none');
                },
            })
            .done(function(response)
            {
                if (response.status) {
                    $('.express-merchant-qr-div').removeClass('d-none');
                    $('.loader-img').addClass('d-none');
                    $('.expressMerchantQrCodeImg').attr("src", response.imgSource)
                }
            })
            .fail(function(error)
            {
                Swal.fire(
                    failedText,
                    JSON.parse(error.responseText).message,
                    'error'
                ).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            });
        }
    }

    //modal on show - generate express merchant qr code
    $('#expressMerchantQrCodeModal').on('show.bs.modal', function (e)
    {
        let clientId = $(e.relatedTarget).attr('data-clientId');
        let clientSecret = $(e.relatedTarget).attr('data-clientSecret');
        let merchantId = $(e.relatedTarget).attr('data-merchantId');
        let merchantDefaultCurrencyId = $(e.relatedTarget).attr('data-merchantDefaultCurrencyId');

        $('#client_id').val(clientId);
        $('#client_secret').val(clientSecret);
        $('#merchant_id').val(merchantId);
        $('#currency_id').val(merchantDefaultCurrencyId);

        $.ajax({
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "GET",
                url: expressMerchantGetQrCodeUrl,
                dataType: "json",
                data: {
                    'merchantId': merchantId,
                }
            })
            .done(function(response)
            {
                if (response.status) {
                    $('.express-merchant-qr-div').removeClass('d-none');
                    $('.expressMerchantQrCodeImg').attr("src", response.imgSource)
                }
            })
            .fail(function(error)
            {
                Swal.fire(
                    failedText,
                    JSON.parse(error.responseText).message,
                    'error'
                ).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            });
        
    });

    $('#expressMerchantQrCodeModal').on('hidden.bs.modal', function (e)
    {
        $('.express-merchant-qr-div').addClass('d-none');
    });

    //on click - print express merchant qr code
    $(document).on('click','#qr-code-print-express',function(e)
    {
        e.preventDefault();

        let expressMerchantId = $('#merchant_id').val();
        let printQrCodeUrl = SITE_URL+'/merchant/qr-code-print/'+expressMerchantId+'/express_merchant';
        $(this).attr('href', printQrCodeUrl);
        window.open($(this).attr('href'), '_blank');
    });

    $('#click-to-copy').on('click', function () {
        $('#copiedMessage').css('color', 'green');
        $('#copy-parent-div').css('opacity', '1').delay(5000, function () {
            $('#copy-parent-div').css('opacity', '0')
        });
        $('#result').removeAttr('disabled').select().attr('disabled', 'true');
        document.execCommand('copy');
    });

    $('#client_secret').on('focus', function ()
    {
        $(this).select();
        document.execCommand('copy');

        $('#copy-parent-div-client-secret').addClass('show-copied');
        setInterval(remove_copy_secret, 5000);
    });

    $('#client_id').on('focus', function ()
    {
        $(this).select();
        document.execCommand('copy');

        $('#copy-parent-div-client').addClass('show-copied');
        setInterval(remove_copy, 5000);
    });

    $('#copyClientIdBtn').on('click', function() {
        $('#client_id').select();
        document.execCommand('copy');
        $('#copy-parent-div-client').addClass('show-copied');
        setInterval(remove_copy, 5000);
    });

    $('#copyClientSecretBtn').on('click', function() {
        $('#client_secret').select();
        document.execCommand('copy');
        $('#copy-parent-div-client-secret').addClass('show-copied');
        setInterval(remove_copy_secret, 5000);
    });

    //on click - print standard merchant qr code
    $(document).on('click','#qr-code-print-standard',function(e)
    {
        e.preventDefault();

        let standardMerchantId = $('#merchant_main_id').val();
        let printQrCodeUrl = SITE_URL+'/merchant/qr-code-print/'+standardMerchantId+'/standard_merchant';
        $(this).attr('href', printQrCodeUrl);
        window.open($(this).attr('href'), '_blank');
    });

    function remove_copy() {
        $('#copy-parent-div-client').removeClass('show-copied');
    }

    function remove_copy_secret() {
        $('#copy-parent-div-client-secret').removeClass('show-copied');
    }

}

if ($('.main-containt').find('#merchantPayment').length) {

    $(function() {
        var sDate;
        var eDate;

        $('#daterange-btn').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment(),

            }, function (start, end) {
                sDate = moment(start, 'MMMM D, YYYY').format('DD-MM-YYYY');
                $('#startfrom').val(sDate);
                eDate = moment(end, 'MMMM D, YYYY').format('DD-MM-YYYY');
                $('#endto').val(eDate);
                $('#daterange-btn p').html(sDate + ' - ' + eDate);
            }
        )
        
        if (startDate == '') {
            $('#daterange-btn p').html(dateRangePickerText);
        } else {
            $('#daterange-btn p').html(startDate + ' - ' + endDate);
        }
    });

    $(document).ready(function () {

        let status = $('#status').val();
        let currency = $('#currency').val();
        let paymentMethod = $('#paymentMethod').val();
        let merchant = $('#merchant').val();

        if (startDate != '' || status != 'all' || currency != 'all' || paymentMethod != 'all' || merchant != 'all') {
            $(".filter-panel").css('display', 'block');
        }
        
        $(".fil-btn").on('click', function () {
            $(this).find('img').toggle();
            $(".filter-panel").slideToggle(300);
        });
    });
}