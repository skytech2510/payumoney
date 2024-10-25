<?php

/**
 * @package RegistrationController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 06-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\Api\V2\RegistrationException;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use App\Http\Requests\{
    CheckDuplicatePhoneNumberRequest,
    CheckDuplicateEmailRequest,
    UserStoreRequest
};

use DB, Exception;
use App\Http\Controllers\Controller;

class RegistrationController extends Controller
{
    protected $service;

    public function __construct(RegistrationService $service)
    {
        $this->service = $service;
    }

    /**
     * Check duplicate email during registration
     *
     * @param CheckDuplicateEmailRequest $request
     * @return JsonResponse
     */
    public function checkDuplicateEmail(CheckDuplicateEmailRequest $request)
    {
        return $this->successResponse([
            'status' => true,
            'success' => __("Email Available!")
        ]);
    }

    /**
     * Check duplicate phone number during registration
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkDuplicatePhoneNumber(CheckDuplicatePhoneNumberRequest $request)
    {
        return $this->successResponse([
            'status' => true,
            'success' => __("The phone number is Available!")
        ]);
    }

    /**
     * User Registration
     *
     * @param UserStoreRequest $request
     * @return JsonResponse
    */
    public function registration(UserStoreRequest $request)
    {
        try {
            return $this->successResponse([
                $this->service->userRegistration($request)
            ]);
        } catch (RegistrationException $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }

    }


}
