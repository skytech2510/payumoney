<?php

namespace Modules\TatumIo\Http\Controllers\Users;

use App\Models\Transaction;
use Exception;
use Illuminate\Routing\Controller;
use Modules\TatumIo\Class\TatumIoTransaction;

class CryptoReceiveController extends Controller
{
    protected $tatumIo;

    public function receiveCrypto($walletCurrencyCode, $walletId)
    {
        //set the session for validating the action
        setActionSession();

        $walletCurrencyCode = decrypt($walletCurrencyCode);
        $walletId = decrypt($walletId);

        $user_id = auth()->id();

        $data['walletCurrencyCode'] = $network = strtoupper($walletCurrencyCode);

        try {

            $this->tatumIo = new TatumIoTransaction($network);
            $this->tatumIo->tatumIoAsset();
            $this->tatumIo->checkUserTatumWallet($user_id);
            $address = $this->tatumIo->getUserAddress();

            $data['address'] = encrypt($address);
            return view('tatumio::user.crypto.receive.create', $data);

        } catch (Exception $th) {
            $data['message'] = __($th->getMessage());
            return view('user_dashboard.users.check_crypto_currency_status', $data);
        }

    }

    public function cryptoSentReceivedTransactionPrintPdf($id)
    {
        $id = decrypt($id);
        $data['transaction'] = $transaction = Transaction::with(['currency:id,symbol,code', 'cryptoAssetApiLog:id,object_id,payload,confirmations'])->where(['id' => $id])->first();

        if (!empty($transaction->cryptoAssetApiLog)) {

            $payLoad = json_decode($transaction->cryptoAssetApiLog->payload);
            $data['senderAddress'] = $payLoad->senderAddress;
            $data['receiverAddress'] = $payLoad->receiverAddress;
            $data['network_fee'] = isset($payLoad->network_fee) ? $payLoad->network_fee : 0.000000;
            $data['confirmations'] = optional($transaction->cryptoAssetApiLog)->confirmations;
        }

        generatePDF('tatumio::user.transactions.crypto_transaction_pdf', 'crypto-transaction_', $data);
    }
}
