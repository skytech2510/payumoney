<?php

namespace Modules\Addons\Http\Controllers;

use Modules\Addons\Entities\{AddonManager, Envato, Addon};
use Modules\Addons\Http\Requests\UploadAddonRequest;
use App\Http\Controllers\Controller;
use Artisan, Cache, Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddonsController extends Controller
{
    /**
     * upload
     *
     * @param  mixed $request
     * @return void
     */
    public function upload(UploadAddonRequest $request)
    {
        try {
            if (in_array(explode('.', $request->attachment->getClientOriginalName())[0], array_keys(Addon::all()) )) {
                return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => __('Addon already exist, Uploading again may lose your previous data.')])->withInput();
            }
    
            // Upload addon to temp directory for checking
            $addonInfo = AddonManager::upload($request->attachment);
            if (isset($addonInfo['status']) && $addonInfo['status'] == 201) {
                return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => $addonInfo['message']])->withInput();
            }
            $addon = $addonInfo['addon'];
           
            // has - check the module.json file and and name value exist or not
            if (!Addon::has($addon->get('name'))) {
                $addon->delete();
                return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => __('The uploaded addon is not a valid file.')])->withInput();
            }

            // load the module config file for getting the config value 
            $addon->register();

            // Script Version
            $appVersion = config('paymoney.version');
            // addon supported versions
            $supportedVersion = config($addon->get('alias') . '.' . 'supported_versions');

            if (!($appVersion >= $supportedVersion)) {
                $message = __('This addon is only supported with :x version :y or higher.', ['x' => settings('name'), 'y' => config($addon->get('alias') . '.' . 'supported_versions')]);
                $addon->delete();
                return back()->with(['AddonStatus' => 'fail', 'AddonMessage' =>  $message])->withInput();
            }

            $appSupportedVersion = config('addons.items_version.' . config($addon->get('alias'). '.' . 'name'));

            $moduleVersion = $addon->get('version');
           
            if (!($moduleVersion >= $appSupportedVersion)) {
                $message = __('The current version of the addon is :z. For compatibility with :x :y, you need to have version :a or higher.', ['x' => settings('name'), 'y' => $appVersion, 'z' => $moduleVersion, 'a' => $appSupportedVersion]);
                $addon->delete();
                return back()->with(['AddonStatus' => 'fail', 'AddonMessage' =>  $message])->withInput();
            }
            
            if ($addon->get('name') !== config('addons.items.' . config($addon->get('alias') . '.' . 'item_id'))) { 
                $addon->delete();
                return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => __('The compressed module file does not belong to this script.')])->withInput();
            }

            // Checking if module required other modules
            $requiredModules = config($addon->get('alias') . '.required_modules');
            if (!is_null($requiredModules) && !empty($requiredModules)) {
                foreach ($requiredModules as $requiredModule) {
                    if (is_null(module($requiredModule))) {
                        $addon->delete();
                        return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => __('The module required :addional module to run.', ['addional' => $requiredModule])])->withInput();
                    }
                }
            }
    
            // Required: purchasecode, username, item_id
            $purchaseData = Envato::isValidPurchaseCode($request->purchase_code, $request->username, config($addon->get('alias') . '.' . 'item_id'));
            if (!$purchaseData->status) {
                $addon->delete();
                return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => __($purchaseData->data)])->withInput();
            }

            // Update .env with addon secret key
            if ($purchaseData->status && !is_null($purchaseData->data)) {
                changeEnvironmentVariable(strtoupper($addon->get('name')) . '_SECRET', $purchaseData->data);
                Cache::put(strtoupper($addon->get('name')) . '_SECRET', env(strtoupper($addon->get('name')) . '_SECRET'), 2629746);
            }

            // migrating and seeding of the uploaded addon
            $migrateAndSeedData = AddonManager::migrateAndSeed($addon->get('name'));
            if (isset($migrateAndSeedData['status']) && $migrateAndSeedData['status'] == 402) {
                Artisan::call('module:migrate-rollback ' . $addon->get('name'));
                $addon->delete();
                return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => $migrateAndSeedData['message']])->withInput();
            }

            $cacheKeys = config('addons.cache_keys');
            if (!is_null($cacheKeys) && !empty($cacheKeys)) {
                foreach ($cacheKeys as $cacheKey) {
                    Cache::forget($cacheKey);
                }
            }
           
            return back()->with(['AddonStatus'=> 'success', 'AddonMessage' => __('Addon has been uploaded successfully.')]);

        } catch (Exception $e) {
            Artisan::call('module:migrate-rollback ' . $addon->get('name'));
            $addon->delete();
            return back()->with(['AddonStatus'=> 'fail', 'AddonMessage' => $e->getMessage()])->withInput();
        }
    }
    /**
     * switchStatus
     *
     * @param  mixed $alias
     * @return void
     */
    public function switchStatus($alias)
    {
        $addon = Addon::find($alias);

        if (is_null($addon)) {
            return back()->with(['fail'=> 'success', 'AddonMessage' => __('Addon not found')]);
        }

        $addon->isEnabled() ? $addon->disable() : $addon->enable();

        return back()->with(['AddonStatus'=> 'success', 'AddonMessage' => __('Addon status updated.')]);
    }

    public function verifyForm($name)
    {
        return view('admin.module_manager.purchasecode', ['name' => $name]);
    }

    public function verifyUsernamePurchasecode(Request $request, $name)
    {
        $addon = Addon::find(strtolower($name));
        $itemId = config($addon->get('alias') . '.' . 'item_id');

        $purchaseData = Envato::isValidPurchaseCode($request->purchase_code, $request->username, $itemId);

        if ($purchaseData->status && !is_null($purchaseData->data)) {
            changeEnvironmentVariable(strtoupper($addon->get('name')) . '_SECRET', $purchaseData->data);
            Cache::put(strtoupper($addon->get('name')) . '_SECRET', env(strtoupper($addon->get('name')) . '_SECRET'), 2629746);
            return redirect(config('adminPrefix') . '/module-manager/addons')->with(['AddonStatus'=> 'success', 'AddonMessage' => __('Addon successfully varified.')]);
        }

        return redirect()->back()->with(['AddonStatus'=> 'error', 'AddonMessage' => $purchaseData->data]);
    }
}
