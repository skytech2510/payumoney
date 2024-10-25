<?php

namespace Modules\BlockIo\Classes;

use App\Models\{CryptoAssetSetting,
    CryptoAssetApiLog
};
use Exception, Common;
use BlockIo\Client;

class BlockIo
{
    protected $blockIoAssetSetting;
    protected $blockIoAssetApiLog;

    public function __construct()
    {
        $this->blockIoAssetSetting = new CryptoAssetSetting();
        $this->blockIoAssetApiLog = new CryptoAssetApiLog();
    }

    /**
     * Initialize Block Io
     * @param  string $network
     * @return object blockIo instance
     */
    public function getBlockIoData($network)
    {
        //get crypto Currencies Settings by network
        $blockIoAssetSettings = $this->blockIoAssetSetting->getCryptoAssetSetting(['payment_method_id' => BlockIo, 'network' => $network], ['network_credentials']);
        $networkCredentials = json_decode($blockIoAssetSettings->network_credentials, true);

        //initialize BlockIo
        $apiKey  = $networkCredentials['api_key'];
        $pin = $networkCredentials['pin'];
        $version = BLOCKIO_API_VERSION;

        $blockIo = new Client($apiKey, $pin, $version);
        return $blockIo;
    }


    public function getAccountInfo($apiKey, $pin)
    {
        $version = BLOCKIO_API_VERSION;
        $blockIo = new Client($apiKey, $pin, $version);
        return $blockIo->get_account_info()->data;
    }

