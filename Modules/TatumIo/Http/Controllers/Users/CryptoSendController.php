<?php

namespace Modules\TatumIo\Http\Controllers\Users;

use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TatumIo\Http\Requests\CryptoSendRequest;
use Modules\TatumIo\Services\CryptoSendService;

class CryptoSendController extends Controller
{
    protected $helper;
    protected $service;

    public function __construct()
    {
        $this->helper = new \App\Http\Helpers\Common();
        $this->service = new CryptoSendService();
    }

    public function sendCryptoCreate($walletCurrencyCode, $walletId)
    {
        // destroying cryptoEncArr after loading create poge from reload of crypto success page
        if (!empty(session('cryptoEncArr'))) {
            session()->forget('cryptoEncArr');
        }

        //set the session for validating the action
        setActionSession();

        try {

            $address = $this->service->userAddress(decrypt($walletCurrencyCode));

            $currency = $this->service->getCryptoCurrency();

            $data = [
                'currencyType' => $currency->type,
                'senderAddress' => encrypt($address['senderAddress']),
                'walletCurrencyCode' => decrypt($walletCurrencyCode),
                'walletId' => decrypt($walletId),
            ];

            return view('tatumio::user.crypto.send.create', $data);

        } catch (Exception $th) {
            $data['message'] = __($th->getMessage());
            return redirect('wallet-list');
        }

    }

    public function sendCryptoConfirm(CryptoSendRequest $request)
    {
        actionSessionCheck();

        $walletCurrencyCode = decrypt($request->walletCurrencyCode);
        $walletId = decrypt($request->walletId);
        $senderAddress = decrypt($request->senderAddress);
        $amount = $request->amount;
        $receiverAddress = $request->receiverAddress;
        $priority = $request->priority;

        try {

            $cryptoTrxData = $this->service->userCryptoBalanceCheck(
                $walletCurrencyCode, $amount, decrypt($senderAddress), $receiverAddress, $priority
            );

            session(['cryptoTrx' => $cryptoTrxData]);

            //Put currency code and wallet into session id for create route & destroy it after loading create poge - starts
            $cryptoEncArr = [];
            $cryptoEncArr['walletCurrencyCode'] = $walletCurrencyCode;
            $cryptoEncArr['walletId'] = $walletId;
            session(['cryptoEncArr' => $cryptoEncArr]);
            // Data for confirm page - starts
            $data['cryptoTrx'] = $cryptoTrxData;
            $data['walletCurrencyCode'] = $walletCurrencyCode;
            $data['walletId'] = $walletId;
            $data['currencyId'] = $cryptoTrxData['currencyId'];

            return view('tatumio::user.crypto.send.confirmation', $data);

        } catch (Exception $e) {
            return back()->withErrors(__($e->getMessage()))->withInput();
        }

    }

    public function sendCryptoSuccess(Request $request)
    {
        $cryptoTrx = session('cryptoTrx');

        if (empty($cryptoTrx)) {
            return redirect()->route('user.crypto_send.create', [encrypt(session('cryptoEncArr')['walletCurrencyCode']), encrypt(session('cryptoEncArr')['walletId'])]);
        }

        actionSessionCheck();


        try {

            $cryptoTrx  = $this->service->sendCryptoFinal(
                $cryptoTrx['network'], $cryptoTrx['receiverAddress'], $cryptoTrx['amount'], $cryptoTrx['priority'], $cryptoTrx['senderAddress']
            );

            // Initially after 1 confirmations of blockio response, websocket queries will be executed
            $cryptConfirmationsArr = [
                'BTC' => 1,
                'BTCTEST' => 1,
                'DOGE' => 1,
                'DOGETEST' => 1,
                'LTC' => 1,
                'LTCTEST' => 1,
                'TRX' => 1,
                'TRXTEST' => 1,
                'ETH' => 1,
                'ETHTEST' => 1,
            ];

            $data['confirmations'] = $cryptConfirmationsArr[$cryptoTrx['network']];
            $data['walletCurrencyCode'] = $cryptoTrx['network'];
            $data['receiverAddress'] = $cryptoTrx['receiverAddress'];
            $data['currencySymbol'] = $cryptoTrx['currencySymbol'];
            $data['currencyId'] = $cryptoTrx['currencyId'];
            $data['amount'] = $cryptoTrx['amount'];
            $data['transactionId'] = $cryptoTrx['transactionId'];
            $data['walletId'] = session('cryptoEncArr')['walletId'];

            // Don't flush/forget cryptoEncArr from session as it will be cleared on create method
            session()->forget(['cryptoTrx']);
            clearActionSession();

            return view('tatumio::user.crypto.send.success', $data);


        } catch (Exception $e) {

            $this->helper->one_time_message('error', $e->getMessage());

            return redirect()->route('tatumio.user.crypto_send.create', [encrypt(session('cryptoEncArr')['walletCurrencyCode']), encrypt(session('cryptoEncArr')['walletId'])]);
        }

    }

    // Validate crypto address
    public function validateCryptoAddress(Request $request)
    {
        $network = $request->walletCurrencyCode;
        $address = $request->receiverAddress;
        return $this->service->cryptoAddressValidation($network, $address, 'web');

    }

    //validate merchant Address Balance Against Amount
    public function validateUserBalanceAgainstAmount(Request $request)
    {
        $sender = decrypt($request->senderAddress);
        $receiver = $request->receiverAddress;
        $amount = $request->amount;
        $priority = $request->priority;
        $network = $request->walletCurrencyCode;

        try {

            $this->service->userCryptoBalanceCheck(
                $network, $amount, $sender, $receiver, $priority
            );

            return true;

        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
