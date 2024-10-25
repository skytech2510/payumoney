<?php

/**
 * @package AcceptCancelRequestMoneyController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\User\RequestMoneyDetailResource;
use App\Http\Requests\AcceptMoney\{
    CheckAmountLimitRequest,
    StoreRequest
};
use App\Exceptions\Api\V2\{
    AcceptMoneyException,
    AmountLimitException,
    PaymentFailedException,
    CurrencyException,
    FeesException,
    WalletException
};
use App\Services\AcceptMoneyService;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\FeesResource;

class AcceptCancelRequestMoneyController extends Controller
{
    /**
     * Get details of a request payment transaction
     *
     * @param AcceptMoneyService $service
     * @return JsonResponse
     */
    public function details(AcceptMoneyService $service)
    {
        try {
            $requestPayment = $service->details(request('tr_ref_id'));
            return $this->successResponse(new RequestMoneyDetailResource($requestPayment));
        } catch (AcceptMoneyException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Check maximum and minimum limit and wallet balance
     *
     * @param CheckAmountLimitRequest $request
     * @param AcceptMoneyService $service
     * @return JsonResponse
     */
    public function checkAmountLimit(CheckAmountLimitRequest $request, AcceptMoneyService $service)
    {
        try {
            $feesDetails =  $service->checkAmountLimit(
                $request->amount,
                $request->currency_id,
                auth()->id()
            );
            return $this->successResponse(new FeesResource($feesDetails));
        } catch (WalletException | AmountLimitException | FeesException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    /**
     * Accept request money
     * @param AcceptMoneyService $service
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request, AcceptMoneyService $service)
    {
        try {
            return $this->okResponse($service->store(
                $request->tr_id,
                $request->amount,
                auth()->id(),
                $request->currency_id,
                $request->emailOrPhone,
                $request->processed_by
            ));
        } catch (CurrencyException | AcceptMoneyException | PaymentFailedException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Cancel request money by request creator
     * @param AcceptMoneyService $service
     * @return JsonResponse
     */
    public function cancelByCreator(AcceptMoneyService $service)
    {
        try {
            return $this->okResponse($service->cancel(request('tr_id'), auth()->id()));
        } catch (AcceptMoneyException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }

    /**
     * Cancel request money by request receiver
     * @param AcceptMoneyService $service
     * @return JsonResponse
     */
    public function cancelByReceiver(AcceptMoneyService $service)
    {
        try {
            return $this->okResponse($service->cancel(request('tr_id'), auth()->id()));
        } catch (Exception $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }
}
