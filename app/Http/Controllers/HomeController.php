<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{App,
    Session
};
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\Language;
use Ramsey\Uuid\Uuid;

class HomeController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index()
    {
        $data         = [];
        $data['menu'] = 'home';
        return view('frontend.home.index', $data);
    }

    public function privacyPolicy()
    {
        $data = [];
        $data['menu'] = 'privacy-policy';
        return view('frontend.home.privacy', $data);
    }

    public function setLocalization(Request $request)
    {
        // Get the list of active language short names
       $langShotCode = Language::where('status', 'active')->pluck('short_name')->toArray();

        // Check if the requested language is in the list of active languages and if the request is an AJAX request
        if ($request->ajax() && in_array($request->lang, $langShotCode, true)) {
            // Set the default language in the session
            Session::put('dflt_lang', $request->lang);
            return 1;
        }

        // If the requested language is not valid or the request is not an AJAX request, return 0
        return 0;
    }
}
