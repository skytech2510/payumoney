'use strict';

function sendMessage(statusCode) {
    setTimeout(() => {
        (window["ReactNativeWebView"] || window).postMessage(JSON.stringify(statusCode));
    }, 100);
}

const successElement = $(".webview-success");
const failElement = $(".webview-fail");
const coinPayment = $(".webview-coinpayment");

if (successElement.length > 0) {
    sendMessage(201);
}

if (coinPayment.length > 0) {
    sendMessage('coinpayment-success');
}

if (failElement.length > 0) {
    sendMessage(401);
}

