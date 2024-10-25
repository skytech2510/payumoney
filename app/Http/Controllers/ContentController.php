<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Controller;
use App\Models\{Meta,
    Pages
};

class ContentController extends Controller
{
    protected $data = [];

    public function pageDetail($url)
    {
        $data['menu'] = 'deposit';
        if ($url == 'developer') {
            $data['pageInfo'] = 'Developer';
            $data['exceptionMeta'] = Meta::where('url', $url)->first();

            if(!is_null(request()->type)) {
                $type = strtolower(request()->type);
                $data['menu'] = $type;
                $defaultPage = ['standard', 'express'];

                $pluginNameSetting = $type.'_plugin_name';
                $publicationStatusSetting = $type.'_publication_status';

                if (!empty(settings($pluginNameSetting))) {
                    $data['plugin_name'] = settings($pluginNameSetting);
                }

                if (!empty(settings($publicationStatusSetting))) {
                    $data['publication_status'] = settings($publicationStatusSetting) ;
                }

                if(in_array($type, $defaultPage)) {
                    return view('frontend.pages.'.$type, $data);
                } else {
                    if (view()->exists($type. '::frontend.pages.' . $type)) {
                        return view($type. '::frontend.pages.' . $type, $data);
                    } else {
                        abort(404);
                    }
                }
            } else {
                $data['menu'] = 'standard';
                return view('frontend.pages.standard', $data);
            }
        } else {
            $info = Pages::where(['url' => $url])->first();
            if (empty($info)) {
                abort(404);
            }
            $data['pageInfo']  = $info;
            $data['exceptionMeta'] = Meta::where('url', $url)->first();
            $data['menu']      = $url;
            return view('frontend.pages.detail', $data);
        }
    }

    public function downloadPackage()
    {
        return response()->download(\Storage::disk('local')->path('paymoney_sdk.zip'));
    }
}
