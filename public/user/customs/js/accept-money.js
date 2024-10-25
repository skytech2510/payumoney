'use strict';

function restrictNumberToPrefdecimalOnInput(e)
{
    var type = $('#currency').data('type');
    restrictNumberToPrefdecimal(e, type);
}

function determineDecimalPoint() 
{
    var currencyType = $('#currency').data('type');
    if (currencyType == 'crypto') {
        $('#formattedFeesPercentage, #formattedFeesFixed, #formattedFeesTotal').text(CRYPTODP);
        $("#amount").attr('placeholder', CRYPTODP);
    } else if (currencyType == 'fiat') {
        $('#formattedFeesPercentage, #formattedFeesFixed, #formattedFeesTotal').text(FIATDP);
        $("#amount").attr('placeholder', FIATDP);
    }
}


if ($('.main-containt').find('#requestMoneyAccept').length) {
    // Code for Amount Limit  check
    $(document).on('input','.amount', $.debounce(800, function () {
        checkAmountLimitAndFeesLimit();
    }));

    function checkAmountLimitAndFeesLimit()
    {
        
        let amount = $('#amount').val().trim();
        let currency_id = $('#currency').attr('data-rel');
        if (amount != '') {
            $.ajax({
                method: "POST",
                url: requestMoneyAcceptLimitUrl,
                dataType: "json",
                data: {
                    "_token":token,
                    'amount':amount,
                    'currency_id':currency_id,
                    'transaction_type_id': transactionTypeId
                }
            })
            .done(function(response)
            {
                if(response.success.status == 200) {

                    $("#percentage_fee").val(response.success.feesPercentage);
                    $("#fixed_fee").val(response.success.feesFixed);
                    $("#total_fees").val(response.success.totalFees);

                    $('#formattedFeesPercentage').html(response.success.pFeesHtml);
                    $('#formattedFeesFixex').html(response.success.fFeesHtml);
                    $('#formattedFeesTotal').html(response.success.totalFeesHtml);

                    $('#requestMoneyAcceptSubmitBtn').removeAttr('disabled');
                    $('.amount-error').text('');
                    return true;
                } else if (response.success.status == 404) {
                    $('.amount-error').text('');
                    $('.currency-error').text(response.success.message);
                    $('#walletlHelp').hide();
                    $('#requestMoneyAcceptSubmitBtn').attr('disabled',true);
                } else {
                    $('#walletlHelp').show();

                    if(amount == '') {
                        $('.amount-error').text('');
                        $('#requestMoneyAcceptSubmitBtn').removeAttr('disabled');
                    } else {
                        $('.amount-error').text(response.success.message).insertAfter($('#amount'));
                        $('#requestMoneyAcceptSubmitBtn').attr('disabled',true);
                    }
                }
            });
        }
    }

    // Code for Amount Limit  check when window load
    $(window).on('load',function(e) {
        let currencyType = $('#currency').data('type');

        // Restrict Amount Decimal Places
        $("#amount").val(function()
        {
            if (this.value != '') {
                return restrictNumberToPrefdecimal(this, currencyType);
            }
        });
        checkAmountLimitAndFeesLimit();
    });

    $('#requestMoneyAcceptForm').on('submit', function () {
        setTimeout(function()
        {
            $("#requestMoneyAcceptSubmitBtn").removeAttr("disabled");
            $(".spinner").addClass('d-done');
            $("#requestMoneyAcceptSubmitBtnText").html('Proceed');
            $("#sendMoneySvgIcon").removeClass('d-none');

        },2000);

        $(".spinner").removeClass('d-none');
        $("#requestMoneySvgIcon").addClass('d-none');
        $("#requestMoneyAcceptSubmitBtn").attr("disabled", true);
        $("#requestMoneyAcceptSubmitBtnText").text(submitBtnText);
    });
}


if ($('.main-containt').find('#requestMoneyAcceptConfirm').length) {
    $('#requestMoneyAcceptConfirmForm').on('submit', function ()
    {
        $('#requestMoneyAcceptConfirmBtn').attr("disabled", true);
        $('#requestMoneyAcceptBackButton').removeAttr('href');
    	$(".spinner").removeClass('d-none')
    	$('#requestMoneyAcceptConfirmBtnText').text(confirmBtnText);
    });
}