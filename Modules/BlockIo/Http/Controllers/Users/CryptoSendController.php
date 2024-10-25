<?php

namespace Modules\BlockIo\Http\Controllers\Users;

use Modules\BlockIo\Classes\BlockIo;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DB, Exception;

class CryptoSendController extends Controller
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

    public function sendCryptoCreate($walletCurrencyCode, $walletId)
    {
        // destroying cryptoEncArr after loading create poge from reload of crypto success page
        if (!empty(session('cryptoEncArr'))) {
            session()->forget('cryptoEncArr');
        }

        //set the session for validating the action
        setActionSession();

        $walletCurrencyCode = decrypt($walletCurrencyCode);
        $walletId = decrypt($walletId);
        $data['walletCurrencyCode'] = $walletCurrencyCode;
        $data['walletId'] = $walletId;
        $data['currencyType'] = \App\Models\Currency::whereCode($walletCurrencyCode)->value('type');

        // Check crypto currency status
        if ($data['walletCurrencyCode'] != '') {
            $getCryptoCurrencyStatus = $this->blockIo->getCryptoCurrencyStatus($data['walletCurrencyCode']);

            if ($getCryptoCurrencyStatus == 'Inactive') {
                $data['message'] =  __(':x is inactive.', ['x' => $data['walletCurrencyCode']]);
                return view('user_dashboard.users.check_crypto_currency_status', $data);
            } else {
                $data['senderAddress'] = $this->blockIo->getUserCryptoAddress($walletId);
                return view('blockio::user.crypto.send.create', $data);
            }
        } else {
            return redirect('wallet-list');
        }
    }

    public function sendCryptoConfirm(Request $request)
    {
        actionSessionCheck();

        $walletCurrencyCode = decrypt($request->walletCurrencyCode);
        $walletId           = decrypt($request->walletId);
        $senderAddress      = decrypt($request->senderAddress);
        $amount             = $request->amount;
        $receiverAddress    = $request->receiverAddress;
        $userId             = auth()->user()->id;
        $currency           = $this->currency->getCurrency(['code' => $walletCurrencyCode], ['id', 'symbol']);
        $priority           = $request->priority;

        $request['walletCurrencyCode'] = $walletCurrencyCode;
        $request['senderAddress'] = $senderAddress;
        $request['receiverAddress'] = $receiverAddress;
        $request['receiverAddress'] = $receiverAddress;

        $rules = array(
            'receiverAddress' => 'required',
            'amount' => 'required',
        );
        $fieldNames = array(
            'receiverAddress' => __("Address"),
            'amount' => __("Amount"),
        );

        // Backend validation of receiver network address validity - starts
        $addressValidity = $this->validateCryptoAddress($request)->getData();
        if ($addressValidity->status && $addressValidity->status == 401) {
            return back()->withErrors($addressValidity->message)->withInput();
        }

        // Backend Validations of minimum amount
        $minimumAmountCheck = $this->blockIo->minimumAmountCheck($walletCurrencyCode, $amount)->getData();
        if (isset($minimumAmountCheck->status) && $minimumAmountCheck->status == 401) {
            return back()->withErrors($minimumAmountCheck->message)->withInput();
        }

        // Backend Validations of sender crypto wallet balance - starts
        $validateUserBalanceAgainstAmount = $this->validateUserBalanceAgainstAmount($request);
        if ($validateUserBalanceAgainstAmount->getData()->status == 401) {
            return back()->withErrors($validateUserBalanceAgainstAmount->getData()->message)->withInput();
        }
        //Backend Validations of sender crypto wallet balance - ends

        $validator = \Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            try {
                $cryptoTrxData = $this->blockIo->cryptoTrxData($walletCurrencyCode, $receiverAddress, $amount, $priority, $senderAddress, $userId, $currency);

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
                $data['currencyId'] = $currency->id;

                return view('blockio::user.crypto.send.confirmation', $data);
            } catch (Exception $e) {
                return back()->withErrors(__($e->getMessage()))->withInput();
            }
        }
    }


    public function sendCryptoSuccess(Request $request)
    {
        $cryptoTrx = session('cryptoTrx');
        if (empty($cryptoTrx)) {
            return redirect()->route('user.crypto_send.create', [encrypt(session('cryptoEncArr')['walletCurrencyCode']), encrypt(session('cryptoEncArr')['walletId'])]);
        }
        
        //initializing session
        actionSessionCheck();

        // Checking if these extensions 'GMP', 'cURL', 'mbstring', 'bcmath' are enabled or not
        $extensionCheck = $this->blockIo->extensionCheck();
        if (isset($extensionCheck)) {
            $this->helper->one_time_message('error', $extensionCheck->getData()->message);
            return redirect()->route('user.crypto_send.create', [encrypt(session('cryptoEncArr')['walletCurrencyCode']), encrypt(session('cryptoEncArr')['walletId'])]);
        }

        //Backend Validations of sender crypto wallet balance -- for multiple tab submit
        $request['walletCurrencyCode'] = session('cryptoEncArr')['walletCurrencyCode'];
        $request['senderAddress']      = $cryptoTrx['senderAddress'];
        $request['receiverAddress']    = $cryptoTrx['receiverAddress'];
        $request['amount']             = $cryptoTrx['amount'];


        $validateUserBalanceAgainstAmount = $this->validateUserBalanceAgainstAmount($request);
        if ($validateUserBalanceAgainstAmount->getData()->status == 401) {
            $this->helper->one_time_message('error', $validateUserBalanceAgainstAmount->getData()->message);
            return redirect()->route('user.crypto_send.create', [encrypt(session('cryptoEncArr')['walletCurrencyCode']), encrypt(session('cryptoEncArr')['walletId'])]);
        } else {
            
            // Main process of crypto send
            $withdrawInfoResponse = $this->blockIo->cryptoSendProcess($request['walletCurrencyCode'], $cryptoTrx)->getData();

            if (isset($withdrawInfoResponse->status) && $withdrawInfoResponse->status == 401) {
                $this->helper->one_time_message('error', $withdrawInfoResponse->message);
                return redirect()->route('user.crypto_send.create', [encrypt(session('cryptoEncArr')['walletCurrencyCode']), encrypt(session('cryptoEncArr')['walletId'])]);
            }

            $withdrawInfo = $withdrawInfoResponse->data;

            try {
                DB::beginTransaction();

                //Create Crypto Transaction
                $createCryptoTransactionId = $this->blockIo->createCryptoTransaction($cryptoTrx);

                //Create new withdrawal/Send crypt api log
                $cryptoTrx['transactionId']      = $createCryptoTransactionId;
                $cryptoTrx['walletCurrencyCode'] = $request['walletCurrencyCode'];
                $cryptoTrx['withdrawInfoData']   = $withdrawInfo->data;

                //need this for showing send address against Crypto Receive Type Transaction in user/admin panel
                $cryptoTrx['withdrawInfoData']->senderAddress = $cryptoTrx['senderAddress'];

                //need this for nodejs websocket server
                $cryptoTrx['withdrawInfoData']->receiverAddress = $cryptoTrx['receiverAddress'];
                $this->blockIo->createWithdrawalOrSendCryptoApiLog($cryptoTrx);

                //Update Sender Network Address Balance
                $this->blockIo->getUpdatedSendWalletBalance($cryptoTrx);

                DB::commit();

                // Initially after 1 confirmations of blockio response, websocket queries will be executed
                $cryptConfirmationsArr = [
                    'BTC'      => 1,
                    'BTCTEST'  => 1,
                    'DOGE'     => 1,
                    'DOGETEST' => 1,
                    'LTC'      => 1,
                    'LTCTEST'  => 1,
                ];
                $data['confirmations']      = $cryptConfirmationsArr[$request['walletCurrencyCode']];
                $data['walletCurrencyCode'] = $request['walletCurrencyCode'];
                $data['receiverAddress']    = $cryptoTrx['receiverAddress'];
                $data['currencySymbol']     = $cryptoTrx['currencySymbol'];
                $data['currencyId']         = $cryptoTrx['currencyId'];
                $data['amount']             = $cryptoTrx['amount'];
                $data['transactionId']      = $cryptoTrx['transactionId'];
                $data['walletId']           = session('cryptoEncArr')['walletId'];

                // Don't flush/forget cryptoEncArr from session as it will be cleared on create method
                session()->forget(['cryptoTrx']);
                clearActionSession();

                return view('blockio::user.crypto.send.success', $data);
            } catch (Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect()->route('user.crypto_send.create', [encrypt(session('cryptoEncArr')['walletCurrencyCode']), encrypt(session('cryptoEncArr')['walletId'])]);
            }
        }
    }


    // Validate crypto address
    public function validateCryptoAddress(Request $request)
    {
        return $this->blockIo->addressValidityCheck($request->walletCurrencyCode, $request->receiverAddress, auth()->user()->id);
    }

    // Validate User Balance against
    public function validateUserBalanceAgainstAmount(Request $request)
    {
        return $this->blockIo->userBalanceCheck($request->walletCurrencyCode, $request->amount, $request->senderAddress, $request->receiverAddress, $request->priority);
    }
}
