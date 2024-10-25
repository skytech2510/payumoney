'use strict';

$('#payumoney-button').on('click', function() {

    var pretext = $("#payumoneySubmitBtnText").text();

    $.ajax({
        type: "POST",
        url: paymentUrl,
        data: {
            '_token' : token,
            'gateway' : 'payumoney',
            'amount': amount,
            'currency_id': currency_id,
            'payment_type' : payment_type,
            'redirect_url' : redirect_url,
            'transaction_type' : transaction_type,
            'payment_method_id': payment_method_id,
            'params' : params,
            'cancel_url' : cancel_url
        },
        dataType: "json",
        beforeSend: function (xhr) {
            $("payumoney-button").attr("disabled", true);
            $(".spinner").removeClass("d-none");
            $("#payumoneySubmitBtnText").text(submitText);
        },
    }).done(function(response)
    {
        if (response.status == 200) {

            var response = response.data.data;

            $("input[name='key']").val(response.key);
            $("input[name='hash']").val(response.hash);
            $("input[name='txnid']").val(response.txnid);
            $("input[name='amount']").val(response.amount);
            $("input[name='email']").val(response.email);
            $("input[name='firstname']").val(response.firstname);
            $("input[name='productinfo']").val(response.productinfo);
            $("input[name='surl']").val(response.surl);
            $("input[name='furl']").val(response.furl);
            $("input[name='service_provider']").val(response.service_provider);
            $('#payuform').prop('action', response.baseurl);

            document.getElementById('payuform').submit();
        } else {
             $("#payuMoneyError").html(response.message);
             $("payumoney-button").attr("disabled", false);
             $(".spinner").addClass("d-none");
             $("#payumoneySubmitBtnText").text(pretext);
        }

    });
});


