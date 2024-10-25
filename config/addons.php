<?php

$adminPrefix = env('ADMIN_PREFIX');

return [
    'name' => 'Addons',

    'options' => [
        ['label' => 'Settings', 'url' => '#']
    ],

    'route_group' => [
        'authenticated' => [
            'admin' => [
                'prefix' => $adminPrefix,
                'middleware' => ['guest:admin', 'locale', 'ip_middleware']
            ],
            'user' => [
                'middleware' => ['guest:users', 'locale', 'twoFa', 'check-user-inactive']
            ]
        ],
        'unauthenticated' => [
            'admin' => [
                'prefix' => $adminPrefix,
                'middleware' => ['no_auth:admin', 'locale', 'ip_middleware']
            ],
            'user' => [
                'middleware' => ['no_auth:users', 'locale']
            ],
        ]
    ],

    'items' => [
        'fv8wtkr1jfc' => 'CryptoExchange',
        'v33hrp9x5en' => 'MobileMoney',
        'azv5h23qhwe' => 'Referral',
        'xnucugaeu6q' => 'Remittance',
        'zlwds4xzwjk' => 'Shop',
        'y6udqa9sf3v' => 'EventTicket',
        'wr6h7efkefa' => 'Agent',
        'f9t4qeh9dmq' => 'PaymentLink',
        'hxrku28ngdp' => 'Woocommerce',
        'mn7hifa2ruq' => 'Investment',
        '8s1i1pwlx3l' => 'PrestaShop',
        'o0pn10eacrt' => 'OpenCart'
    ],

    'items_version' => [
        'CryptoExchange' => '2.1.0',
        'MobileMoney'    => '1.0.0',
        'Referral'       => '1.0.0',
        'Remittance'     => '1.0.0',
        'Shop'           => '1.0.0',
        'EventTicket'    => '1.0.0',
        'Agent'          => '1.0.0',
        'PaymentLink'    => '1.0.0',
        'Woocommerce'    => '2.0.0',
        'Investment'     => '2.1.0',
        'PrestaShop'     => '1.0.0',
        'OpenCart'       => '1.0.0',
    ],

    'cache_keys' => [
        'paymoney_cache-preferences',
        'paymoney_cache-settings'
    ],

    'file_permission' => 755
];
