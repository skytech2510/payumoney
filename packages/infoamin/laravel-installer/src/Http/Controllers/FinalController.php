<?php

namespace Infoamin\Installer\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use AppController;
use Artisan;

class FinalController extends AppController
{
    /**
     * Complete the installation
     *
     * @return \Illuminate\View\View
     */
    public function finish()
    {
        changeEnvironmentVariable('APP_DEBUG', false);
        changeEnvironmentVariable('APP_INSTALL', 'true');
        Cache::put('a_s_k', env(base64_decode('SU5TVEFMTF9BUFBfU0VDUkVU')), 2629746);

        // Change key in .env
        Artisan::call('key:generate');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return view('vendor.installer.finish');
    }
}
