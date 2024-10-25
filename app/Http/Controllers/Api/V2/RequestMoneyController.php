<?php

/**
 * @package RequestMoneyController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Http\Controllers\Api\V2;
use App\Http\Requests\RequestMoney\{
    EmailCheckRequest,
    PhoneCheckRequest,
    StoreRequest
};
use App\Exceptions\Api\V2\{
    RequestMoneyException,
    CurrencyException,
    PaymentFailedException
};
use App\Models\Wallet;
use App\Services\RequestMoneyService;
use Exception;
use App\Http\Controllers\Controller;

class RequestMoneyController extends Controller
{
    /**
     * Check email for request money
     * @param EmailCheckRequest $request
     * @param RequestMoneyService $service
     * @return JsonResponse
     */
    public function checkEmail(EmailCheckRequest $request, RequestMoneyService $service)
    {
        try {
            $response = $service->checkRequestSenderEmail(auth()->user()->id, request('receiverEmail'));
            if ($response) {
                return $this->okResponse([], __("Email is ok"));
            }
        } catch (RequestMoneyException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        }
        return $this->unprocessableResponse([], __("Email validation failed."));
    }

    /**
     * Check phone number for request money
     * @param PhoneCheckRequest $request
     * @param RequestMoneyService $service
     * @return JsonResponse
     */
    public function checkPhone(PhoneCheckRequest $request, RequestMoneyService $service)
    {
        try {
            $response = $service->checkRequestSenderPhone(auth()->user()->id, request('receiverPhone'));
            if ($response) {
                return $this->okResponse([], __("Phone number is ok"));
            }
        } catch (RequestMoneyException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        }
        return $this->unprocessableResponse([], __("Phone number validation failed."));
    }

    /**
     * Get available currencies for request money
     * @param RequestMoneyService $service
     * @return JsonResponse
     */
    public function getCurrency(RequestMoneyService $service)
    {
        try {
            $response['defaultWallet'] = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->value('currency_id');
            $response['currencies']    = $service->getCurrencies();
            if (0 == count($response['currencies'])) {
                return $this->notFoundResponse(__("No currency found!"));
            }
            return $this->successResponse($response);
        } catch (RequestMoneyException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }

    /**
     * Store request money
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request, RequestMoneyService $service)
    {
        try {
            $emailOrPhone = request('emailOrPhone');
            $amount       = request('amount');
            $currency_id  = request('currencyId');
            $note         = request('note');
            $uid          = auth()->user()->id;
            $processedBy  = preference('processed_by');
            $response = $service->store($emailOrPhone, $amount, $currency_id, $note, $uid, $processedBy);
            return $this->successResponse($response);
        } catch (CurrencyException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        }  catch (RequestMoneyException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (PaymentFailedException $e) {
            return $this->unprocessableResponse($e->getDecodedMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }
}
