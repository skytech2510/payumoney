<?php

namespace Modules\BlockIo\Providers;

use Exception;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Modules\BlockIo\Classes\BlockIo;
use Modules\BlockIo\Exception\CryptoSendException;

class CryptoSendService
{
    protected $helper;
    protected $currency;
    protected $blockIo;


    public function __construct()
    {
        $this->helper = new \App\Http\Helpers\Common();
        $this->currency = new \App\Models\Currency();
        $this->blockIo = new BlockIo;
    }


    public function cryptoAddressValidation($currencyCode, $address)
    {
        $userId = auth()->user()->id;

        $response =  $this->blockIo->addressValidityCheck($currencyCode, $address, $userId);

        $this->processResponse($response);

        return true;

    }

    public function userCryptoBalanceCheck($code, $amount, $senderAddress, $receiverAddress, $priority)
    {

        $this->getNetworkMinLimit($code, $amount);

        $response =  $this->blockIo->userBalanceCheck($code, $amount, $senderAddress, $receiverAddress, $priority);

        $this->processResponse($response);

       return  $response->getData(true);

    }

    public function minimumSendAmountCheck($walletCurrencyCode, $amount)
    {
        $response = $this->blockIo->minimumAmountCheck($walletCurrencyCode, $amount);

        $this->processResponse($response);

        return $response;

    }


    public function userAddress($walletId)
    {
        $response = $this->blockIo->getUserCryptoAddress($walletId);

        return [
            'senderAddress' => $response
        ];

    }

    public function cryptoSendPreview($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress)
    {
        $currency = $this->currency->getCurrency(['code' => $walletCurrencyCode], ['id', 'symbol']);
        $this->cryptoAddressValidation($walletCurrencyCode, $receiverAddress);
        $this->minimumSendAmountCheck($walletCurrencyCode, $amount);
        $this->userCryptoBalanceCheck($walletCurrencyCode, $amount, $senderAddress, $receiverAddress, $priority);
        return $this->getCryptoTrxData($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress, $currency);
    }

    public function sendCryptoFinal($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress)
    {
        try {
            $cryptoTrx =  $this->cryptoSendPreview($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress);
            $cryptoTrx['walletCurrencyCode'] = $walletCurrencyCode;
            $extensionCheck = $this->blockIo->extensionCheck();
            $withdrawInfoData  = $this->sendProcess($walletCurrencyCode, $cryptoTrx);
            $withdrawInfoData['senderAddress'] = $cryptoTrx['senderAddress'];
            $withdrawInfoData['receiverAddress'] = $cryptoTrx['receiverAddress'];
            $cryptoTrx['withdrawInfoData'] =  $withdrawInfoData;

            DB::beginTransaction();
            $cryptoTrx['transactionId'] = $this->blockIo->createCryptoTransaction($cryptoTrx);
            $this->blockIo->createWithdrawalOrSendCryptoApiLog($cryptoTrx);
            $this->blockIo->getUpdatedSendWalletBalance($cryptoTrx);
            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw new CryptoSendException(__($e->getMessage()));
        }

        return $cryptoTrx;

    }


    public function getCryptoTrxData($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress, $currency)
    {
        $userId = auth()->user()->id;
        $response = $this->blockIo->cryptoTrxData($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress, $userId, $currency);
        return $response;
    }

    /**
     * Method sendProcess
     *
     * @param $walletCurrencyCode $walletCurrencyCode [explicite description]
     * @param $cryptoTrx $cryptoTrx [explicite description]
     *
     * @return void
     */
    public function sendProcess($walletCurrencyCode, $cryptoTrx)
    {
        $response = $this->blockIo->cryptoSendProcess($walletCurrencyCode, $cryptoTrx);
        $this->processResponse($response);
        $data = $response->getData(true);
        $responseData = $data['data']['data'];
        return $responseData;
    }


    /**
     * Method processResponse
     *
     * @param $response $response [explicite description]
     * To check if the response have any error issue
     *
     * @return void
     */
    public function processResponse($response)
    {
        $jsonResponse = $response->getData(true);

        if ($jsonResponse['status'] !== 200) {
            throw new CryptoSendException(__($jsonResponse['message']));
        }

        return true;
    }

    /**
     * Method cryptoWalletCheck
     *
     * @param $walletId $walletId [explicite description]
     * @param $walletCurrencyCode $walletCurrencyCode [explicite description]
     *
     * @return void
     */
    public function cryptoWalletCheck($walletId, $walletCurrencyCode)
    {
        $userId = auth()->user()->id;

        $wallet = Wallet::whereHas('currency', function ($q) {
            $q->where(['type' => 'crypto_asset']);
        })
            ->with(['currency:id,code,status'])
            ->where(['user_id' => $userId, 'id' => $walletId])
            ->first(['id', 'currency_id', 'is_default', 'balance']);

        if (empty($wallet)) {
            throw new CryptoSendException(__(':x wallet not associated to the user.', ['x' => $walletId]));
        }

        if (optional($wallet->currency)->code !== $walletCurrencyCode) {
            throw new CryptoSendException(__(':x wallet is not available for this user.', ['x' => $walletCurrencyCode]));
        }

        if (optional($wallet->currency)->status == 'Inactive') {
            throw new CryptoSendException(__(':x is inactive.', ['x' => $walletCurrencyCode]));
        }

        return true;
    }

    public function getNetworkMinLimit($network, $amount)
    {
        $minLimit =  getBlockIoMinLimit('amount', $network);

        if ($minLimit > $amount) {
            throw new CryptoSendException(__('The minimum amount must be :x  :y.', ['x' => $minLimit, 'y' => $network]));
        }
        return true;
    }



}
