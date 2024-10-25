"use strict";
$(function () {
    var lastVisitedUrl;

    $(".payment-loader").addClass("hidden");

    window.addEventListener("pagehide", function (event) {
        $(".payment-loader").removeClass("hidden");
    });


    window.onbeforeunload = function () {
        $(".payment-loader").removeClass("hidden");
    };

    window.onbeforeunload = function () {
        $(".payment-loader").removeClass("hidden");
    };

    window.onpageshow = function (event) {
        if (!event.persisted) {
            $(".payment-loader").removeClass("hidden");
        }
    };

    window.onpageshow = function (event) {
        if (event.persisted) {
            $(".payment-loader").addClass("hidden");
        }
    };

    $(document).on("click", ".return", function () {
        try {
            window.ReactNativeWebView.postMessage(JSON.stringify(response));
        } catch (err) {
            if (document.referrer.startsWith(window.location.origin) && document.referrer != window.location.origin && lastVisitedUrl !== document.referrer) {
                lastVisitedUrl = document.referrer;
                history.back()
            } else {
                window.location.replace(window.location.origin);
            }
        }
    });

    if ($(".confirmed-block").length > 0 || $(".failed-block").length > 0) {
        setTimeout(() => {
            window.ReactNativeWebView.postMessage(JSON.stringify(response));
        }, 3000);
    }
});
