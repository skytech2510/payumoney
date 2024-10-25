<?php

namespace Modules\TatumIo\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Transaction,
    Wallet,
    CryptoAssetApiLog
};
use Exception;
use Modules\TatumIo\Class\TatumIoTransaction;

class TatumIoNotificationController extends Controller
{
    protected $tatumIo;
    protected $data;
    protected $transactionDetails;

    public function balanceNotification(Request $request)
    {
        \Log::info($request->all());

        try {

            $jsonData = $request->getContent();

            $this->data = json_decode($jsonData);

            $apiLog = tatumGetCryptoTransactionApiLog($this->data->txId);

            \Log::info($apiLog);

            $this->userCryptoWalletUpdate($this->data->address);

            if (!empty($apiLog)) {
                $this->cryptoReceivedUpdateLog($apiLog);
            }

            if (!$apiLog) {
                $this->cryptoReceivedAnonymous();
            }

        } catch (Exception $th) {
            \Log::info($th->getMessage().$th->getLine());
        }
    }

    private function userCryptoWalletUpdate($address)
    {
        $tatumWalletApiLog = tatumGetWalletApiLog($address);

        if (!empty($tatumWalletApiLog)) {

            $this->tatumIo = new TatumIoTransaction($tatumWalletApiLog->network);

            $this->tatumIo->tatumIoAsset();

            $this->tatumIo->checkUserTatumWallet(optional($tatumWalletApiLog->wallet)->user_id);

            $balance = $this->tatumIo->getAddressBalance($address);

            Wallet::where(
                [
                    'user_id' => optional($tatumWalletApiLog->wallet)->user_id,
                    'currency_id' => optional($tatumWalletApiLog->wallet)->currency_id
                ]
            )->update(['balance' => $balance]);
        }

        return $tatumWalletApiLog;
    }

    private function cryptoReceivedUpdateLog($apiLog)
    {
        if (!empty($apiLog) && ($apiLog->confirmations == 0) ) {

            $transaction = Transaction::where([
                'id' => optional($apiLog->transaction)->id,
                'transaction_type_id' => Crypto_Sent,
                'status' => 'Pending']
            )->first();

            if (!empty($transaction) && ($transaction->status == 'Pending')) {

                $this->tatumIo = new TatumIoTransaction($apiLog->network);

                $this->tatumIo->tatumIoAsset();

                $this->transactionDetails = $this->tatumIo->transactionView($this->data->txId);


                $apiLog->confirmations = 7;

                $payload = json_decode($apiLog->payload);
                $apiLog->payload = json_encode($payload);
                $apiLog->save();

                $transaction->status = 'Success';
                $transaction->save();


                $payload2['txId'] = $payload->txId;
                $payload2['senderAddress'] = $payload->senderAddress;
                $payload2['receiverAddress'] = $payload->receiverAddress;

                if (isset($payload->senderAddress)) {
                    $this->userCryptoWalletUpdate($payload->senderAddress);
                }
                if (isset($payload->receiverAddress)) {
                    $this->userCryptoWalletUpdate($payload->receiverAddress);
                }

                \Log::info('ok');


                if ($transaction->end_user_id && $transaction->user_id ) {

                    $cryptoReceiveTransaction = Transaction::where([
                        'uuid' =>  $transaction->uuid,
                        'payment_method_id' => TatumIo,
                        'transaction_type_id' => Crypto_Received
                    ])->first();

                    $transactionB = ($cryptoReceiveTransaction) ? $cryptoReceiveTransaction : new Transaction() ;
                    $transactionB->user_id = $transaction->end_user_id;
                    $transactionB->end_user_id = $transaction->user_id;
                    $transactionB->currency_id = $transaction->currency_id;
                    $transactionB->transaction_type_id = Crypto_Received;
                    $transactionB->subtotal = $transaction->subtotal;
                    $transactionB->total = $transaction->subtotal;
                    $transactionB->uuid = $transaction->uuid;
                    $transactionB->payment_method_id = TatumIo;
                    $transactionB->status = 'Success';
                    $transactionB->save();

                    $cryptoReceiveApiLog  = (new CryptoAssetApiLog())
                        ->where(['payment_method_id' => TatumIo, 'object_type' => 'crypto_received'])
                        ->whereJsonContains('payload->txId', $payload->txId)
                        ->first();

                    $apiLogB = ($cryptoReceiveApiLog) ? $cryptoReceiveApiLog : new CryptoAssetApiLog() ;
                    $apiLogB->object_type = 'crypto_received';
                    $apiLogB->payment_method_id  = TatumIo;
                    $apiLogB->object_id  = $transactionB->id;
                    $apiLogB->network  = $apiLog->network;
                    $apiLogB->confirmations  = 7;
                    $apiLogB->payload = json_encode($payload2);
                    $apiLogB->save();

                }

                return true;

            }

            return true;
        }
    }

    private function cryptoReceivedAnonymous()
    {

        $tatumWalletApiLog = $this->userCryptoWalletUpdate($this->data->address);

        \Log::info( $tatumWalletApiLog);


        if (!empty($tatumWalletApiLog)) {

            $arr = [
                'userId' => optional($tatumWalletApiLog->wallet)->user_id,
                'endUserId' => null,
                'currencyId' => optional($tatumWalletApiLog->wallet)->currency_id,
                'uniqueCode' => unique_code(),
                'transactionType' => Crypto_Received,
                'amount' => $this->data->amount,
                'status' => 'Success',
            ];

            \Log::info($arr);


            $this->transactionDetails = $this->tatumIo->transactionView($this->data->txId);

            \Log::info($this->transactionDetails);

            $transactionId = $this->tatumIo->createCryptoTransaction($arr);

            \Log::info($transactionId);

            $arr['transactionId'] = $transactionId;
            $arr['walletCurrencyCode'] = $tatumWalletApiLog->network;
            $arr['object_type'] = 'crypto_received';
            $arr['confirmations'] = 7;
            $arr['withdrawInfoData']['txId'] = $this->data->txId;
            $arr['withdrawInfoData']['receiverAddress'] = $this->data->address;
            $arr['withdrawInfoData']['senderAddress'] = $this->transactionDetails['senderAddress'] ?? '';

            $cryptoApiLog = $this->tatumIo->createWithdrawalOrSendCryptoApiLog($arr);

            \Log::info( $cryptoApiLog);

        }

        return true;
    }
}
