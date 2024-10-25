function checkRequestCreatorStatus() {
    var promiseObj = new Promise(function(resolve, reject) {
        var trans_id = $('.trxn').attr('data');
        $.ajax({
            url: requestPaymentCreatorStatusCheckUrl,
            type: "GET",
            data: {
                'trans_id': trans_id,
            },
            dataType: "json",
        })
        .done(function(res) {
            resolve(res.status);
        })
        .fail(function(error) {
            reject(error);
        });
    });
    return promiseObj;
}

//Request To - Cancel
$(document).on('click', '.trxn', function(e) {
    e.preventDefault();
    
    var trans_id = $(this).attr('data');
    var type = $(this).attr('data-type');
    var notificationType = $(this).attr('data-notificationType');

    $.post({
        url: requestPaymentCancelUrl,
        dataType: "json",
        data: {
            id: trans_id,
            type: type,
            notificationType: notificationType,
        },
        beforeSend: function() {
            $("#status_" + trans_id).text(cancellingText);
            $("#btn_" + trans_id).attr("disabled", true).text(cancellingText);
            $('.trxn_accept').hide();
        },
    })
    .done(function(data) {
        $("#status_" + trans_id).text(cancelledText);
        $("#btn_" + trans_id).text(cancelledText);

        setTimeout(function() {
            $("#btn_" + trans_id).fadeOut('fast');
        }, 1000);
    });
});

//Request To - Accept - only
$(document).on('click', '.trxn_accept', function(e) {
    e.preventDefault();
    checkUserSuspended(e);
    checkRequestCreatorStatus()
    .then(res => {
        if (res != "Suspended" && res != "Inactive") {
            window.location.replace(SITE_URL + "/request_payment/accept/" + ($(this).attr('data-rel')));
        } else {
            e.stopPropagation();
            if (res == "Suspended") {
                window.location.href = requestPaymentCreatorSuspendUrl;
            } else if (res == "Inactive") {
                window.location.href = requestPaymentCreatorInactiveUrl;
            }
            return false;
        }
    })
    .catch(error => {
        console.log(error);
    });
});