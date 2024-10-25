"use strict";

function depositBankBack()
{
    localStorage.setItem("depositConfirmPreviousUrl",document.URL);
    window.history.back();
}

function getBanks()
{
    var bank = $('#bank').val();
    if (bank)
    {
        $.ajax({
            method: "POST",
            url: bankDetailsUrl,
            dataType: "json",
            cache: false,
            data: {
                '_token' : token,
                'bank': bank,
            }
        })
        .done(function(response)
        {
            if (response.status == true)
            {
                $('#bank_name').html(response.bank.bank_name);
                $('#account_name').html(response.bank.account_name);
                $('#account_number').html(response.bank.account_number);

                if (response.bank_logo) {
                    $("#bank_logo").html(`<img  src="${SITE_URL}/public/uploads/files/bank_logos/${response.bank_logo}"/>`);
                } else {
                    $("#bank_logo").html(`<img  src="${SITE_URL}/public/dist/images/gateways/bank.png"/>`);
                }
            }
            else
            {
                $('#bank_name, #bank_branch_name, #bank_branch_city, #bank_branch_address, #swift_code, #account_name, #account_number').html('');
            }
        });
    }
}

$(window).on('load',function()
{
    getBanks();
});


$(document).on('change', '#bank', function()
{
    getBanks();
});



$(document).on('submit', '#bank_deposit_form', function () {
    var pretext = $("#bankSubmitBtnText").text();
    $("#bank-button-submit").attr("disabled", true);
    $(".spinner").removeClass('d-none');
    $("#bankSubmitBtnText").text(confirming);
});


//Only go back by back button, if submit button is not clicked
$(document).on('click', '.deposit-bank-confirm-back-btn', function (e)
{
    e.preventDefault();
    depositBankBack();
});


