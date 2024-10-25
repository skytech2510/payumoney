<?php

if (!app()->runningInConsole()) {
    return [
        'name' => 'TatumIo',
        'transaction_types' => (defined('Crypto_Sent') && defined('Crypto_Received')) ? [Crypto_Sent, Crypto_Received] : [],
        'transaction_type_settings' => [
            'web' => [
                'sent' => (defined('Crypto_Sent') && defined('Crypto_Received')) ? [Crypto_Sent, Crypto_Received] : [],
                'received' => [],
            ],
            'mobile' => [
                'sent' => [
                    'Crypto_Sent' => defined('Crypto_Sent') ? Crypto_Sent : '',
                    'Crypto_Received' => defined('Crypto_Received') ? Crypto_Received : '',
                ],
                'received' => []
            ]
        ],

        'transaction_list' => [
            'sender' => (defined('Crypto_Sent') && defined('Crypto_Received') )
                        ? [ Crypto_Sent => 'user', Crypto_Received => 'end_user']
                        : [],
            'receiver' => (defined('Crypto_Sent') && defined('Crypto_Received') )
            ? [ Crypto_Sent => 'end_user', Crypto_Received => 'user']
            : [],
        ]


    ];
} else {
    return [
        'name' => 'TatumIo',
    ];
}

