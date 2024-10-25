<?php

namespace Modules\TatumIo\Class;

use App\Http\Helpers\Common;
use App\Models\CryptoAssetApiLog;
use App\Models\CryptoAssetSetting;
use Exception;
use Illuminate\Support\Facades\Http;

class TatumIoTransaction
{
    protected $network;
    protected $cryptoNetwork;
    protected $userCryptoWallet;
    protected $networkCredentials;

    public function __construct($network)
    {
        $this->network = $network;
    }

    public function tatumIoAsset()
    {
        $tatumAssetSetting = (new CryptoAssetSetting())->where(
            [
                'network' => $this->network,
                'payment_method_id' => TatumIo,
            ]
        )->first();

        if (empty($tatumAssetSetting)) {
            throw new Exception(__(':x crypto asset is not found', ['x' => $this->network]));
        }

        $this->networkCredentials = json_decode($tatumAssetSetting->network_credentials);

        $this->cryptoNetwork = new CryptoNetwork($this->networkCredentials->api_key, $this->network);

        return $tatumAssetSetting;
    }

    public function getMerchantAddress()
    {
        return $this->networkCredentials->address;
    }

    public function getMerchantPrivateKey()
    {
        return $this->networkCredentials->key;
    }

    public function getMerchantApiKey()
    {
        return $this->networkCredentials->api_key;
    }

    public function getMerchantBalance()
    {
        return $this->cryptoNetwork->getBalanceOfAddress($this->getMerchantAddress());
    }

    public function getTransactionByHash($arg_hash)
    {
        $url = "https://api.tatum.io/v3/" . $this->cryptoNetwork->networkName() . "/transaction/" . $arg_hash;
        $response = Http::withHeaders([
            "x-api-key" => $this->getMerchantApiKey(),
        ])->get($url);

        $response = json_decode($response);

        return $response;
    }

    public function checkUserTatumWallet($user_id)
    {
        $user = \App\Models\User::find($user_id, ['id']);
        $walletArr = [];
        foreach ($user->wallets as $wallet) {
            $walletArr[] = $wallet->id;
        }

        $walletApiLog = (new CryptoAssetApiLog())->where([
            'payment_method_id' => TatumIo,
            'object_type' => 'wallet_address',
            'network' => $this->network,
        ])->whereIn('object_id', $walletArr)->first('payload');

        if (empty($walletApiLog)) {
            throw new Exception(__(':x crypto wallet not available for the user', ['x' => $this->network]));
        }

        $this->userCryptoWallet = json_decode($walletApiLog->payload);
    }

    public function getUserPrivateKey()
    {
        return $this->userCryptoWallet->key;
    }

    public function getUserAddress()
    {
        return $this->userCryptoWallet->address;
    }

    public function getUserBalance()
    {
        return $this->cryptoNetwork->getBalanceOfAddress($this->getUserAddress());
    }

    public function getAddressBalance($address)
    {
        return $this->cryptoNetwork->getBalanceOfAddress($address);
    }

    public function transactionView($hash)
    {
        if (in_array($this->cryptoNetwork->networkName(), ['dogecoin', 'ethereum', 'tron'])) {

            return $this->cryptoNetwork->transactionDetails($hash);

        }

        $url = "https://api.tatum.io/v3/".$this->cryptoNetwork->networkName()."/transaction/". $hash;

        $response = Http::withHeaders([
            "x-api-key" => $this->getMerchantApiKey(),
        ])->get($url);

        $response = json_decode($response);

        $senderAddress = $response->outputs[0]->address;
        $receiverAddress = $response->outputs[1]->address;
        $networkFee = $response->fee;

        return  [
            'senderAddress' => $senderAddress,
            'receiverAddress' => $receiverAddress,
            'network_fee' => $networkFee,
        ];
    }

