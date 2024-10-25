<?php

/**
 * @package LoginController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 30-11-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\User\UserLoginResource;
use App\Exceptions\Api\V2\LoginException;
use App\Http\Requests\UserLoginRequest;
use App\Services\AuthService;
use Carbon\Carbon;
use App\Models\{
    ActivityLog,
    UserDetail
};
use Exception;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * User Login
     * @param UserLoginRequest $request
     * @param AuthService $service
     * @return JsonResponse
     * @throws LoginException
     */
    public function login(UserLoginRequest $request, AuthService $service)
    {
        try {
            $response = $service->login($request->email, $request->password);
            (new ActivityLog())->createActivityLog($response['id'], 'User', $request->ip(), $request->header('user-agent'));
            (new UserDetail())->updateUserLoginInfo($response, Carbon::now()->toDateTimeString(), $request->getClientIp());
            return $this->successResponse(new UserLoginResource($response));
        } catch (LoginException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

}
