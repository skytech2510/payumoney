'use strict';

paypal.Buttons({
    createOrder: function (data, actions) {
        // This function sets up the details of the transaction, including the amount and line item details.
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: amount
                }
            }]
        });
    },
    onApprove: function (data, actions) {
        // This function captures the funds from the transaction.
        return actions.order.capture().then(function (details) {
            // This function shows a transaction success message to your buyer.
            $('#Paypal').append('<input type="hidden" name="payment_id" id="payment_id" />');
            $("#payment_id").val(btoa(details.id));
            paypalSuccess();
        });
    }
}).render('#paypal-button-container');

function paypalSuccess()
{
    var form = $('#Paypal')[0];
    var formData = new FormData(form);
    formData.append('_token', token);
    $.ajax({
        type: "POST",
        url: SITE_URL + "/payment/paypal_payment_success",
        data: formData,
        processData: false,
        contentType: false,
        cache: false,
    }).done(function(response) {
        window.location.replace(SITE_URL + response.data.redirectedUrl);
  });
}