<?php
/**
 * @package PayoutSettingController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 19-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\Api\V2\PayoutSettingException;
use App\Http\Requests\{
    UpdatePayoutSettingRequest,
    StorePayoutSettingRequest,
};
use App\Services\PayoutSettingService;
use Exception;
use App\Http\Controllers\Controller;

/**
 * @group  Payout setting
 * 
 * API to manage payout setting
 */
class PayoutSettingController extends Controller
{
    /**
     * Get payment setting list by user id
     * Get specific payout setting if id of the payout setting is provided
     * @return JsonResponse
     * @throws PayoutSettingException
     */
    public function index(PayoutSettingService $service)
    {
        try {
            $response = $service->list(auth()->user()->id, request('id'));
            return $this->okResponse($response);
        } catch (PayoutSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Store Payout setting
     *
     * @param StorePayoutSettingRequest $request
     * @param PayoutSettingService $service
     * @return JsonResponse
     * @throws PayoutSettingException
     */
    public function store(StorePayoutSettingRequest $request, PayoutSettingService $service)
    {
        try {
            $response = $service->store($request->validatedFromRequest());
            if ($response) {
                return $this->createdResponse([], __('The :x has been successfully saved.', ['x' => __('payout setting')]));
            }
        } catch (PayoutSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            // Common return value for every other exception
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }

    /**
     * Update Payout setting
     *
     * @param UpdatePayoutSettingRequest $request
     * @param int $id
     * @param PayoutSettingService $service
     * @return JsonResponse
     * @throws PayoutSettingException
     */
    public function update(UpdatePayoutSettingRequest $request, $id, PayoutSettingService $service)
    {
        try {
            $response = $service->update($request->validatedFromRequest(), $id, auth()->user()->id);
            if ($response) {
                return $this->okResponse([], __('The :x has been successfully saved.', ['x' => __('payout setting')]));
            }
        } catch (PayoutSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            // Common return value for every other exception
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }

    /**
     * Show payout setting by id
     *
     * @param int $id
     * @param PayoutSettingService $service
     * @return JsonResponse
     * @throws PayoutSettingException
     */
    public function show($id, PayoutSettingService $service)
    {
        try {
            $response = $service->show($id, auth()->user()->id);
            return $this->successResponse($response);
        } catch (PayoutSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Delete payout setting by id
     *
     * @param int $id
     * @param PayoutSettingService $service
     * @return JsonResponse
     * @throws PayoutSettingException
     */
    public function destroy($id, PayoutSettingService $service)
    {
        try {
            $response = $service->delete($id, auth()->user()->id);
            if ($response) {
                return $this->okResponse([], __('The :x has been successfully deleted.', ['x' => __('payout setting')]));
            }
        } catch (PayoutSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            // Common return value for every other exception
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }

    /**
     * Get available payment methods
     *
     * @param PayoutSettingService $service
     * @return JsonResponse
     * @throws PayoutSettingException
     */
    public function paymentMethods(PayoutSettingService $service)
    {
        try {
            $response = $service->paymentMethods();
            return $this->successResponse($response);
        } catch (PayoutSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * List of crypto currencies
     *
     * @param PayoutSettingService $service
     * @return JsonResponse
     * @throws PayoutSettingException
     */
    public function cryptoCurrencies(PayoutSettingService $service)
    {
        try {
            $response = $service->cyptoCurrencies(auth()->user()->id);
            return $this->successResponse($response);
        } catch (PayoutSettingException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
}
