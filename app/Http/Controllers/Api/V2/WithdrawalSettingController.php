<?php
/**
 * @package WithdrawalSettingController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 19-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\Api\V2\WithdrawalSettingException;
use App\Http\Requests\{
    UpdateWithdrawalSettingRequest,
    StoreWithdrawalSettingRequest,
};
use App\Services\WithdrawalSettingService;
use Exception;
use App\Http\Controllers\Controller;

/**
 * @group  Withdrawal setting
 *
 * API to manage Withdrawal setting
 */
class WithdrawalSettingController extends Controller
{
    /**
     * Get payment setting list by user id
     * Get specific Withdrawal setting if id of the Withdrawal setting is provided
     * @return JsonResponse
     * @throws WithdrawalSettingException
     */
    public function index(WithdrawalSettingService $service)
    {
        try {
            return $this->okResponse($service->list(auth()->id(), request('id')));
        } catch (WithdrawalSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Store Withdrawal setting
     *
     * @param StoreWithdrawalSettingRequest $request
     * @param WithdrawalSettingService $service
     * @return JsonResponse
     * @throws WithdrawalSettingException
     */
    public function store(StoreWithdrawalSettingRequest $request, WithdrawalSettingService $service)
    {
        try {
            $response = $service->store($request->validatedFormRequest());
            if ($response) {
                return $this->createdResponse([], __('The :x has been successfully saved.', ['x' => __('withdrawal setting')]));
            }
        } catch (WithdrawalSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }

    }

    /**
     * Update Withdrawal setting
     *
     * @param UpdateWithdrawalSettingRequest $request
     * @param int $id
     * @param WithdrawalSettingService $service
     * @return JsonResponse
     * @throws WithdrawalSettingException
     */
    public function update(UpdateWithdrawalSettingRequest $request, $id, WithdrawalSettingService $service)
    {
        try {
            $response = $service->update($request->validatedFromRequest(), $id, auth()->id());
            if ($response) {
                return $this->okResponse([], __('The :x has been successfully saved.', ['x' => __('Withdrawal setting')]));
            }
        } catch (WithdrawalSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }

    }

    /**
     * Show Withdrawal setting by id
     *
     * @param int $id
     * @param WithdrawalSettingService $service
     * @return JsonResponse
     * @throws WithdrawalSettingException
     */
    public function show($id, WithdrawalSettingService $service)
    {
        try {
            return $this->successResponse($service->show($id, auth()->id()));
        } catch (WithdrawalSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Delete Withdrawal setting by id
     *
     * @param int $id
     * @param WithdrawalSettingService $service
     * @return JsonResponse
     * @throws WithdrawalSettingException
     */
    public function destroy($id, WithdrawalSettingService $service)
    {
        try {
            $response = $service->delete($id, auth()->user()->id);
            if ($response) {
                return $this->okResponse([], __('The :x has been successfully deleted.', ['x' => __('withdrawal setting')]));
            }
        } catch (WithdrawalSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }

    }

    /**
     * Get available payment methods
     *
     * @param WithdrawalSettingService $service
     * @return JsonResponse
     * @throws WithdrawalSettingException
     */
    public function paymentMethods(WithdrawalSettingService $service)
    {
        try {
            return $this->successResponse($service->paymentMethods());
        } catch (WithdrawalSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * List of crypto currencies
     *
     * @param WithdrawalSettingService $service
     * @return JsonResponse
     * @throws WithdrawalSettingException
     */
    public function cryptoCurrencies(WithdrawalSettingService $service)
    {
        try {
            return $this->successResponse($service->cyptoCurrencies(auth()->id()));
        } catch (WithdrawalSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
}
