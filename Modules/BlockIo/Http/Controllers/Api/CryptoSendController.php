<?php

/**
 * @package CryptoSendController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Ashraful Alam <[ashraful.techvill@gmail.com]>
 * @created 06-04-2023
 */

 namespace Modules\BlockIo\Http\Controllers\Api;

use Exception;
use App\Models\CryptoProvider;
use App\Http\Controllers\Controller;
use Modules\BlockIo\Exception\CryptoSendException;
use Modules\BlockIo\Http\Requests\CryptoSendRequest;
use Modules\BlockIo\Providers\CryptoSendService;

class CryptoSendController extends Controller
{

    protected $service;

    public function __construct(CryptoSendService $service)
    {
        $this->service = $service;
    }

    /**
     * Method providerStatus
     * Get Block.Io module status
     *
     * @return void
     */
    public function providerStatus()
    {
        $response['status'] =  CryptoProvider::getStatus('BlockIo');
        return $this->okResponse($response);

    }



    public function userCryptoAddress()
    {
        try {
            $this->service->cryptoWalletCheck(request('walletId'), request('walletCurrencyCode'));
            $response = $this->service->userAddress(request('walletId'));
            return $this->successResponse($response);
        } catch (CryptoSendException $e) {
            return $this->unprocessableResponse([], __($e->getMessage()));
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }

    }


    public function validateCryptoAddress()
    {
        try {
            $response = $this->service->cryptoAddressValidation(request('walletCurrencyCode'), request('receiverAddress'));
            return $this->successResponse($response);
        } catch (CryptoSendException $e) {
            return $this->unprocessableResponse([], __($e->getMessage()));
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

    /**
     * Method validateUserBalanceAgainstAmount
     *
     * @return void
     */
    public function validateUserBalanceAgainstAmount()
    {
        try {
            $response =  $this->service->userCryptoBalanceCheck(request('walletCurrencyCode'), request('amount'), request('senderAddress'), request('receiverAddress'), request('priority'));
            return $this->okResponse($response);
        } catch (CryptoSendException $e) {
            return $this->unprocessableResponse([], __($e->getMessage()));
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }



    public function cryptoSendConfirm(CryptoSendRequest $request)
    {
        try {
            extract($request->only(['receiverAddress', 'amount', 'senderAddress', 'walletCurrencyCode', 'priority']));
            $response =  $this->service->sendCryptoFinal($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress);
            return $this->successResponse($response);
        } catch (CryptoSendException $e) {
            return $this->unprocessableResponse([], __($e->getMessage()));
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }

    }





}


