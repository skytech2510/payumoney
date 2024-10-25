<?php

namespace Modules\BlockIo\Http\Controllers\Users;

use App\Models\Transaction;
use Illuminate\Routing\Controller;
use Modules\BlockIo\Classes\BlockIo;

class CryptoReceiveController extends Controller
{
    protected $blockIo;

    public function __construct()
    {
        $this->blockIo = new BlockIo();
    }

    public function receiveCrypto($walletCurrencyCode, $walletId)
    {
        //set the session for validating the action
        setActionSession();

        $walletCurrencyCode = decrypt($walletCurrencyCode);
        $walletId = decrypt($walletId);

        $data['walletCurrencyCode'] = strtoupper($walletCurrencyCode);

        // Check crypto currency status
        $getCryptoCurrencyStatus = $this->blockIo->getCryptoCurrencyStatus($data['walletCurrencyCode']);

        if ($getCryptoCurrencyStatus == 'Inactive') {
            $data['message'] =  __(':x is inactive.', ['x' => $data['walletCurrencyCode']]);
            return view('user_dashboard.users.check_crypto_currency_status', $data);
        } else {
            //get user's wallet address
            $address = $this->blockIo->getUserCryptoAddress($walletId);
            $data['address'] = encrypt($address);
            return view('blockio::user.crypto.receive.create', $data);
        }
    }

    public function cryptoSentReceivedTransactionPrintPdf($id)
    {
        $id = decrypt($id);
        $data['transaction'] = $transaction = Transaction::with(['currency:id,symbol', 'cryptoAssetApiLog:id,object_id,payload,confirmations'])->where(['id' => $id])->first();

        // Get crypto api log details for Crypto_Sent & Crypto_Received (via custom relationship)
        if (!empty($transaction->cryptoAssetApiLog)) {
            $getCryptoDetails = $this->blockIo->getCryptoPayloadConfirmationsDetails($transaction->transaction_type_id, $transaction->cryptoAssetApiLog->payload, $transaction->cryptoAssetApiLog->confirmations);
            if (count($getCryptoDetails) > 0) {
                if (isset($getCryptoDetails['senderAddress'])) {
                    $data['senderAddress']   = $getCryptoDetails['senderAddress'];
                }
                if (isset($getCryptoDetails['receiverAddress'])) {
                    $data['receiverAddress'] = $getCryptoDetails['receiverAddress'];
                }

                $data['network_fee'] = isset($getCryptoDetails['network_fee']) ? $getCryptoDetails['network_fee'] : 0.00000000;
                $data['confirmations'] = $getCryptoDetails['confirmations'];
            }
        }

        generatePDF('blockio::user.transactions.crypto_transaction_pdf', 'crypto-transaction_', $data);
    }
}
