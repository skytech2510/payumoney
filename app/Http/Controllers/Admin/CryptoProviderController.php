<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoProvider;
use Illuminate\Http\Request;

class CryptoProviderController extends Controller
{
    public function index($provider = null)
    {
        $data['menu'] = 'crypto_providers';
        $data['selected_provider'] = $provider;

        // If module name not matched
        if (is_null($provider)){
            return redirect()->back();
        }

        $data['providers'] = CryptoProvider::get();

        $data['provider'] = CryptoProvider::with([
            'cryptoAssetSettings:id,currency_id,crypto_provider_id,network,network_credentials,status', 
            'cryptoAssetSettings.currency:id,name,type,code,symbol,logo,status'
        ])
        ->where('alias', $provider)
        ->first();

        return view(strtolower($provider) . '::admin.crypto_provider.' . strtolower($provider), $data);
    }

    public function statusChange(Request $request, $provider)
    {
        $allProviders = CryptoProvider::all(['name']);
        $status = $request->provider_status == 'true' ? 'Active' : 'Inactive';
        
        if ($allProviders->contains('name', $provider)) {
            CryptoProvider::where('name', $provider)->update(['status' => $status]);
            return response()->json([
                'status'  => 200,
                'message' => __('The :x has been successfully saved.', ['x' => $provider . ' ' . __('status')]),
            ]);
        } else {
            return response()->json([
                'status'  => 400,
                'message' => __('The :x does not exist.', ['x' => __('provider')])
            ]);
        }
    }
}