    public function getEstimatedFees($sender, $receiver, $amount, $priority)
    {
        $chain = str_replace('TEST', '', $this->network);

        try {

            if ($chain == 'ETH' || $chain ==  'TRX') {
                return $this->cryptoNetwork->getEstimateFees($sender, $receiver, $amount, $priority);
            }

            $url = "https://api.tatum.io/v3/blockchain/estimate";
            $apiKey = $this->getMerchantApiKey();
            $payload = array(
                "chain" => $chain,
                "type" => "TRANSFER",
                "fromAddress" => array(
                    $sender,
                ),
                "to" => array(
                    array(
                        "address" => $receiver,
                        "value" => floatval($amount),
                    ),
                ),
            );

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->post($url, $payload);

            if ($response->failed()) {
                $response = json_decode($response);
                throw new Exception($response->message);
            }
            $response = (array) json_decode($response);
            return $response[$priority];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function sendCryptoAdminToUser($amount, $priority)
    {
        return $this->cryptoSendProcess($this->getMerchantAddress(), $this->getMerchantPrivateKey(), $this->getUserAddress(), $amount, $priority);
    }

    public function sendCryptoUserToAdmin($amount, $priority)
    {
        return $this->cryptoSendProcess($this->getUserAddress(), $this->getUserPrivateKey(), $this->getMerchantAddress(), $amount, $priority);
    }

    public function sendCryptoToAddress($receiver, $amount, $priority)
    {
        return $this->cryptoSendProcess($this->getUserAddress(), $this->getUserPrivateKey(), $receiver, $amount, $priority);
    }

    public function cryptoSendProcess($sender, $key, $receiver, $amount, $priority)
    {

        $chain = str_replace('TEST', '', $this->network);

        if ($chain == 'ETH' || $chain ==  'TRX') {
            return $this->cryptoNetwork->makeTransaction($sender, $key, $receiver, $amount, $priority);
        }

        $payload = [
            "fromAddress" => [
                [
                    "address" => $sender,
                    "privateKey" => $key,
                ],
            ],
            "to" => [
                [
                    "address" => $receiver,
                    "value" => floatval($amount),
                ],
            ],
            "fee" => $this->getEstimatedFees($sender, $receiver, $amount, $priority),
            "changeAddress" => $sender,
        ];

        $url = "https://api.tatum.io/v3/" . $this->cryptoNetwork->networkName() . "/transaction";

        $api_key = $this->getMerchantApiKey();

        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "x-api-key" => $api_key,
        ])->post($url, $payload);

        return json_decode($response);
    }