    /**
     * Get Crypto Api Log Of Wallet
     * @param  string $walletId
     * @param  string $network
     * @param  object $user
     * @return void
     */
    public function getBlockIoAssetsApiLogOfWallet($walletId, $network, $user)
    {
        $getBlockIoAssetapiLog = $this->blockIoAssetApiLog->getCryptoAssetapiLog(['payment_method_id' => BlockIo, 'object_id' => $walletId, 'object_type' => 'wallet_address', 'network' => $network], ['id']);

        if (empty($getBlockIoAssetapiLog)) {
            //create new crypt api log if empty
            $blockIoAssetApiLog = new CryptoAssetApiLog();
            $blockIoAssetApiLog->payment_method_id = BlockIo;
            $blockIoAssetApiLog->object_id = $walletId;
            $blockIoAssetApiLog->object_type = 'wallet_address';
            $blockIoAssetApiLog->network = $network;

            $getNewAddressInfo = "";

            //initialize BlockIo
            $blockIo = $this->getBlockIoData($network);
            
            try {
                // For Production
                // Call get_new_address BlockIo API
                $getNewAddressInfo = $blockIo->get_new_address(array('label' => $user->id . '_' . $user->email));

                // For Development
                $getNewAddressInfoUserForDevelopment = $blockIo->get_balance();

                // Add BlockIo API response to CryptoapiLog payload
                $blockIoAssetApiLog->payload = json_encode($getNewAddressInfo->data);

                // Save cryptoapiLog
                $blockIoAssetApiLog->save();

                return [
                    'status' => 200,
                ];
            } catch (Exception $e) {
                return [
                    'status'  => 401,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    /**
     * Get User Crypt Address Balance
     * @param  string $network
     * @param  string $address
     * @return object available_balance
     */
    public function getUserBlockIoAddressBalance($network, $address)
    {
        //Initialize BlockIo
        $blockIo = $this->getBlockIoData($network);

        //Call get address balance blockio API
        $getAddressBalance = $blockIo->get_address_balance(array('addresses' => $address));

        //Check address available balance
        foreach ($getAddressBalance->data->balances as $balanceObject) {
            return $balanceObject->available_balance;
        }
    }

    /**
     * Get User's Crypto Address
     * @param  string $walletId
     * @return object address
     */
    public function getUserCryptoAddress($walletId)
    {
        //get user's wallet address
        $cryptoapiLog = $this->blockIoAssetApiLog->getBlockIoAssetapiLog(['payment_method_id' => BlockIo, 'object_id' => $walletId, 'object_type' => 'wallet_address'], ['payload']);
        $payload = json_decode($cryptoapiLog->payload, true);
        $address = $payload['address'];
        return $address;
    }

    /**
     * Get Minimum Network fee
     * @param  string $network
     * @param  string $receiverAddress
     * @param  string $amount
     * @return object estimated min network fee
     */
    public function getNetworkFeeEstimate($network, $receiverAddress, $amount, $priority = 'low')
    {
        //Initialize BlockIo
        $blockIo = $this->getBlockIoData($network);

        $networkFeeInfo = $blockIo->get_network_fee_estimate(array('amount' => $amount, 'to_address' => $receiverAddress, 'priority' => $priority, 'custom_network_fee' => ''));
        return $networkFeeInfo->data->estimated_network_fee;
    }
    /**
     * Get Network from crypto currency API
     * @param  $apiKey
     * @param  $pin
     * @return $network
     */
    public function getBlockIoAccountBalance($apiKey, $pin)
    {
        $blockIo = new Client($apiKey, $pin, BLOCKIO_API_VERSION);
        $blockIoObj = $blockIo->get_balance();
        return $blockIoObj->data;
    }

    public function getBlockIoMerchantBalance($apiKey, $pin, $address)
    {
        $blockIo = new Client($apiKey, $pin, BLOCKIO_API_VERSION);
        return $blockIo->get_address_balance(array('addresses' => $address))->data;
    }

    public function validateNetworkAddressBalance($walletCurrencyCode, $amount, $senderAddress, $receiverAddress, $priority = 'low')
    {
        $getUserCryptoAddressBalance = $this->getUserBlockIoAddressBalance($walletCurrencyCode, $senderAddress);
        $getNetworkFeeEstimate = $this->getNetworkFeeEstimate($walletCurrencyCode, $receiverAddress, $amount, $priority);

        if ($getUserCryptoAddressBalance < 0 || $amount > (double)$getUserCryptoAddressBalance) {
            return [
                'status' => false,
                'reason' => 'insufficient-balance',
                'network-fee' => $getNetworkFeeEstimate,
            ];
        }

        if ($getUserCryptoAddressBalance < ($amount + $getNetworkFeeEstimate)) {
            return [
                'status' => false,
                'reason' => 'network-fee',
                'network-fee' => $getNetworkFeeEstimate,
            ];
        } else {
            return [
                'status' => true,
                'network-fee' => $getNetworkFeeEstimate,
            ];
        }
    }
    /**
     * Check Network Address (both user & merchant)
     * @param  $network
     * @param  $address
     * @return true/false
     */
    public function checkNetworkAddressValidity($network, $address)
    {
        $blockIo = $this->getBlockIoData($network);
        $checkMerchantNetworkAddress = $blockIo->is_valid_address(array('address' => $address));
        if ($checkMerchantNetworkAddress->data->is_valid != true) {
            return false;
        }
        return true;
    }

    /**
     * Get Crypto Api log Payload Details & Confirmations for Crypto_Sent & Crypto_Received
     * @param  $transaction_type_id
     * @param  $payload
     * @param  $confirmations
     * @return array
     */
    public function getCryptoPayloadConfirmationsDetails($transaction_type_id, $payload, $confirmations)
    {
        $arr = [];
        if (!empty($payload))  {
            if ($transaction_type_id == Crypto_Sent || $transaction_type_id == Crypto_Received) {
                
                $payloadJson = json_decode($payload, true);
                if (isset($payloadJson['senderAddress'])) {
                    $arr['senderAddress'] = $payloadJson['senderAddress'];
                }

                if (isset($payloadJson['receiverAddress'])) {
                    $arr['receiverAddress'] = ($transaction_type_id == Crypto_Sent) 
                        ? $payloadJson['receiverAddress'] 
                        : (isset($payloadJson['address']) ? $payloadJson['address'] : $payloadJson['receiverAddress']);
                }
                if (isset($payloadJson['network_fee'])) {
                    $arr['network_fee'] = isset($payloadJson['network_fee']) ? $payloadJson['network_fee'] : 0.00000000;
                }
                $arr['txId'] = $payloadJson['txid'] ?? $payloadJson['txId'];

                $arr['confirmations'] = $confirmations;
            }
        }
        return $arr;
    }

    public function getBlockIoAssetSetting($network, $status, $selectOptions)
    {
        $blockIoAssetSetting = $this->blockIoAssetSetting->where('network', $network)->where(['payment_method_id' => BlockIo]);
        
        if ($status == 'Active') {
            return $blockIoAssetSetting->where(['status' => 'Active'])->first($selectOptions);
        } elseif ($status == 'Inactive') {
            return $blockIoAssetSetting->where(['status' => 'Inactive'])->first($selectOptions);
        } elseif ($status == 'All') {
            return $blockIoAssetSetting->first($selectOptions);
        }
    }

    /**
     * Get active crypto currencies settings networks
     * @return networks
     */
    public function getActiveCryptoCurrenciesSettingsNetwork()
    {
        return $this->blockIoAssetSetting->getAllCryptoCurrencySettings(['payment_method_id' => BlockIo, 'status' => 'Active'], ['id', 'network']);
    }

    /**
     * Check whether user has any crypto wallet address
     * @param  $userWallets
     * @return crypto api log object
     */
    public function getUserWalletBlockIoAssetApiLogs($userWallets)
    {
        $walletArr = [];
        foreach ($userWallets as $wallet) {
            $walletArr[] = $wallet->id;
        }
        return $this->blockIoAssetApiLog->where(['payment_method_id' => BlockIo, 'object_type' => 'wallet_address'])->whereIn('object_id', $walletArr);
    }

    /**
     * Get user wallet address user id
     * @param  $receiverAddress
     * @return user               id
     */
    public function getReceiverAddressWalletUserId($receiverAddress)
    {
        return $this->blockIoAssetApiLog->with(['wallet:id,user_id'])->where(['payment_method_id' => BlockIo, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $receiverAddress . '[[:>:]]'])->first(['object_id']);
    }

    /**
     * Get User Wallet Address
     * @param  $user_id
     * @param  $network
     * @return user       address
     */
    public function getUserNetworkWalletAddress($user_id, $network)
    {
        try {
            $user = \App\Models\User::find($user_id, ['id']);
            $getUserBlockIoAssetApiLog = $this->getUserWalletBlockIoAssetApiLogs($user->wallets)->where(['network' => $network])->first(['payload']);

            if (!empty($getUserBlockIoAssetApiLog)) {
                $payload = json_decode($getUserBlockIoAssetApiLog->payload, true);
                $userAddress = $payload['address'];
                return response()->json([
                    'status' => 200,
                    'userAddress' => $userAddress,
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => __('User :x address not found.', ['x' => $network]),
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Merchant Network Address
     * @param  $network
     * @return merchant   address
     */
    public function getMerchantNetworkAddress($network)
    {
        $cryptoCurrenciesSetting = $this->getBlockIoAssetSetting($network, 'Active', ['network_credentials']);
        if (!empty($cryptoCurrenciesSetting)) {
            $payload = json_decode($cryptoCurrenciesSetting->network_credentials, true);
            $merchantAddress = $payload['address'];
            return $merchantAddress;
        }
    }

    /**
     * Get crypto currency status
     * @param  $walletCurrencyCode
     * @return status
     */
    public function getCryptoCurrencyStatus($walletCurrencyCode)
    {
        $currency = (new \App\Models\Currency())->where(function ($q) use ($walletCurrencyCode) {
            $q->where(['code' => $walletCurrencyCode]);
        })
        ->where(['type' => 'crypto_asset'])
        ->first(['status']);
        
        return $currency->status;
    }

    /**
     * Check Merchant Network Address Validity
     * @param  $network
     * @param  $address
     * @return true/false
     */
    public function checkMerchantNetworkAddressValidity($apiKey, $pin, $address)
    {
        $blockIo = new Client($apiKey, $pin, BLOCKIO_API_VERSION);
        $checkMerchantNetworkAddress = $blockIo->is_valid_address(array('address' => $address));

        if ($checkMerchantNetworkAddress->data->is_valid != true) {
            return [
                'status' => false,
            ];
        }

        return [
            'status' => true,
            'network' => $checkMerchantNetworkAddress->data->network,
        ];
    }

    /**
     * Get active crypto currencies
     * @return array
     */
    public function getActiveCryptoCurrencies()
    {
        return (new Common())->key_value('id', 'code', \App\Models\Currency::where(['type' => 'crypto_asset', 'status' => 'Active'])->get(['id', 'code'])->toArray());
    }

    /**
     * Get allowed crypto currencies setting
     * @return object
     */
    public function getAllowedCryptoCurrenciesSetting()
    {
        return Setting::where(['name' => 'default_crypto_currencies', 'type' => 'general'])->first(['value']);
    }

    /**
     * Get crypto send and crypto received api logs of provided user wallet address
     * @param  $userWallets
     * @return crypto         api log object
     */
    public function deleteWalletAddressCryptoSentCryptoReceivedApiLogs($userWallets)
    {
        $walletArr = [];
        foreach ($userWallets as $wallet) {
            $walletArr[] = $wallet->id;
        }
        $getUserWalletAddressCryptoApiLogs = $this->blockIoAssetApiLog->where(['payment_method_id' => 9, 'object_type' => 'wallet_address'])->whereIn('object_id', $walletArr)->get(['id','payload']);
        
        if ($getUserWalletAddressCryptoApiLogs->isNotEmpty()) {
            foreach ($getUserWalletAddressCryptoApiLogs as $cryptoapiLog) {
                $payload = json_decode($cryptoapiLog->payload, true);
                $address = $payload['address'];
                $getWalletAddressCryptoSentReceivedApiLogs = $this->blockIoAssetApiLog->where(['payment_method_id' => 9])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $address . '[[:>:]]'])->get(['id']);
                if ($getWalletAddressCryptoSentReceivedApiLogs->isNotEmpty()) {
                    foreach ($getWalletAddressCryptoSentReceivedApiLogs as $getWalletAddressCryptoSentReceivedApiLog) {
                        $getWalletAddressCryptoSentReceivedApiLog->delete();
                    }
                }
            }
        }
    }
    //Send & Receive - starts
    /**
     * Withdraw/Send from/to a crypto address
     * @param  string $network
     * @param  string $receiverAddress
     * @param  string $amount
     * @return object withdraw Api response
     */
    public function withdrawOrSendAmountToReceiverAddress($network, $senderAddress, $receiverAddress, $amount, $nonce, $priority = 'low')
    {
       // Initialize BlockIo
        $blockIo = $this->getBlockIoData($network);

        // Transaction operation
        $prepareTransaction           = $blockIo->prepare_transaction(array('amounts' => $amount, 'from_addresses' => $senderAddress, 'to_addresses' => $receiverAddress, 'nonce' => $nonce, 'priority' => $priority));
        $summarizePreparedTransaction = $blockIo->summarize_prepared_transaction($prepareTransaction);
        $createSignedTransaction      = $blockIo->create_and_sign_transaction($prepareTransaction);
        $submitTransaction            = $blockIo->submit_transaction(array('transaction_data' => $createSignedTransaction));

        // Arrange data
        $summarizePreparedTransaction['txid'] = $submitTransaction->data->txid;
        $withdrawInfoData['data'] = $summarizePreparedTransaction;
        $endcodedWithdrawInfoData = json_encode($withdrawInfoData);
        $withdrawInfo = json_decode($endcodedWithdrawInfoData);

        return $withdrawInfo;
    }

    public function createCryptoTransaction($arr)
    {
        $transaction                      = new \App\Models\Transaction();
        $transaction->user_id             = $arr['userId'];
        $transaction->end_user_id         = $arr['endUserId'];
        $transaction->currency_id         = $arr['currencyId'];
        $transaction->payment_method_id   = BlockIo;
        $transaction->uuid                = $arr['uniqueCode'];
        $transaction->transaction_type_id = Crypto_Sent;
        $transaction->subtotal            = $arr['amount'];
        $transaction->total               = "-" . ($arr['amount']);
        $transaction->status              = 'Pending';
        $transaction->save();
        return $transaction->id;
    }

    public function createWithdrawalOrSendCryptoApiLog($arr)
    {
        $cryptoapiLog                    = new CryptoAssetApiLog();
        $cryptoapiLog->payment_method_id = BlockIo;
        $cryptoapiLog->object_id         = $arr['transactionId'];
        $cryptoapiLog->object_type       = 'crypto_sent';
        $cryptoapiLog->network           = $arr['walletCurrencyCode'];
        $cryptoapiLog->payload           = json_encode($arr['withdrawInfoData']); //add BlockIo API withdrawal/Send response to CryptoapiLog payload
        $cryptoapiLog->save();
    }

    public function getUpdatedSendWalletBalance($arr)
    {
        // updating of merchant network address balance will NOT be done in the system
        // update user network address balance
        $getUserCryptoAddressBalance = self::getUserBlockIoAddressBalance($arr['walletCurrencyCode'], $arr['senderAddress']);
        $senderWallet = (new Common())->getUserWallet([], ['user_id' => $arr['userId'], 'currency_id' => $arr['currencyId']], ['id', 'balance']);
        $senderWallet->balance = $getUserCryptoAddressBalance;
        $senderWallet->save();
    }

    public function getEachTransactionNetworkFee($txId, $network)
    {
        $blockIo = $this->getBlockIoData($network);
        $networkFee = $blockIo->get_raw_transaction(array('txid' => $txId));
        return $networkFee->data->network_fee;
    }


    public function createNotification($network)
    {
        $blockIo = $this->getBlockIoData($network);

        $notificaions = $blockIo->list_notifications();

        if (empty($notificaions->data->notifications)) {
            return $blockIo->create_notification(array('type' => 'account', 'url' => (\URL::to('/') . '/receive/blockio-balance-change-notification') ));
        } else {
            $oldNotifications = $notificaions->data->notifications;
            foreach($oldNotifications as $oldNotification) {
                $blockIo->delete_notification(array('notification_id' => $oldNotification->notification_id));
            }
            return $blockIo->create_notification(array('type' => 'account', 'url' => (\URL::to('/') . '/receive/blockio-balance-change-notification') ));
        }
    }

    public function getNotificationList($network)
    {
        $blockIo = $this->getBlockIoData($network);
        return $blockIo->list_notifications();
    }

    public function getNotificationStatus($network)
    {
        $blockIo = $this->getBlockIoData($network);
        $notification = $blockIo->list_notifications();

        if (empty($notification->data->notifications)) {
            return [
                'status' => false,
            ];
        }

        return [
            'status' => $notification->data->notifications[0]->enabled,
            'notificationId' => $notification->data->notifications[0]->notification_id,
        ];
    }

    public function enableNotificationStatus($network, $notificationId)
    {
        $blockIo = $this->getBlockIoData($network);
        return $blockIo->enable_notification(array('notification_id' => $notificationId));
    }

    public function deleteNotification($network)
    {
        $blockIo = $this->getBlockIoData($network);

        $notificaionList = $blockIo->list_notifications();
    
        $notifications = $notificaionList->data->notifications;
        foreach($notifications as $notification) {
            $blockIo->delete_notification(array('notification_id' => $notification->notification_id));
        }
    }

    public function checkAddressValidity($network, $address)
    {
        $blockIo = $this->getBlockIoData($network);

        try {
            $isValidAddress = $blockIo->is_valid_address(array('address' => $address));

            if ($isValidAddress->status == 'success') {
                if ($isValidAddress->data->is_valid == true) {
                    $response = [
                        'status' => 200,
                        'message' => __('This is a valid :x address', ['x' => $network])
                    ];
                } else {
                    $response = [
                        'status' => 401,
                        'message' => __('This is not a valid :x address', ['x' => $network])
                    ];
                }
                return response()->json($response);
            } else if ($isValidAddress->status == 'fail' && isset($isValidAddress->data->error_message)) {
                return response()->json([
                    'status' => 401,
                    'message' => $isValidAddress->data->error_message
                ]);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getCurrentPricesList($network)
    {
        $blockIo = $this->getBlockIoData(decrypt($network));
        return $blockIo->get_current_price();
    }

    /**
     * Functions start that both need for Web and mobile app
     */

    // Address validity check during Crypto Send
    public function addressValidityCheck($walletCurrencyCode, $receiverAddress, $userId)
    {
        try {
            $checkUserNetworkAddress = $this->checkNetworkAddressValidity($walletCurrencyCode, $receiverAddress);

            if (!$checkUserNetworkAddress) {
                return response()->json([
                    'status'  => 401,
                    'message' => __('Invalid recipient :x address', ['x' => $walletCurrencyCode])
                ]);
            }

            //Backend validation of own network address with receiver network address - starts
            $getUserNetworkWallet = $this->getUserNetworkWalletAddress($userId, $walletCurrencyCode);
            if ($receiverAddress == $getUserNetworkWallet->getData()->userAddress) {
                return response()->json([
                    'status'  => 401,
                    'message' => __('Cannot send :x to own address', ['x' => $walletCurrencyCode]),
                ]);
            }
            //Backend validation of own network address with receiver network address - ends

            return response()->json([
                'status'  => 200,
                'isValid' => true,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Validate user balance against amount
    public function userBalanceCheck($walletCurrencyCode, $amount, $senderAddress, $receiverAddress, $priority)
    {
        $currencyId = \App\Models\Currency::whereCode($walletCurrencyCode)->Value('id');

        try {
            $validateNetworkAddressBalance = $this->validateNetworkAddressBalance($walletCurrencyCode, $amount, $senderAddress, $receiverAddress, $priority);
            if (!$validateNetworkAddressBalance['status']) {
                return response()->json([
                    'status' => 401,
                    'message' => __("Network fee :x and amount :y exceeds your :z balance.", ['x' => $validateNetworkAddressBalance['network-fee'], 'y' => formatNumber($amount, $currencyId), 'z' => $walletCurrencyCode]),
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                    'network-fee' => $validateNetworkAddressBalance['network-fee']
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function minimumAmountCheck($walletCurrencyCode, $amount)
    {
        $response = ['status' => 200];

        // Minimum Amounts You can prepare transactions for sending at least 0.02 DOGE, 0.00002 BTC, or 0.0002 LTC. (BlockIo)
        switch ($walletCurrencyCode) {
            case 'DOGE':
            case 'DOGETEST':
                if ($amount < 2) {
                    $response = [
                        'status' => 401,
                        'message' => __('The minimum amount must be :x :y.', ['x' => 2, 'y' => $walletCurrencyCode]),
                    ];
                }
                break;

            case 'LTC':
            case 'LTCTEST':
                if ($amount < 0.0002) {
                    $response = [
                        'status' => 401,
                        'message' => __('The minimum amount must be :x :y.', ['x' => 0.0002, 'y' => $walletCurrencyCode])
                    ];
                }
                break;

            case 'BTC':
            case 'BTCTEST':
                if ($amount < 0.00002) {
                    $response = [
                        'status' => 401,
                        'message' => __('The minimum amount must be :x :y.', ['x' => 0.00002, 'y' => $walletCurrencyCode])
                    ];
                }
                break;
            default:
                break;
        }

        return response()->json($response);
    }


    public function cryptoTrxData($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress, $userId,  $currency)
    {
        $getNetworkFeeEstimate = $this->getNetworkFeeEstimate($walletCurrencyCode, $receiverAddress, $amount, $priority);

        $arr = [
            'receiverAddress' => $receiverAddress,
            'amount' => $amount,
            'networkFee' => $getNetworkFeeEstimate,
            'senderAddress' => $senderAddress,
            'userId' => $userId,
            'currencyId' => $currency->id,
            'currencySymbol' => $currency->symbol,
            'uniqueCode' => unique_code(),
            'priority' => $priority
        ]; 

        //Get wallet id of receiver address from crypto api log
        $getReceiverAddressCryptoApiLog = $this->getReceiverAddressWalletUserId($receiverAddress);
        if (!empty($getReceiverAddressCryptoApiLog)) {
            $arr['endUserId'] = $getReceiverAddressCryptoApiLog->wallet->user_id;
        } else {
            $arr['endUserId'] = null;
        }

        return $arr;
    }

    public function extensionCheck()
    {
        $extensions = ['GMP', 'cURL', 'mbstring', 'bcmath'];
        foreach ($extensions as $extension) {
            if (!extension_loaded(strtolower($extension))) {
                return response()->json([
                    'status' => 401,
                    'message' => __(':x extension is needed to be installed for crypto send process.', ['x' => $extension])
                ]);
            }
        }
    }

    public function cryptoSendProcess($walletCurrencyCode, $cryptoTrx)
    {
        try {
            $notifications = $this->getNotificationList($walletCurrencyCode);
            $notification = NULL;

            // If no notification created or available
            if ($notifications->status == 'success' && empty($notifications->data->notifications)) {
                $notification = $this->createNotification($walletCurrencyCode);
            } else if ($notifications->status == 'success' && isset($notifications->data->notifications[0]->notification_id) && $notifications->data->notifications[0]->enabled == false) {
                $notification = $this->enableNotificationStatus($walletCurrencyCode, $notifications->data->notifications[0]->notification_id);
            }

            if (isset($notification) && $notification->status == 'success' && isset($notification->data->notification_id) && $notification->data->enabled) {
                return $this->processCrypto($walletCurrencyCode, $cryptoTrx);
            } else if (isset($notifications) && $notifications->status == 'success' && isset($notifications->data->notifications[0]->notification_id) && $notifications->data->notifications[0]->enabled) {
                return $this->processCrypto($walletCurrencyCode, $cryptoTrx);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => __('Block.io subscribed package is Expired, please contact with the administrator.')
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function processCrypto($walletCurrencyCode, $cryptoTrx)
    {
        try {
            $processData = $this->withdrawOrSendAmountToReceiverAddress($walletCurrencyCode, $cryptoTrx['senderAddress'], $cryptoTrx['receiverAddress'], $cryptoTrx['amount'], $cryptoTrx['uniqueCode'], $cryptoTrx['priority']);
            $response = [
                'status' => 200,
                'data' => $processData
            ];

        } catch (Exception $e) {
            $response = [
                'status' => 401,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response);
    }
}


