<?php

namespace Infoamin\Installer\Http\Controllers;

use Illuminate\Http\Request;
use Infoamin\Installer\Helpers\PermissionsChecker;
use Infoamin\Installer\Helpers\RequirementsChecker;
use Infoamin\Installer\Interfaces\PurchaseInterface;
use Illuminate\Support\Facades\Cache;
use Validator;

class PermissionsController extends PermissionsChecker
{

    /**
     * @var PermissionsChecker
     */
    protected $permissions;
    /**
     * @var RequirementsChecker
     */
    protected $requirements;
    /**
     * @param PermissionsChecker $checker && @param RequirementsChecker $requirementschecker
     */
    public function __construct(PermissionsChecker $checker, RequirementsChecker $requirementschecker)
    {
        $this->permissions  = $checker;
        $this->requirements = $requirementschecker;
    }

    /**
     * Display the permissions check page.
     *
     * @return \Illuminate\View\View
     */
    public function checkPermissions()
    {
        $phpSupportInfo = $this->requirements->checkPHPversion(config('installer.core.minimumPhpVersion'));
        $requirements   = $this->requirements->check(config('installer.requirements'));
        $permissions    = $this->permissions->checkPermission(config('installer.permissions'));
        if (!isset($requirements['errors']) && $phpSupportInfo['supported']) {
            return view('vendor.installer.permissions', compact('permissions'));
        } else {
            return redirect('install/requirements');
        }
    }

    /**
     * Display the purchase code verification page.
     *
     * @return \Illuminate\View\View
     */
    public function verifyPurchaseCode(Request $request, PurchaseInterface $purchaseChecker)
    {
        $this->isInstalled();
        if (!$request->isMethod('POST')) {
            return view('vendor.installer.purchasecode', ['old' => $request->old]);
        }

        $validator = Validator::make($request->all(), [
            'envatopurchasecode' => 'required',
            'envatoUsername' => 'required'
        ]);
        $validator->setAttributeNames([
            'envatopurchasecode' => 'Purchase code',
            'envatoUsername' => 'Envato Username'
        ]);

		return redirect('install/database');
    }

    public function isInstalled() {
        if(base64_decode('SU5TVEFMTF9BUFBfU0VDUkVU') && env('APP_INSTALL')) {
            return view('vendor.installer.purchasecode', ['installed' => 'App is already installed']);
        }
    }

    public function clearCache(Request $request) {
		Cache::forget('a_s_k');
		return true;
    }
}