    public function checkAddress($address, $via='web')
    {
        try {
            $response = $this->cryptoNetwork->getBalanceOfAddress($address);

            return response()->json([
                'status' => 200,
                'data' => $response,
                'message' => __('This is a valid :x address', ['x' => $this->network])
            ]);
        } catch (Exception $e) {
            
            if ($via=='api') {
                throw new Exception($e->getMessage());
            }

            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function createCryptoTransaction($arr)
    {
        $transaction = new \App\Models\Transaction();
        $transaction->user_id = $arr['userId'];
        $transaction->end_user_id = $arr['endUserId'];
        $transaction->currency_id = $arr['currencyId'];
        $transaction->payment_method_id = TatumIo;
        $transaction->uuid = $arr['uniqueCode'];
        $transaction->transaction_type_id = (isset($arr['transactionType']))  ?  $arr['transactionType'] :  Crypto_Sent;
        $transaction->subtotal = $arr['amount'];
        $transaction->total = (isset($arr['transactionType']) && ($arr['transactionType'] == Crypto_Received) ) ? $arr['amount'] : "-" . ($arr['amount']);
        $transaction->status = (isset($arr['status'])) ? $arr['status'] : 'Pending';
        $transaction->save();
        return $transaction->id;
    }

    public function createWithdrawalOrSendCryptoApiLog($arr)
    {
        $cryptoApiLog = new CryptoAssetApiLog();
        $cryptoApiLog->payment_method_id = TatumIo;
        $cryptoApiLog->object_id = $arr['transactionId'];
        $cryptoApiLog->object_type = (isset($arr['object_type'])) ? $arr['object_type'] : 'crypto_sent';
        $cryptoApiLog->confirmations = (isset($arr['confirmations'])) ? $arr['confirmations'] : 0;
        $cryptoApiLog->network = $arr['walletCurrencyCode'];
        $cryptoApiLog->payload = json_encode($arr['withdrawInfoData']);
        $cryptoApiLog->save();

        return $cryptoApiLog;
    }

    public function getUpdatedSendWalletBalance($arr)
    {
        // updating of merchant network address balance will NOT be done in the system
        // update user network address balance
        $getUserCryptoAddressBalance = self::getUserBalance();
        $senderWallet = (new Common)->getUserWallet([], ['user_id' => $arr['userId'], 'currency_id' => $arr['currencyId']], ['id', 'balance']);
        $senderWallet->balance = $getUserCryptoAddressBalance;
        $senderWallet->save();
    }

    public function getCryptoPayloadConfirmationsDetails($transaction_type_id, $payload, $confirmations)
    {
        $arr = [];
        if (!empty($payload)) {
            if ($transaction_type_id == Crypto_Sent || $transaction_type_id == Crypto_Received) {

                $payloadJson = json_decode($payload, true);
                if (isset($payloadJson['senderAddress'])) {
                    $arr['senderAddress'] = $payloadJson['senderAddress'];
                }

                if (isset($payloadJson['receiverAddress'])) {
                    $arr['receiverAddress'] = ($transaction_type_id == Crypto_Sent) ? $payloadJson['receiverAddress'] : $payloadJson['address'];
                }
                if (isset($payloadJson['network_fee'])) {
                    $arr['network_fee'] = isset($payloadJson['network_fee']) ? $payloadJson['network_fee'] : 0.00000000;
                }
                $arr['txId'] = $payloadJson['txId'];
                $arr['confirmations'] = $confirmations;
            }
        }
        return $arr;
    }

    public function getReceiverAddressWalletUserId($receiverAddress)
    {
        return (new CryptoAssetApiLog())
            ->with(['wallet:id,user_id', 'transaction:id'])
            ->where(['payment_method_id' => TatumIo, 'object_type' => 'wallet_address'])
            ->whereJsonContains('payload->address', $receiverAddress)
            ->first('object_id');
    }

    public function createCryptoWalletLog($walletId, $userId, $network)
    {
        try {
            $getTatumAssetApiLog = (new CryptoAssetApiLog())->getCryptoAssetapiLog(['payment_method_id' => TatumIo, 'object_id' => $walletId, 'object_type' => 'wallet_address', 'network' => $network], ['id']);
            if (empty($getTatumAssetApiLog)) {
                $tatumAddress = $this->cryptoNetwork->generateAddress($this->networkCredentials->xpub, $userId);
                $tatumKey = $this->cryptoNetwork->generateAddressPrivateKey($userId,  $this->networkCredentials->mnemonic);
                $tatumBalance =  $this->cryptoNetwork->getBalanceOfAddress($tatumAddress['address']);
                $this->cryptoNetwork->createSubscription($tatumAddress['address']);

                $tatumNetworkArray = [];

                $tatumNetworkArray['address'] = $tatumAddress['address'];
                $tatumNetworkArray['key'] = isset($tatumKey['key']) ? $tatumKey['key'] : '';
                $tatumNetworkArray['balance'] =  $tatumBalance;
                $tatumNetworkArray['user_id'] =  $userId;
                $tatumNetworkArray['wallet_id'] =  $walletId;
                $tatumNetworkArray['network'] =  $network;

                //create new crypt api log if empty
                $blockIoAssetApiLog = new CryptoAssetApiLog();
                $blockIoAssetApiLog->payment_method_id = TatumIo;
                $blockIoAssetApiLog->object_id = $walletId;
                $blockIoAssetApiLog->object_type = 'wallet_address';
                $blockIoAssetApiLog->network = $network;
                $blockIoAssetApiLog->payload = json_encode($tatumNetworkArray);
                $blockIoAssetApiLog->save();
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


}
