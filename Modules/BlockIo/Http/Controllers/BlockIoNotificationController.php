<?php

namespace Modules\BlockIo\Http\Controllers;

use Modules\BlockIo\Classes\BlockIo;
use App\Models\CryptoAssetSetting;
use Illuminate\Routing\Controller;
use App\Models\CryptoAssetApiLog;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Wallet;
use DB, Exception;

class BlockIoNotificationController extends Controller
{
    protected $blockIo;

    public function __construct()
    {
        $this->blockIo = new BlockIo();
    }

    public function balanceNotification(Request $request)
    {
        \Log::info($request->all());

        $notifArray = $request->all();

        if ($notifArray['type'] == 'address') {
            $responseData           = $notifArray['data'];
            $responseNetwork        = $notifArray['data']['network'];
            $responseAddress        = $notifArray['data']['address'];
            $responseBalanceChange  = $notifArray['data']['balance_change'];
            $responseAmountReceived = $notifArray['data']['amount_received'];
            $responseAmountSent     = $notifArray['data']['amount_sent'];
            $responseTxid           = $notifArray['data']['txid'];
            $responseConfirmations  = $notifArray['data']['confirmations'];
            $responseNotification   = $notifArray['notification_id'];

            $cryptoAssetSetting =  CryptoAssetSetting::get(['network_credentials', 'status']);

            foreach ($cryptoAssetSetting as $cryptoAssetSettings) {
                $cryptoAssetSettingApiKey = json_decode($cryptoAssetSettings->network_credentials)->api_key;
                $cryptoAssetSettingStatus = $cryptoAssetSettings->status;
            }

            if (count($cryptoAssetSetting) > 0 ) {
                $cryptoAssetsSettingNetworkCreds = $cryptoAssetSetting[0]->network_credentials;
                $BLOCKIO_API_KEY = json_decode($cryptoAssetsSettingNetworkCreds)->api_key;
                $BLOCKIO_SECRET_PIN  = json_decode($cryptoAssetsSettingNetworkCreds)->pin;

                // If Confirmation count 1 from response (BTC, LTC, DOGE, BTCTEST, LTCTEST, DOGETEST)
                // Record inserted to the database but status remain as pending
                if ($responseConfirmations == 1) {

                    // Amount who received ['balance_change' => '2.00000000', 'amount_sent' => '0.00000000', 'amount_received' => '2.00000000']
                    if ($responseBalanceChange > 0 && $responseAmountSent == 0) {

                        // Finding who has sent the coin
                        $getCryptoSentApiLogSql = $getCryptoSentApiLog = CryptoAssetApiLog::with(['transaction:id,user_id,end_user_id,currency_id,uuid'])->where(['network' => $responseNetwork])->whereIn('object_type', ['crypto_sent', 'crypto_exchange_from', 'fiat_exchange_from'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseTxid . '[[:>:]]'])->get();
                        
                        // If found the record someone sent the coin from the system (May be merchant or any other user)
                        if (count($getCryptoSentApiLog) > 0) {
                            $getCryptoSentApiLogNetwork = json_decode($getCryptoSentApiLog[0]->payload)->network;
                            $getCryptoSentApiLogTxid = json_decode($getCryptoSentApiLog[0]->payload)->txid;
                            $getCryptoSentApiLogReceiverAddress = json_decode($getCryptoSentApiLog[0]->payload)->receiverAddress;

                            // Matching the receiver address (who receive the coin)
                            if ($responseNetwork == $getCryptoSentApiLogNetwork && $responseTxid == $getCryptoSentApiLogTxid && $responseAddress == $getCryptoSentApiLogReceiverAddress) {

                                $getCryptoSentApiLogObjectId        = $getCryptoSentApiLog[0]->object_id;
                                $getCryptoSentApiLogObjectType      = $getCryptoSentApiLog[0]->object_type;
                                $getCryptoSentTransactionUserId     = $getCryptoSentApiLog[0]->transaction->user_id;
                                $getCryptoSentTransactionEndUserId  = $getCryptoSentApiLog[0]->transaction->end_user_id;
                                $getCryptoSentTransactionUniqueCode = $getCryptoSentApiLog[0]->transaction->uuid;
                                $getCryptoSentTransactionCurrencyId = $getCryptoSentApiLog[0]->transaction->currency_id;
                                $getCryptoSentApiLogSenderAddress   = json_decode($getCryptoSentApiLog[0]->payload)->senderAddress;

                                if ($getCryptoSentApiLogObjectType == 'fiat_exchange_from') {
                                    $network_fee = json_decode($getCryptoSentApiLog[0]->payload)->network_fee;
                                    $responseData["network_fee"] = $network_fee;
                                }

                                $responseData["senderAddress"]   = $getCryptoSentApiLogSenderAddress;
                                $responseData["receiverAddress"] = $responseAddress;

                                try {
                                    DB::beginTransaction();

                                    // Update the confirmation who has sent the coin (from confirmation 0 to 2/3/5)
                                    $updateCryptoApiLogsConfirmationsSql = CryptoAssetApiLog::where('object_id', $getCryptoSentApiLogObjectId)->update(['confirmations' => $responseConfirmations]);

                                    // Create the transaction record who received the coin
                                    if ($getCryptoSentApiLogObjectType == 'crypto_sent') {
                                        $transaction = new \App\Models\Transaction();
                                        $transaction->user_id             = $getCryptoSentTransactionEndUserId;
                                        $transaction->end_user_id         = $getCryptoSentTransactionUserId;
                                        $transaction->currency_id         = $getCryptoSentTransactionCurrencyId;
                                        $transaction->payment_method_id   = BlockIo;
                                        $transaction->uuid                = unique_code();
                                        $transaction->transaction_type_id = Crypto_Received;
                                        $transaction->subtotal            = $responseAmountReceived;
                                        $transaction->total               = $responseAmountReceived;
                                        $transaction->status              = 'Pending';
                                        $transaction->save();
                                    }  elseif ($getCryptoSentApiLogObjectType == 'crypto_exchange_from') {
                                        $transaction = new \App\Models\Transaction();
                                        $transaction->user_id              = $getCryptoSentTransactionUserId;
                                        $transaction->end_user_id          = NULL;
                                        $transaction->currency_id          = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyId;
                                        $transaction->payment_method_id    = BlockIo;
                                        $transaction->uuid                 = $getCryptoSentTransactionUniqueCode;
                                        $transaction->transaction_type_id  = defined('Crypto_Exchange_To') ? Crypto_Exchange_To : NULL;
                                        $transaction->subtotal             = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->total                = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->status               = 'Pending';
                                        $transaction->save();
                                    } else if ($getCryptoSentApiLogObjectType == 'fiat_exchange_from') {
                                        $transaction = new \App\Models\Transaction();
                                        $transaction->user_id              = $getCryptoSentTransactionUserId;
                                        $transaction->end_user_id          = NULL;
                                        $transaction->currency_id          = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyId;
                                        $transaction->payment_method_id    = BlockIo;
                                        $transaction->uuid                 = $getCryptoSentTransactionUniqueCode;
                                        $transaction->transaction_type_id  = defined('Fiat_Exchange_To') ? Fiat_Exchange_To : NULL;
                                        $transaction->subtotal             = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->total                = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->status               = 'Pending';
                                        $transaction->save();
                                    }

                                    $enCodedResponseData = json_encode($responseData);

                                    // Create the CryptoAssetApiLog record for who received the coin
                                    $cryptoReceivedApiLogSql = new \App\Models\CryptoAssetApiLog();
                                    $cryptoReceivedApiLogSql->payment_method_id = BlockIo;
                                    $cryptoReceivedApiLogSql->object_id = $transaction->id;
                                    if ($getCryptoSentApiLogObjectType == 'crypto_sent') {
                                        $cryptoReceivedApiLogSql->object_type = 'crypto_received';
                                    } elseif ($getCryptoSentApiLogObjectType == 'crypto_exchange_from') {
                                        $cryptoReceivedApiLogSql->object_type = 'crypto_exchange_to';
                                    } elseif ($getCryptoSentApiLogObjectType == 'fiat_exchange_from') {
                                        $cryptoReceivedApiLogSql->object_type = 'fiat_exchange_to';
                                    }
                                    $cryptoReceivedApiLogSql->network = $responseNetwork;
                                    $cryptoReceivedApiLogSql->payload = $enCodedResponseData;
                                    $cryptoReceivedApiLogSql->confirmations = $responseConfirmations;
                                    $cryptoReceivedApiLogSql->save();

                                    // Update the blockio wallet balance who received the coin
                                    $getAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($responseNetwork, $responseAddress);

                                    $getWalletAddressCryptoApiLogSql = $getWalletAddressCryptoApiLog = CryptoAssetApiLog::with(['wallet:id,user_id'])->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();

                                    if (!$getWalletAddressCryptoApiLogSql->isEmpty()) {
                                        $walletId = $getWalletAddressCryptoApiLog[0]->wallet->id;

                                        if (count($getWalletAddressCryptoApiLog) > 0) {
                                            $updateReceiverWalletBalanceSql = Wallet::where('id', $walletId)->update(['balance' => $getAddressBalance]);
                                        }
                                    }
                                    DB::commit();
                                } catch (Exception $e) {
                                    DB::rollback();
                                }
                            }
                        } else {
                            // Coint sent from external sourcres
                            $getCryptoSentApiLogSql = $getCryptoSentApiLog = CryptoAssetApiLog::with('wallet:id,user_id')->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();

                            // If found the record, the notification for user crypto received, otherwise admin received the coin
                            if (count($getCryptoSentApiLogSql) > 0) {
                                $getNetworkCurrency = \App\Models\Currency::where(['code' => $responseNetwork])->first(['id']);
                                // Get the user who received the coin
                                $cryptoReceiverFromUnknown = \App\Models\User::where(['id' => $getCryptoSentApiLogSql[0]->wallet->user_id])->first();

                                try {
                                    DB::beginTransaction();
                                    $transaction = new \App\Models\Transaction();
                                    $transaction->user_id             = $cryptoReceiverFromUnknown->id;
                                    $transaction->end_user_id         = NULL;
                                    $transaction->currency_id         = $getNetworkCurrency->id;
                                    $transaction->payment_method_id   = BlockIo;
                                    $transaction->uuid                = unique_code();
                                    $transaction->transaction_type_id = Crypto_Received;
                                    $transaction->subtotal            = $responseAmountReceived;
                                    $transaction->total               = $responseAmountReceived;
                                    $transaction->status              = 'Pending';
                                    $transaction->save();

                                    $responseData['receiverAddress'] = json_decode($getCryptoSentApiLog[0]->payload)->address;
                                    $enCodedResponseData = json_encode($responseData);

                                    $cryptoReceivedApiLogSql = new \App\Models\CryptoAssetApiLog();
                                    $cryptoReceivedApiLogSql->payment_method_id = BlockIo;
                                    $cryptoReceivedApiLogSql->object_id         = $transaction->id;
                                    $cryptoReceivedApiLogSql->object_type       = 'crypto_received';
                                    $cryptoReceivedApiLogSql->network           = $responseNetwork;
                                    $cryptoReceivedApiLogSql->payload           = $enCodedResponseData;
                                    $cryptoReceivedApiLogSql->confirmations     = $responseConfirmations;
                                    $cryptoReceivedApiLogSql->save();
                                    DB::commit();
                                } catch (Exception $e) {
                                    DB::rollBack();
                                }

                            } else {
                                \Log::info('Admin');
                            }
                        }
                    } else {
                        // Amount who sent
                        $getCryptoSentApiLogSql = $getCryptoSentApiLog = CryptoAssetApiLog::where(['network' => $responseNetwork, 'object_type' => 'crypto_sent'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseTxid . '[[:>:]]'])->get();

                        if (count($getCryptoSentApiLog) > 0) {
                            $getCryptoSentApiLogObjectId        = $getCryptoSentApiLog[0]->object_id;
                            $getCryptoSentApiLogNetwork         = json_decode($getCryptoSentApiLog[0]->payload)->network;
                            $getCryptoSentApiLogTxid            = json_decode($getCryptoSentApiLog[0]->payload)->txid;
                            $getCryptoSentApiLogReceiverAddress = json_decode($getCryptoSentApiLog[0]->payload)->receiverAddress;
                            $getCryptoSentApiLogSenderAddress   = json_decode($getCryptoSentApiLog[0]->payload)->senderAddress;

                            if ($responseNetwork == $getCryptoSentApiLogNetwork && $responseTxid == $getCryptoSentApiLogTxid && $responseAddress == $getCryptoSentApiLogSenderAddress) {

                                $receiverAddressCheck = CryptoAssetApiLog::where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $getCryptoSentApiLogReceiverAddress . '[[:>:]]'])->get();

                                $cryptoAssetSettings = CryptoAssetSetting::where(['network' => $responseNetwork])->first(['network_credentials']);
                                $merchantAddress = json_decode($cryptoAssetSettings->network_credentials)->address;

                                if (count($receiverAddressCheck) == 0 && ($getCryptoSentApiLogReceiverAddress !=  $merchantAddress)) {
                                    CryptoAssetApiLog::where('object_id', $getCryptoSentApiLogObjectId)->update(['confirmations' => $responseConfirmations]);
                                }
                            }
                        }
                    }
                } else {
                    // Fetching the records of same blockIO txid (sent - receive both which confimation last stage was 1)
                    $getCryptoSentReceivedApiLogsSql = $getCryptoSentReceivedApiLogs = CryptoAssetApiLog::where(['confirmations' => 1, 'network' => $responseNetwork])->whereIn('object_type', ['crypto_sent', 'crypto_received', 'crypto_exchange_from', 'crypto_exchange_to', 'fiat_exchange_from', 'fiat_exchange_to'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseTxid . '[[:>:]]'])->get();
                    if (count($getCryptoSentReceivedApiLogsSql) > 0) {
                        foreach ($getCryptoSentReceivedApiLogs as $getCryptoSentReceivedApiLog) {
                            $getCryptoSentReceivedApiLogNetwork         = json_decode($getCryptoSentReceivedApiLog->payload)->network;
                            $getCryptoSentReceivedApiLogTxid            = json_decode($getCryptoSentReceivedApiLog->payload)->txid;
                            $getCryptoSentReceivedApiLogReceiverAddress = json_decode($getCryptoSentReceivedApiLog->payload)->receiverAddress;
                            $getCryptoSentReceivedApiLogSenderAddress   = isset(json_decode($getCryptoSentReceivedApiLog->payload)->senderAddress) ? json_decode($getCryptoSentReceivedApiLog->payload)->senderAddress : '';

                            // Matching network (BTC, LTC, DOGE...) & TransactionID
                            if ($responseNetwork == $getCryptoSentReceivedApiLogNetwork && $responseTxid == $getCryptoSentReceivedApiLogTxid) {

                                try {
                                    DB::beginTransaction();
                                    // Crypto Sent Receive
                                    if (!empty($getCryptoSentReceivedApiLogSenderAddress) && ($responseAddress == $getCryptoSentReceivedApiLogReceiverAddress || $responseAddress == $getCryptoSentReceivedApiLogSenderAddress)) {

                                        $getCryptoSentReceivedApiLogObjectId = $getCryptoSentReceivedApiLog->object_id;
                                        // Confirmatin update [1 to 3/5/10]
                                        $updateCryptoApiLogsConfirmationsSql = CryptoAssetApiLog::where(['object_id' => $getCryptoSentReceivedApiLogObjectId, 'confirmations' => 1])->update(['confirmations' => $responseConfirmations]);

                                        // Transaction status update
                                        $updateCryptoApiLogsConfirmations = Transaction::where('id', $getCryptoSentReceivedApiLogObjectId)->update(['status' => 'Success']);

                                        // Wallet balance update who received the coin
                                        $getWalletAddressCryptoApiLogSql = $getWalletAddressCryptoApiLog = CryptoAssetApiLog::with(['wallet:id,user_id'])->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();
                                        if (count($getWalletAddressCryptoApiLog) > 0) {
                                            $getAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($responseNetwork, $responseAddress);
                                            $getWalletAddressCryptoApiLogWalletId = $getWalletAddressCryptoApiLog[0]->wallet->id;
                                            $updateReceiverWalletBalanceSql = Wallet::where('id', $getWalletAddressCryptoApiLogWalletId)->update(['balance' => $getAddressBalance]);
                                        }
                                    } else {
                                        $getCryptoSentReceivedApiLogObjectId = $getCryptoSentReceivedApiLog->object_id;

                                        $updateCryptoAssetApiLogsConfirmationsSql = CryptoAssetApiLog::where(['object_id' => $getCryptoSentReceivedApiLogObjectId, 'confirmations' => 1])->update(['confirmations' => $responseConfirmations]);

                                        $updateCryptoApiLogsConfirmations = Transaction::where('id', $getCryptoSentReceivedApiLogObjectId)->update(['status' => 'Success']);

                                        $getAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($responseNetwork, $responseAddress);

                                        $getWalletAddressCryptoApiLogSql = $getWalletAddressCryptoAssetApiLog = CryptoAssetApiLog::with(['wallet:id,user_id'])->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();

                                        if (count($getWalletAddressCryptoAssetApiLog) > 0) {
                                            $getWalletAddressCryptoAssetApiLogWalletId = $getWalletAddressCryptoAssetApiLog[0]->wallet->id;

                                            $updateReceiverWalletBalanceSql = Wallet::where(['id' => $getWalletAddressCryptoAssetApiLogWalletId])->update(['balance' => $getAddressBalance]);
                                        }
                                    }
                                    DB::commit();
                                } catch (Exception $e) {
                                    DB::rollback();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function balanceNotificationDevelopment()
    {
        return;

        // Admin amount sent to user [confimation at 1]
        $notifArray =  array (
            'notification_id' => 'e9bbca823cb14f2a1a4d17e2',
            'delivery_attempt' => 1,
            'type' => 'address',
            'data' =>
            array (
              'network' => 'DOGETEST',
              'address' => '2N3nZfHVr1QUEnq2CFd52CMH5LWdrYVJ2ET',
              'balance_change' => '-2.02366000',
              'amount_sent' => '474.70308000',
              'amount_received' => '472.67942000',
              'txid' => '32ef1c1b44bcc8569823403792b7b17ec79e4adc52d81ddaa2401abe85f580cd',
              'confirmations' => 1,
              'is_green' => false,
            ),
            'created_at' => 1654597891,
        );

        // User amount received [confirmation at - 1]
        $notifArray = array (
            'notification_id' => 'e9bbca823cb14f2a1a4d17e2',
            'delivery_attempt' => 6,
            'type' => 'address',
            'data' =>
            array (
                'network' => 'DOGETEST',
                'address' => '2MvHFvDVKngaSNEfeUFqCbNL9SrQrbg1u5M',
                'balance_change' => '2.00000000',
                'amount_sent' => '0.00000000',
                'amount_received' => '2.00000000',
                'txid' => '32ef1c1b44bcc8569823403792b7b17ec79e4adc52d81ddaa2401abe85f580cd',
                'confirmations' => 1,
                'is_green' => false,
            ),
            'created_at' => 1654597891,
        );
        
        // Admin/User amount sent to user [confimation at 10]
        $notifArray = array (
          'notification_id' => '662cf8cea3293771bd5218df',
          'delivery_attempt' => 1,
          'type' => 'address',
          'data' =>
          array (
            'network' => 'DOGETEST',
            'address' => '2MvHFvDVKngaSNEfeUFqCbNL9SrQrbg1u5M',
            'balance_change' => '-2.02626000',
            'amount_sent' => '4.00000000',
            'amount_received' => '1.97374000',
            'txid' => '429f2ab0e2975d53d541655e2a676ed09e293ad7845fed85eb4e469dc3b64d88',
            'confirmations' => 10,
            'is_green' => false,
          ),
          'created_at' => 1655033229,
        );

        // User amount received [confirmation at - 10]
        $notifArray = array (
          'notification_id' => '662cf8cea3293771bd5218df',
          'delivery_attempt' => 1,
          'type' => 'address',
          'data' =>
          array (
            'network' => 'DOGETEST',
            'address' => '2NC5vrph6uYV3a9GWVASsfq81UayUt6kojC',
            'balance_change' => '2.00000000',
            'amount_sent' => '0.00000000',
            'amount_received' => '2.00000000',
            'txid' => '429f2ab0e2975d53d541655e2a676ed09e293ad7845fed85eb4e469dc3b64d88',
            'confirmations' => 10,
            'is_green' => false,
          ),
          'created_at' => 1655033229,
        );

        if ($notifArray['type'] == 'address') {
            $responseData           = $notifArray['data'];
            $responseNetwork        = $notifArray['data']['network'];
            $responseAddress        = $notifArray['data']['address'];
            $responseBalanceChange  = $notifArray['data']['balance_change'];
            $responseAmountReceived = $notifArray['data']['amount_received'];
            $responseAmountSent     = $notifArray['data']['amount_sent'];
            $responseTxid           = $notifArray['data']['txid'];
            $responseConfirmations  = $notifArray['data']['confirmations'];
            $responseNotification   = $notifArray['notification_id'];

            $cryptoAssetSetting =  CryptoAssetSetting::get(['network_credentials', 'status']);

            foreach ($cryptoAssetSetting as $cryptoAssetSettings) {
                $cryptoAssetSettingApiKey = json_decode($cryptoAssetSettings->network_credentials)->api_key;
                $cryptoAssetSettingStatus = $cryptoAssetSettings->status;
            }

            $getActiveCryptoCurrencySettingsCredentialsSql = DB::select(DB::raw("SELECT network_credentials FROM crypto_asset_settings WHERE network = '$responseNetwork' AND payment_method_id = 9 AND status = 'Active' "));

            if (count($cryptoAssetSetting) > 0 ) {
                $cryptoAssetSettingNetworkCreds = $cryptoAssetSetting[0]->network_credentials;
                $BLOCKIO_API_KEY = json_decode($cryptoAssetSettingNetworkCreds)->api_key;
                $BLOCKIO_SECRET_PIN  = json_decode($cryptoAssetSettingNetworkCreds)->pin;

                // If Confirmation count 1 from response (BTC, LTC, DOGE, BTCTEST, LTCTEST, DOGETEST)
                // Record inserted to the database but status remain as pending
                if ($responseConfirmations == 1) {

                    // Amount who received ['balance_change' => '2.00000000', 'amount_sent' => '0.00000000', 'amount_received' => '2.00000000']
                    if ($responseBalanceChange > 0 && $responseAmountSent == 0) {

                        // Finding who has sent the coin
                        $getCryptoSentApiLogSql = $getCryptoSentApiLog = CryptoAssetApiLog::with(['transaction:id,user_id,end_user_id,currency_id,uuid'])->where(['network' => $responseNetwork])->whereIn('object_type', ['crypto_sent', 'crypto_exchange_from', 'fiat_exchange_from'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseTxid . '[[:>:]]'])->get();
                        
                        // If found the record someone sent the coin from the system (May be merchant or any other user)
                        if (count($getCryptoSentApiLog) > 0) {
                            $getCryptoSentApiLogNetwork = json_decode($getCryptoSentApiLog[0]->payload)->network;
                            $getCryptoSentApiLogTxid = json_decode($getCryptoSentApiLog[0]->payload)->txid;
                            $getCryptoSentApiLogReceiverAddress = json_decode($getCryptoSentApiLog[0]->payload)->receiverAddress;

                            // Matching the receiver address (who receive the coin)
                            if ($responseNetwork == $getCryptoSentApiLogNetwork && $responseTxid == $getCryptoSentApiLogTxid && $responseAddress == $getCryptoSentApiLogReceiverAddress) {

                                $getCryptoSentApiLogObjectId        = $getCryptoSentApiLog[0]->object_id;
                                $getCryptoSentApiLogObjectType      = $getCryptoSentApiLog[0]->object_type;
                                $getCryptoSentTransactionUserId     = $getCryptoSentApiLog[0]->transaction->user_id;
                                $getCryptoSentTransactionEndUserId  = $getCryptoSentApiLog[0]->transaction->end_user_id;
                                $getCryptoSentTransactionUniqueCode = $getCryptoSentApiLog[0]->transaction->uuid;
                                $getCryptoSentTransactionCurrencyId = $getCryptoSentApiLog[0]->transaction->currency_id;
                                $getCryptoSentApiLogSenderAddress   = json_decode($getCryptoSentApiLog[0]->payload)->senderAddress;

                                if ($getCryptoSentApiLogObjectType == 'fiat_exchange_from') {
                                    $network_fee = json_decode($getCryptoSentApiLog[0]->payload)->network_fee;
                                    $responseData["network_fee"] = $network_fee;
                                }

                                $responseData["senderAddress"]   = $getCryptoSentApiLogSenderAddress;
                                $responseData["receiverAddress"] = $responseAddress;

                                try {
                                    DB::beginTransaction();

                                    // Update the confirmation who has sent the coin (from confirmation 0 to 2/3/5)
                                    $updateCryptoApiLogsConfirmationsSql = CryptoAssetApiLog::where('object_id', $getCryptoSentApiLogObjectId)->update(['confirmations' => $responseConfirmations]);

                                    // Create the transaction record who received the coin
                                    if ($getCryptoSentApiLogObjectType == 'crypto_sent') {
                                        $transaction = new \App\Models\Transaction();
                                        $transaction->user_id             = $getCryptoSentTransactionEndUserId;
                                        $transaction->end_user_id         = $getCryptoSentTransactionUserId;
                                        $transaction->currency_id         = $getCryptoSentTransactionCurrencyId;
                                        $transaction->payment_method_id   = BlockIo;
                                        $transaction->uuid                = unique_code();
                                        $transaction->transaction_type_id = Crypto_Received;
                                        $transaction->subtotal            = $responseAmountReceived;
                                        $transaction->total               = $responseAmountReceived;
                                        $transaction->status              = 'Pending';
                                        $transaction->save();
                                    }  elseif ($getCryptoSentApiLogObjectType == 'crypto_exchange_from') {
                                        $transaction = new \App\Models\Transaction();
                                        $transaction->user_id              = $getCryptoSentTransactionUserId;
                                        $transaction->end_user_id          = NULL;
                                        $transaction->currency_id          = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyId;
                                        $transaction->payment_method_id    = BlockIo;
                                        $transaction->uuid                 = $getCryptoSentTransactionUniqueCode;
                                        $transaction->transaction_type_id  = defined('Crypto_Exchange_To') ? Crypto_Exchange_To : NULL;
                                        $transaction->subtotal             = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->total                = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->status               = 'Pending';
                                        $transaction->save();
                                    } else if ($getCryptoSentApiLogObjectType == 'fiat_exchange_from') {
                                        $transaction = new \App\Models\Transaction();
                                        $transaction->user_id              = $getCryptoSentTransactionUserId;
                                        $transaction->end_user_id          = NULL;
                                        $transaction->currency_id          = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyId;
                                        $transaction->payment_method_id    = BlockIo;
                                        $transaction->uuid                 = $getCryptoSentTransactionUniqueCode;
                                        $transaction->transaction_type_id  = defined('Fiat_Exchange_To') ? Fiat_Exchange_To : NULL;
                                        $transaction->subtotal             = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->total                = json_decode($getCryptoSentApiLog[0]->payload)->exchangeToCurrencyAmount;
                                        $transaction->status               = 'Pending';
                                        $transaction->save();
                                    }

                                    $enCodedResponseData = json_encode($responseData);

                                    // Create the CryptoAssetApiLog record for who received the coin
                                    $cryptoReceivedApiLogSql = new \App\Models\CryptoAssetApiLog();
                                    $cryptoReceivedApiLogSql->payment_method_id = BlockIo;
                                    $cryptoReceivedApiLogSql->object_id = $transaction->id;
                                    if ($getCryptoSentApiLogObjectType == 'crypto_sent') {
                                        $cryptoReceivedApiLogSql->object_type = 'crypto_received';
                                    } elseif ($getCryptoSentApiLogObjectType == 'crypto_exchange_from') {
                                        $cryptoReceivedApiLogSql->object_type = 'crypto_exchange_to';
                                    } elseif ($getCryptoSentApiLogObjectType == 'fiat_exchange_from') {
                                        $cryptoReceivedApiLogSql->object_type = 'fiat_exchange_to';
                                    }
                                    $cryptoReceivedApiLogSql->network = $responseNetwork;
                                    $cryptoReceivedApiLogSql->payload = $enCodedResponseData;
                                    $cryptoReceivedApiLogSql->confirmations = $responseConfirmations;
                                    $cryptoReceivedApiLogSql->save();

                                    // Update the blockio wallet balance who received the coin
                                    $getAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($responseNetwork, $responseAddress);

                                    $getWalletAddressCryptoApiLogSql = $getWalletAddressCryptoApiLog = CryptoAssetApiLog::with(['wallet:id,user_id'])->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();

                                    if (!$getWalletAddressCryptoApiLogSql->isEmpty()) {
                                        $walletId = $getWalletAddressCryptoApiLog[0]->wallet->id;

                                        if (count($getWalletAddressCryptoApiLog) > 0) {
                                            $updateReceiverWalletBalanceSql = Wallet::where('id', $walletId)->update(['balance' => $getAddressBalance]);
                                        }
                                    }
                                    DB::commit();
                                } catch (Exception $e) {
                                    DB::rollback();
                                }
                            }
                        } else {
                            // Coint sent from external sourcres
                            $getCryptoSentApiLogSql = $getCryptoSentApiLog = CryptoAssetApiLog::with('wallet:id,user_id')->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();

                            // If found the record, the notification for user crypto received, otherwise admin received the coin
                            if (count($getCryptoSentApiLogSql) > 0) {
                                $getNetworkCurrency = \App\Models\Currency::where(['code' => $responseNetwork])->first(['id']);
                                // Get the user who received the coin
                                $cryptoReceiverFromUnknown = \App\Models\User::where(['id' => $getCryptoSentApiLogSql[0]->wallet->user_id])->first();

                                try {
                                    DB::beginTransaction();
                                    $transaction = new \App\Models\Transaction();
                                    $transaction->user_id             = $cryptoReceiverFromUnknown->id;
                                    $transaction->end_user_id         = NULL;
                                    $transaction->currency_id         = $getNetworkCurrency->id;
                                    $transaction->payment_method_id   = BlockIo;
                                    $transaction->uuid                = unique_code();
                                    $transaction->transaction_type_id = Crypto_Received;
                                    $transaction->subtotal            = $responseAmountReceived;
                                    $transaction->total               = $responseAmountReceived;
                                    $transaction->status              = 'Pending';
                                    $transaction->save();

                                    $responseData['receiverAddress'] = json_decode($getCryptoSentApiLog[0]->payload)->address;
                                    $enCodedResponseData = json_encode($responseData);

                                    $cryptoReceivedApiLogSql = new \App\Models\CryptoAssetApiLog();
                                    $cryptoReceivedApiLogSql->payment_method_id = BlockIo;
                                    $cryptoReceivedApiLogSql->object_id         = $transaction->id;
                                    $cryptoReceivedApiLogSql->object_type       = 'crypto_received';
                                    $cryptoReceivedApiLogSql->network           = $responseNetwork;
                                    $cryptoReceivedApiLogSql->payload           = $enCodedResponseData;
                                    $cryptoReceivedApiLogSql->confirmations     = $responseConfirmations;
                                    $cryptoReceivedApiLogSql->save();
                                    DB::commit();
                                } catch (Exception $e) {
                                    DB::rollBack();
                                }

                            } else {
                                \Log::info('Admin');
                            }
                        }
                    } else {
                        // Amount who sent
                        $getCryptoSentApiLogSql = $getCryptoSentApiLog = CryptoAssetApiLog::where(['network' => $responseNetwork, 'object_type' => 'crypto_sent'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseTxid . '[[:>:]]'])->get();

                        if (count($getCryptoSentApiLog) > 0) {
                            $getCryptoSentApiLogObjectId        = $getCryptoSentApiLog[0]->object_id;
                            $getCryptoSentApiLogNetwork         = json_decode($getCryptoSentApiLog[0]->payload)->network;
                            $getCryptoSentApiLogTxid            = json_decode($getCryptoSentApiLog[0]->payload)->txid;
                            $getCryptoSentApiLogReceiverAddress = json_decode($getCryptoSentApiLog[0]->payload)->receiverAddress;
                            $getCryptoSentApiLogSenderAddress   = json_decode($getCryptoSentApiLog[0]->payload)->senderAddress;

                            if ($responseNetwork == $getCryptoSentApiLogNetwork && $responseTxid == $getCryptoSentApiLogTxid && $responseAddress == $getCryptoSentApiLogSenderAddress) {

                                $receiverAddressCheck = CryptoAssetApiLog::where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $getCryptoSentApiLogReceiverAddress . '[[:>:]]'])->get();

                                $cryptoAssetSettings = CryptoAssetSetting::where(['network' => $responseNetwork])->first(['network_credentials']);
                                $merchantAddress = json_decode($cryptoAssetSettings->network_credentials)->address;

                                if (count($receiverAddressCheck) == 0 && ($getCryptoSentApiLogReceiverAddress !=  $merchantAddress)) {
                                    $updateCryptoApiLogsConfirmationsSql = CryptoAssetApiLog::where('object_id', $getCryptoSentApiLogObjectId)->update(['confirmations' => $responseConfirmations]);
                                }
                            }
                        }
                    }
                } else {
                    // Fetching the records of same blockIO txid (sent - receive both which confimation last stage was 1)
                    $getCryptoSentReceivedApiLogsSql = $getCryptoSentReceivedApiLogs = CryptoAssetApiLog::where(['confirmations' => 1, 'network' => $responseNetwork])->whereIn('object_type', ['crypto_sent', 'crypto_received', 'crypto_exchange_from', 'crypto_exchange_to', 'fiat_exchange_from', 'fiat_exchange_to'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseTxid . '[[:>:]]'])->get();
                    if (count($getCryptoSentReceivedApiLogsSql) > 0) {
                        foreach ($getCryptoSentReceivedApiLogs as $getCryptoSentReceivedApiLog) {
                            $getCryptoSentReceivedApiLogNetwork         = json_decode($getCryptoSentReceivedApiLog->payload)->network;
                            $getCryptoSentReceivedApiLogTxid            = json_decode($getCryptoSentReceivedApiLog->payload)->txid;
                            $getCryptoSentReceivedApiLogReceiverAddress = json_decode($getCryptoSentReceivedApiLog->payload)->receiverAddress;
                            $getCryptoSentReceivedApiLogSenderAddress   = isset(json_decode($getCryptoSentReceivedApiLog->payload)->senderAddress) ? json_decode($getCryptoSentReceivedApiLog->payload)->senderAddress : '';

                            // Matching network (BTC, LTC, DOGE...) & TransactionID
                            if ($responseNetwork == $getCryptoSentReceivedApiLogNetwork && $responseTxid == $getCryptoSentReceivedApiLogTxid) {

                                try {
                                    DB::beginTransaction();
                                    // Crypto Sent Receive
                                    if (!empty($getCryptoSentReceivedApiLogSenderAddress) && ($responseAddress == $getCryptoSentReceivedApiLogReceiverAddress || $responseAddress == $getCryptoSentReceivedApiLogSenderAddress)) {

                                        $getCryptoSentReceivedApiLogObjectId = $getCryptoSentReceivedApiLog->object_id;
                                        // Confirmatin update [1 to 3/5/10]
                                        $updateCryptoApiLogsConfirmationsSql = CryptoAssetApiLog::where(['object_id' => $getCryptoSentReceivedApiLogObjectId, 'confirmations' => 1])->update(['confirmations' => $responseConfirmations]);

                                        // Transaction status update
                                        $updateCryptoApiLogsConfirmations = Transaction::where('id', $getCryptoSentReceivedApiLogObjectId)->update(['status' => 'Success']);

                                        // Wallet balance update who received the coin
                                        $getWalletAddressCryptoApiLogSql = $getWalletAddressCryptoApiLog = CryptoAssetApiLog::with(['wallet:id,user_id'])->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();
                                        if (count($getWalletAddressCryptoApiLog) > 0) {
                                            $getAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($responseNetwork, $responseAddress);
                                            $getWalletAddressCryptoApiLogWalletId = $getWalletAddressCryptoApiLog[0]->wallet->id;
                                            $updateReceiverWalletBalanceSql = Wallet::where('id', $getWalletAddressCryptoApiLogWalletId)->update(['balance' => $getAddressBalance]);
                                        }
                                    } else {
                                        $getCryptoSentReceivedApiLogObjectId = $getCryptoSentReceivedApiLog->object_id;

                                        $updateCryptoAssetApiLogsConfirmationsSql = CryptoAssetApiLog::where(['object_id' => $getCryptoSentReceivedApiLogObjectId, 'confirmations' => 1])->update(['confirmations' => $responseConfirmations]);

                                        $updateCryptoApiLogsConfirmations = Transaction::where('id', $getCryptoSentReceivedApiLogObjectId)->update(['status' => 'Success']);

                                        $getAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($responseNetwork, $responseAddress);

                                        $getWalletAddressCryptoApiLogSql = $getWalletAddressCryptoAssetApiLog = CryptoAssetApiLog::with(['wallet:id,user_id'])->where(['network' => $responseNetwork, 'object_type' => 'wallet_address'])->whereRaw('payload REGEXP ?', ['[[:<:]]' . $responseAddress . '[[:>:]]'])->get();

                                        if (count($getWalletAddressCryptoAssetApiLog) > 0) {
                                            $getWalletAddressCryptoAssetApiLogWalletId = $getWalletAddressCryptoAssetApiLog[0]->wallet->id;

                                            $updateReceiverWalletBalanceSql = Wallet::where(['id' => $getWalletAddressCryptoAssetApiLogWalletId])->update(['balance' => $getAddressBalance]);
                                        }
                                    }
                                    DB::commit();
                                } catch (Exception $e) {
                                    DB::rollback();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
