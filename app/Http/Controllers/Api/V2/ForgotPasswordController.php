<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Exception;
use App\Exceptions\Api\V2\ForgotPasswordException;
use App\Http\Requests\Auth\{ForgotPasswordRequest,
    NewPasswordRequest,
    VerifyCodeRequest
};
use App\Services\ForgotPasswordService;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset code and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
     */

    /**
     * ForgotPasswordService
     *
     * @var ForgotPasswordService
     */

    public $service;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ForgotPasswordService $service)
    {
        $this->service = $service;
    }

    public function forgetPassword(ForgotPasswordRequest $request)
    {
        try {
            return $this->successResponse($this->service->resetCode($request->email));
        } catch (ForgotPasswordException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    public function verifyResetCode(VerifyCodeRequest $request)
    {
        try {
            return $this->successResponse($this->service->verifyCode($request->code, $request->email));
        } catch (ForgotPasswordException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    public function confirmNewPassword(NewPasswordRequest $request)
    {
        try {
            return $this->successResponse($this->service->confirmPassword($request->code, $request->email, $request->password));
        } catch (ForgotPasswordException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
}
