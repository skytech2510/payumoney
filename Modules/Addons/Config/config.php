<?php

return [
    'name' => 'Addons',

    'options' => [
        ['label' => 'Settings', 'url' => '#']
    ],

    // set the route group array
    'route_group' => [
        'prefix' => 'admin',
        'middleware' => [
            'guest:admin', 'locale', 'ip_middleware'
        ]
    ],
    'file_permission' => 755,

    'items' => [
        'unique-key' => 'addon-name',
    ],
];