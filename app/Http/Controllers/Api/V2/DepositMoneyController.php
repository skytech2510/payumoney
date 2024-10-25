<?php

/**
 * @package DepositMoneyController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 21-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\Api\V2\{
    AmountLimitException,
    DepositMoneyException,
    ApiException
};
use App\Http\Requests\Api\V2\DepositMoney\{
    PaymentGatewayConfirmRequest,
    GetPaymentMethodRequest,
    ValidateDepositRequest,
    GetBankDetailsRequest,
    PaymentMethodRequest
};

use App\Services\Gateways\{
    Gateway\PaymentProcessor,
    Stripe\StripeProcessor,
    Gateway\GatewayHandler
};
use App\Services\DepositMoneyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Exception;

class DepositMoneyController extends Controller
{
    /**
     * @var DepositMoneyService
     */
    protected $service;

    public function __construct(DepositMoneyService $service)
    {
        $this->service = $service;
        // Issue gateway processor if required
        if (request("gateway")) {
            GatewayHandler::gatewayIssue(request("gateway"));
        }
    }


    /**
     * Get available currency of the user
     *
     * @return JsonResponse
     */
    public function getCurrencies(): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getSelfCurrencies());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    /**
     * Get available payment method for the currency
     *
     * @return JsonResponse
     */
    public function getPaymentMethod(GetPaymentMethodRequest $reqeust): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getPaymentMethods(
                $reqeust->currency_id,
                $reqeust->currency_type,
                $reqeust->transaction_type
            ));
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    /**
     * Get available bank list
     *
     * @param PaymentMethodRequest $reqeust
     *
     * @return JsonResponse
     */
    public function getBankList(PaymentMethodRequest $reqeust): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getBanklist($reqeust->currency_id));
        } catch (DepositMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    /**
     * Validate depositable data
     *
     * @param ValidateDepositRequest $reqeust
     *
     * @return JsonResponse
     */
    public function validateDepositData(ValidateDepositRequest $reqeust): JsonResponse
    {
        try {
            return $this->successResponse($this->service->validateDepositable(
                $reqeust->currency_id,
                $reqeust->amount,
                $reqeust->payment_method
            ));
        } catch (DepositMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), __("Failed to process the request."));
        } catch (AmountLimitException $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        }
    }


    /**
     * Undocumented function
     *
     * @param ValidateDepositRequest $request
     * @param StripeProcessor $processor
     *
     * @return JsonResponse
     */
    public function stripePaymentInitiate(ValidateDepositRequest $request, StripeProcessor $processor): JsonResponse
    {
        try {
            $totalAmount = $this->service->getTotalAmount($request->amount, $request->currency_id, $request->payment_method_id);
            $processor->setPaymentType("deposit");
            $response = $processor->initiatePayment(array_merge($request->all(), ['total_amount' => $totalAmount]));
            return $this->successResponse($response);
        } catch (ApiException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    /**
     * Confirm deposit payment
     *
     * @param PaymentGatewayConfirmRequest $request
     *
     * @return JsonResponse
     */
    public function paymentConfirm(PaymentGatewayConfirmRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->processGatewayPayment(
                $request->currency_id,
                $request->payment_method_id,
                $request->amount,
            ));

        } catch (ApiException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        }
    }


    /**
     * Get bank details
     *
     * @param GetBankDetailsRequest $reqeust
     *
     * @return JsonResponse
     */
    public function getBankDetails(GetBankDetailsRequest $reqeust): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getBankDetails($reqeust->bank_id));
        } catch (DepositMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }


    /**
     * Get paypal infor
     *
     * @param PaymentMethodRequest $request
     *
     * @return JsonResponse
     */
    public function getPaypalInfo(PaymentMethodRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->getPaypalInfo($request->currency_id, "deposit"));
        } catch (DepositMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
}
