<?php

/**
 * @package ExchangeMoneyController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 04-12-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\Api\V2\AmountLimitException;
use App\Exceptions\Api\V2\ExchangeMoneyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\ExchangeMoney\{
    ConfirmExchangeDetailsRequest,
    AmountLimitCheckRequest,
    GetDestinationsRequest,
    WalletBalanceRequest,
    ExchangeRateRequest
};
use App\Http\Resources\V2\FeesResource;
use App\Services\ExchangeMoneyService;

class ExchangeMoneyController extends Controller
{
    /**
     * ExchangeMoneyService
     *
     * @var ExchangeMoneyService
     */
    public $service;
    /**
     * Controller constructor
     *
     * @param ExchangeMoneyService $service
     *
     * @return void
     */
    public function __construct(ExchangeMoneyService $service)
    {
        $this->service = $service;
    }


    /**
     * Get available currency of the user
     *
     * @return JsonResponse
     */
    public function getCurrencies()
    {
        try {
            return $this->successResponse($this->service->getSelfCurrencies());
        } catch (ExchangeMoneyException $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        } catch (\Exception $exception) {
            // Common return value for every other exception
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }


    /**
     * Check exchange limit against wallet balance
     *
     * @param AmountLimitCheckRequest $request
     *
     * @return JsonResponse
     */
    public function exchangeLimitCheck(AmountLimitCheckRequest $request)
    {
        try {
            extract($request->only('amount', 'currency_id'));
            $feesDetails =  $this->service->amountLimitCheck($amount, $currency_id);
            return $this->successResponse(new FeesResource($feesDetails));
        } catch (ExchangeMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (AmountLimitException $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }


    /**
     * Get exchange source and destination wallets balance
     *
     * @param WalletBalanceRequest $request
     *
     * @return JsonResponse
     */
    public function getExchangeWalletsBalance(WalletBalanceRequest $request)
    {
        try {
            extract($request->only('from_currency', 'to_currency'));
            return $this->successResponse($this->service->getWallets($from_currency, $to_currency));
        } catch (ExchangeMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }


    /**
     * Get exchangable destination wallets
     *
     * @param GetDestinationsRequest $request
     *
     * @return JsonResponse
     */
    public function getExchangableDestinations(GetDestinationsRequest $request)
    {
        try {
            return $this->successResponse($this->service->getAvailableDestinationWallets($request->currency_id));
        } catch (ExchangeMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (\Exception $exception) {
            return $exception->getMessage();
            // Common return value for every other exception
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }


    /**
     * Get currency exchange rates from
     * source currency to destination currency
     *
     * @return void
     */
    public function getCurrenciesExchangeRate(ExchangeRateRequest $request)
    {
        try {
            extract($request->only(['to_currency_id', 'from_currency_id', 'amount']));
            return $this->successResponse($this->service->getCurrenciesExchangeRates($from_currency_id, $to_currency_id, $amount));
        } catch (ExchangeMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (\Exception $exception) {
            return $exception->getMessage();
            // Common return value for every other exception
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }


    /**
     * Review exchange money information
     *
     * @param ConfirmExchangeDetailsRequest $request
     *
     * @return JsonResponse
     */
    public function reviewExchangeDetails(ConfirmExchangeDetailsRequest $request)
    {
        try {
            extract($request->only(['to_currency_id', 'from_currency_id', 'amount']));
            return $this->successResponse($this->service->reviewExchangeRequest($from_currency_id, $to_currency_id, $amount));
        } catch (ExchangeMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (\Exception $exception) {
            return $exception->getMessage();
            // Common return value for every other exception
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }


    /**
     * Complete exchange money process
     *
     * @param ConfirmExchangeDetailsRequest $request
     *
     * @return JsonResponse
     */
    public function exchangeMoneyComplete(ConfirmExchangeDetailsRequest $request)
    {
        try {
            extract($request->only(['to_currency_id', 'from_currency_id', 'amount']));
            return $this->successResponse($this->service->exchangeMoney($from_currency_id, $to_currency_id, $amount));
        } catch (ExchangeMoneyException $exception) {
            return $this->unprocessableResponse($exception->getData(), $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->unprocessableResponse([], $exception->getMessage());
        }
        return $this->unprocessableResponse([], __("Failed to process the request."));
    }
}
