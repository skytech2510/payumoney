<?php

namespace Modules\TatumIo\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\TatumIo\Class\TatumIoTransaction;
use Modules\TatumIo\Exception\CryptoSendException;

class CryptoSendService
{
    protected $helper;
    protected $currency;
    protected $tatumIo;
    protected $network;


    public function __construct()
    {
        $this->helper = new \App\Http\Helpers\Common();
        $this->currency = new \App\Models\Currency();
    }


    public function cryptoAddressValidation($network, $address, $via='api')
    {
        try {
            $this->setTatumIo($network);
            return $this->tatumIo->checkAddress($address, $via);
        } catch (Exception $e) {
           throw new CryptoSendException($e->getMessage());
        }
    }

    public function userCryptoBalanceCheck($network, $amount, $senderAddress, $receiverAddress, $priority)
    {
        try {

            $this->getNetworkMinLimit($network, $amount);

            $this->setTatumIo($network);

            $this->checkSenderAddress($senderAddress);

            $this->tatumIo->checkAddress($receiverAddress, 'api');

            return $this->setTransactionArray($senderAddress, $receiverAddress, $amount, $priority);

        } catch (Exception $e) {
            throw new CryptoSendException($e->getMessage());
        }

    }


    public function userAddress($network)
    {
        try {

            $this->setTatumIo($network);

            return [
                'senderAddress' => $this->tatumIo->getUserAddress()
            ];
        } catch (Exception $e) {
           throw new CryptoSendException($e->getMessage());
        }

    }

    public function sendCryptoFinal($network, $receiverAddress, $amount, $priority, $senderAddress)
    {
        try {

            $this->setTatumIo($network);

            $this->checkSenderAddress($senderAddress);

            $this->tatumIo->checkAddress($receiverAddress, 'api');


            $cryptoTrx =  $this->setTransactionArray($senderAddress, $receiverAddress, $amount, $priority);

            $cryptoTrx['uniqueCode'] = unique_code();


            $sendResponse = $this->tatumIo->sendCryptoToAddress($receiverAddress, $amount, $priority);

            if (!isset($sendResponse->txId)) {

                $message = isset($sendResponse->cause) ?  $sendResponse->cause : __('Transaction Failed, please try again');
                throw new Exception(__($message));
            }


            DB::beginTransaction();

            $createCryptoTransactionId = $this->tatumIo->createCryptoTransaction($cryptoTrx);

            $cryptoTrx['transactionId'] = $createCryptoTransactionId;
            $cryptoTrx['withdrawInfoData'] = $sendResponse;


            //Create new withdrawal/Send crypt api log
            $cryptoTrx['transactionId'] = $createCryptoTransactionId;
            $cryptoTrx['walletCurrencyCode'] = $network;

            //need this for showing send address against Crypto Receive Type Transaction in user/admin panel
            $cryptoTrx['withdrawInfoData']->network_fee = $cryptoTrx['networkFee'];

            $cryptoTrx['withdrawInfoData']->senderAddress = $senderAddress;
            //need this for nodejs websocket server
            $cryptoTrx['withdrawInfoData']->receiverAddress = $cryptoTrx['receiverAddress'];

            $this->tatumIo->createWithdrawalOrSendCryptoApiLog($cryptoTrx);

            $this->tatumIo->getUpdatedSendWalletBalance($cryptoTrx);

            DB::commit();

            return $cryptoTrx;

        } catch (Exception $e) {
            DB::rollBack();
            throw new CryptoSendException(__($e->getMessage()));
        }

    }


    public function getNetworkMinLimit($network, $amount)
    {
        $minLimit =  getTatumIoMinLimit('amount', $network);

        if ($minLimit > $amount) {
            throw new CryptoSendException(__('The minimum amount must be :x  :y.', ['x' => $minLimit, 'y' => $network]));
        }
        return true;
    }

    public function getCryptoCurrency( $options = ['id', 'symbol', 'status'])
    {
        $currency = $this->currency->getCurrency(['code' => $this->network, 'type' => 'crypto_asset'], $options);

        if ($currency->status !== 'Active') {
            throw new CryptoSendException(__(':x is inactive.', ['x' =>  $this->network]));
        }
        return $currency;
    }

    public function setTatumIo($network)
    {
        $userId = auth()->id();

        $this->network = strtoupper($network);

        $this->tatumIo = new TatumIoTransaction($this->network);

        $this->tatumIo->tatumIoAsset();

        $this->tatumIo->checkUserTatumWallet($userId);

    }

    public function checkSenderAddress($address)
    {
        if ( $address !== $this->tatumIo->getUserAddress() ) {
            throw new Exception(__('Sender Address is not correct'));
        }

        return true;

    }

    public function setTransactionArray($senderAddress, $receiverAddress, $amount, $priority)
    {

        $currency = $this->getCryptoCurrency();


        $networkFees = $this->tatumIo->getEstimatedFees($senderAddress, $receiverAddress, $amount, $priority);

        $userBalance = $this->tatumIo->getUserBalance();

        if ($userBalance < ($amount + $networkFees)) {
            throw new Exception(__('Network fee :x and Amount :y exceeds your :z balance', ['x' => $networkFees, 'y' => $amount, 'z' => strtoupper($this->network), 'b' => $userBalance]));
        }

        if ($senderAddress == $receiverAddress) {
           throw new Exception(__('You can not send :x to your own wallet', ['x' => $this->network]));
        }

        $arr = [
            'receiverAddress' => $receiverAddress,
            'amount' => $amount,
            'networkFee' => $networkFees,
            'senderAddress' => $senderAddress,
            'userId' => auth()->id(),
            'currencyId' => $currency->id,
            'currencySymbol' => $currency->symbol,
            'priority' => $priority,
            'network' => $this->network,
        ];

        $endUserWallet = getReceiverAddressWalletUserId($receiverAddress);

        if (!empty($endUserWallet)) {
            $arr['endUserId'] = optional($endUserWallet->wallet)->user_id;
        } else {
            $arr['endUserId'] = null;
        }

        return $arr;

    }



}
