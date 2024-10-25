<?php
/**
 * @package PreferenceController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 27-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\PreferenceResource;
use App\Http\Controllers\Controller;
use App\Models\Role;

class PreferenceController extends Controller
{
    /**
     * Check Login via preference
     *
     * @return JsonResponse
     */
    public function checkLoginVia()
    {
        $success['loginVia'] = settings('login_via');
        return $this->okResponse($success);
    }

    /**
     * Method by which send/request money will proceed through
     *
     * @return JsonResponse
     */
    public function checkProcessedByApi()
    {
        $success['processedBy'] = preference('processed_by');
        return $this->okResponse($success);
    }

    /**
     * Get system preferences
     *
     * @return JsonResponse
     */
    public function preferenceSettings()
    {
        return $this->okResponse(new PreferenceResource(null));
    }

    /**
     * Get custom preferences
     *
     * @return JsonResponse
     */
    public function customSetting()
    {
        $response['payment_methods']   = getPaymoneySettings('payment_methods')['mobile'];
        $response['transaction_types'] = getPaymoneySettings('transaction_types')['mobile'];
        return $this->okResponse($response);
    }

    /**
     * Get user roles
     *
     * @return JsonResponse
     */
    public function userRoles()
    {
        $response['types'] = (new Role())->availableUserRoles();
        return $this->okResponse($response);
    }

    

}
