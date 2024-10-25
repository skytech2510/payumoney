<?php

namespace Infoamin\Installer\Http\Controllers;

use AppController;

class WelcomeController extends AppController
{
    /**
     * Display the installer welcome page.
     *
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        return view('vendor.installer.welcome');
    }

}