<?php

namespace Modules\Upgrader\Entities\Handler;

class View
{
    /**
     * The view of upgrade process
     *
     * @var string
     */
    public function view($redirectTo, $data = [])
    {
        return  '<html>
                <head>
                    <meta http-equiv="refresh" content="3;'. $redirectTo .'" />
                <title>' . __("Application Upgrade") . '...</title>
                </head>
                <body>
                    <p>' . __("Upgrade is in progress, please wait") . '...</p>
                </body>
                </html>';
    }
}