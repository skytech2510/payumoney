<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'deposit/payumoney_confirm',
        'deposit/payumoney_fail',
        'deposit/payeer/payment/status',
        'deposit/checkout/payment/success',
        'merchant/api/*',
        'payment/form',
        'payment/payumoney_success',
        'payment/payumoney_fail',
        'dispute/change_reply_status',
        'ticket/change_reply_status',
        'request_payment/cancel',
        'gateway/payment-verify/coinpayments',
        'gateway/payment-verify/stripe',
        'gateway/payment-verify/paypal',
        'gateway/payment-verify/payeer',
        'gateway/payment-verify/payumoney',
        'gateway/payment-verify/coinbase',
        'receive/blockio-balance-change-notification',
        'receive/tatumio-balance-change-notification'
    ];
}
