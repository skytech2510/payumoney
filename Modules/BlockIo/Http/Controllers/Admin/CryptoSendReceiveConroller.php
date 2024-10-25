<?php

namespace Modules\BlockIo\Http\Controllers\Admin;

use Modules\BlockIo\Classes\BlockIo;
use Illuminate\Routing\Controller;
use App\Models\CryptoProvider;
use Illuminate\Http\Request;
use Exception, DB;

class CryptoSendReceiveConroller extends Controller
{
    protected $blockIo;
    protected $currency;
    protected $helper;

    public function __construct()
    {
        $this->blockIo = new BlockIo();
        $this->currency = new \App\Models\Currency();
        $this->helper = new \App\Http\Helpers\Common();
    }
    
    /**
     * Crypto Sent via admin start from here
     * 
     */
    
    /* Crypto Sent :: Create */
    public function eachUserCryptoSentCreate($network)
    {
        $data['network'] = $network = decrypt($network);
        $data['currency'] = \App\Models\Currency::where(['code' => $network, 'type' => 'crypto_asset'])->first(['id', 'type', 'status']);
        $currencyId = $data['currency']->id;

        if ($data['currency']->status != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the :x first for making any transaction', ['x' => $network]));
            return redirect()->route('admin.crypto_providers.list', 'BlockIo');
        }

        if (CryptoProvider::getStatus('BlockIo') != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the provider first for making any transaction'));
            return redirect()->route('admin.crypto_providers.list', 'BlockIo');
        }

        setActionSession();
        // Get those users who has selected network wallets
        $data['users'] = \App\Models\User::whereHas('wallets.cryptoAssetApiLogs', function($q) use ($currencyId, $network) {
            $q->where('wallets.currency_id', $currencyId);
            $q->where(['crypto_asset_api_logs.payment_method_id' => BlockIo, 'crypto_asset_api_logs.network' => $network]);
        })
        ->whereStatus('Active')
        ->get();

        $data['minBlockIoLimit'] = json_encode(getBlockIoMinLimit());

        return view('blockio::admin.crypto.send.create', $data);
    }

    /* Crypto Sent :: Confirm */
    public function eachUserCryptoSentConfirm(Request $request)
    {
        actionSessionCheck();

        $data['users'] = \App\Models\User::find($request->user_id, ['id', 'first_name', 'last_name']);

        $response = $this->cryptoSendReceiveConfirm($data, $request, 'send');
        
        if ($response['status'] == 401) {
            $this->helper->one_time_message('error', $response['message']);
            return redirect()->route('admin.crypto_send.create', ['code' => encrypt($request->network)]);
        }
        // For confirm page only
        $data['cryptoTrx'] = $response['cryptoTrx'];
        return view('blockio::admin.crypto.send.confirmation', $data);
        
    }

    /* Crypto Sent :: success */
    public function eachUserCryptoSentSuccess(Request $request)
    {
        actionSessionCheck();

        $res = $this->cryptoSendReceiveSuccess($request, 'send');
 
        if ($res['status'] == 401) {
            $this->helper->one_time_message('error', $res['message']);
            return redirect()->route('admin.crypto_send.create', [encrypt($request->network)]);
        }
        return view('blockio::admin.crypto.send.success', $res['data']);
    }

    /**
     * Crypto Receive via admin start from here
     *
     */

    /* Crypto Receive :: Create */
    public function eachUserCryptoReceiveCreate($network)
    {
        $data['menu'] = 'crypto_providers';
        $data['network'] = $network = decrypt($network);
        $data['currency'] = \App\Models\Currency::where(['code' => $network, 'type' => 'crypto_asset'])->first(['id', 'type', 'status']);
        $currencyId = $data['currency']->id;

        if ($data['currency']->status != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the :x first for making any transaction', ['x' => $network]));
            return redirect()->route('admin.crypto_providers.list', 'BlockIo');
        }

        if (CryptoProvider::getStatus('BlockIo') != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the provider first for making any transaction'));
            return redirect()->route('admin.crypto_providers.list', 'BlockIo');
        }

        setActionSession();
        $data['users'] = \App\Models\User::whereHas('wallets.cryptoAssetApiLogs', function($q) use ($currencyId, $network) {
            $q->where('wallets.currency_id', $currencyId);
            $q->where(['crypto_asset_api_logs.payment_method_id' => BlockIo, 'crypto_asset_api_logs.network' => $network]);
        })
        ->get();

        $data['minBlockIoLimit'] = json_encode(getBlockIoMinLimit());

        return view('blockio::admin.crypto.receive.create', $data);
    }

    /* Crypto Receive :: Confirm */
    public function eachUSerCryptoReceiveConfirm(Request $request)
    {
        actionSessionCheck();

        $data['menu'] = 'crypto_providers';
        $data['users'] = \App\Models\User::find($request->user_id, ['id', 'first_name', 'last_name']);

        $response = $this->cryptoSendReceiveConfirm($data, $request, 'receive');
        if ($response['status'] == 401) {
            $this->helper->one_time_message('error', $response['message']);
             return redirect()->route('admin.crypto_receive.create', ['code' => encrypt($request->network)]);
        }
        //for confirm page only
        $data['cryptoTrx'] = $response['cryptoTrx'];
        return view('blockio::admin.crypto.receive.confirmation', $data);
    }

    /* Crypto Receive :: Success */
    public function eachUserCryptoReceiveSuccess(Request $request)
    {
        actionSessionCheck();
        $res = $this->cryptoSendReceiveSuccess($request, 'receive');
        if ($res['status'] == 401) {
            $this->helper->one_time_message('error', $res['message']);
            return redirect()->route('admin.crypto_receive.create', [$request->network]);
        }

        return view('blockio::admin.crypto.receive.success', $res['data']);
    }
    
    /**
     * Common functions for Crypto Sent Receive starts from here
     *
     */

    public function cryptoSendReceiveConfirm($data, $request, $type)
    {
        $userId = $request->user_id;
        $network = $request->network;
        $amount = $request->amount;
        $merchantAddress = $request->merchantAddress;
        $userAddress = $request->userAddress;
        $currency = $this->currency->getCurrency(['code' => $network, 'type' => 'crypto_asset'], ['id', 'symbol']);

        //merge currency symbol with request array
        $request->merge(['currency_symbol' => $currency->symbol]);
        $request->merge(['currency_id' => $currency->id]);
        $request->merge(['user_full_name' => getColumnValue($data['users']) ]);

        //unset users - not needed in confirm page
        unset($data['users']);

        //Form back-end validations - starts
        if ($type === 'send') {
            $rules = array(
                'user_id'         => 'required',
                'merchantAddress' => 'required',
                'merchantBalance' => 'required',
                'userAddress'     => 'required',
                'amount'          => 'required',
            );
            $fieldNames = array(
                'user_id'         => __('User'),
                'merchantAddress' => __('Merchant Address'),
                'merchantBalance' => __('Merchant Balance'),
                'userAddress' => __('User Address'),
                'amount' => __('Amount'),
            );
        } elseif ($type === 'receive') {
            $rules = array(
                'userAddress'     => 'required',
                'userBalance'     => 'required',
                'merchantAddress' => 'required',
                'amount'          => 'required',
            );
            $fieldNames = array(
                'userAddress' => __('User Address'),
                'userBalance' => __('User Balance'),
                'merchantAddress' => __('User Address'),
                'amount' => __('Amount'),
            );
        }

        // Backend Validations of minimum amount
        $minimumAmountCheck = $this->blockIo->minimumAmountCheck($network, $amount)->getData();
        if (isset($minimumAmountCheck->status) && $minimumAmountCheck->status == 401) {
            return (array) $minimumAmountCheck;
        }

        //Backend validation of merchant & user network address validity & correct address - starts
        $checkMerchantNetworkAddress = $this->blockIo->checkNetworkAddressValidity($network, $merchantAddress);
        
        if (!$checkMerchantNetworkAddress) {
            return [
                'status' => 401,
                'message' => __('Invalid merchant :x address', ['x' => $network]),
            ];
        }
        //Backend validation of correct merchant network address
        $getMerchantNetworkAddress = $this->blockIo->getMerchantNetworkAddress($network);  
        if (empty($getMerchantNetworkAddress) || $merchantAddress != $getMerchantNetworkAddress) {
            return [
                'message' => __('Incorrect merchant :x address', ['x' => $network]),
                'status'  => 401,
            ];
        }
        //Backend validation of user network address validity
        $checkUserNetworkAddress = $this->blockIo->checkNetworkAddressValidity($network, $userAddress);
        if (!$checkUserNetworkAddress) {
            return [
                'message' => __('Invalid user :x address', ['x' => $network]),
                'status'  => 401,
            ];
        }
        //Backend validation of correct user network address
        $getUserNetworkWalletAddress = $this->blockIo->getUserNetworkWalletAddress($userId, $network);

        if ($userAddress != $getUserNetworkWalletAddress->getData()->userAddress) {
            return [
                'status'  => 401,
                'message' => __('Incorrect user :x address', ['x' => $network]),
            ];
        }
        //Backend validation of merchant & user network address validity & correct address - ends

        //Backend validation of merchant & user network address balance - starts
        if ($type === 'send') {
            //Backend merchant network address balance
            $getMerchantNetworkAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($network, $this->blockIo->getMerchantNetworkAddress($network));
            if ($request->merchantBalance != $getMerchantNetworkAddressBalance) {
                return [
                    'message' => __('Incorrect merchant :x balance', ['x' => $network]),
                    'status'  => 401,
                ];
            }
            //Backend merchant network address balance against amount
            $validateAddressBlnce = $this->validateMerchantAddressBalanceAgainstAmount($request);
        } elseif ($type === 'receive') {
            //Backend user network address balance
            $getUserNetworkAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($network, $getUserNetworkWalletAddress->getData()->userAddress);
            if ($request->userBalance != $getUserNetworkAddressBalance) {
                return [
                    'message' => __('Incorrect user :x balance', ['x' => $network]),
                    'status'  => 401,
                ];
            }
            //Backend user network address balance against amount
            $validateAddressBlnce = $this->validateUserAddressBalanceAgainstAmount($request);
        }

        if ($validateAddressBlnce->getData()->status == 401) {
            return [
                'status' => 401,
                'message' => $validateAddressBlnce->getData()->message,
            ];
        }
        //Backend validation of merchant & user network address balance - ends

        $validator = \Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);
        if ($validator->fails()) {
            return [
                'message' => $validator,
                'status'  => 401,
            ];
        } else {
            //Call network fee API of block io
            $priority = 'low';

            if ($request->priority == 'high') {
                $priority = 'high';
            } else if ($request->priority == 'medium') {
                $priority = 'medium';
            } else {
                $priority = 'low';
            }

            if ($type === 'send') {
                $getNetworkFeeEstimate = $this->blockIo->getNetworkFeeEstimate($network, $userAddress, $amount, $priority);
            } elseif ($type === 'receive') {
                $getNetworkFeeEstimate = $this->blockIo->getNetworkFeeEstimate($network, $merchantAddress, $amount, $priority);
            }

            //merge network fee with request array
            $request->merge(['network_fee' => $getNetworkFeeEstimate]);

            //Put data in session for success page
            session(['cryptoTrx' => $request->all()]);

            //for confirm page only
            $data['cryptoTrx'] = $request->only('currency_symbol', 'currency_id', 'network', 'amount', 'network_fee', 'user_id', 'user_full_name');
 

            return [
                'cryptoTrx' => $data['cryptoTrx'],
                'status'    => 200,
            ];
        }
    }

    public function cryptoSendReceiveSuccess($request, $type)
    {
        $network = $request->network;
        $cryptoTrx = session('cryptoTrx');

        if (empty($cryptoTrx)) {
            return [
                'message' => null,
                'network' => $network,
                'status'  => 401,
            ];
        }

        // Backend validation of sender crypto wallet balance -- for multiple tab submit
        $request['network']         = $cryptoTrx['network'];
        $request['merchantAddress'] = $cryptoTrx['merchantAddress'];
        $request['userAddress']     = $cryptoTrx['userAddress'];
        $request['amount']          = $cryptoTrx['amount'];
        $request['priority']        = $cryptoTrx['priority'];

        if ($type === 'send') {
            $validateAddressBlnceSuccess = $this->validateMerchantAddressBalanceAgainstAmount($request);
        }
        elseif ($type === 'receive') {
            $validateAddressBlnceSuccess = $this->validateUserAddressBalanceAgainstAmount($request);
        }

        if ($validateAddressBlnceSuccess->getData()->status == 401) {
            return [
                'status'  => 401,
                'network' => $network,
                'message' => $validateAddressBlnceSuccess->getData()->message,
            ];
        } else {

            try {
                $uniqueCode = unique_code();
                $arr = [
                    'walletCurrencyCode' => $cryptoTrx['network'],
                    'amount'             => $cryptoTrx['amount'],
                    'networkFee'         => $cryptoTrx['network_fee'],
                    'userId'             => null,
                    'endUserId'          => null,
                    'currencyId'         => $cryptoTrx['currency_id'],
                    'currencySymbol'     => $cryptoTrx['currency_symbol'],
                    'uniqueCode'         => $uniqueCode,
                ];

                if ($type === 'send') {
                    $arr['senderAddress']   = $cryptoTrx['merchantAddress'];
                    $arr['receiverAddress'] = $cryptoTrx['userAddress'];
                    $arr['endUserId']       = $cryptoTrx['user_id'];
                    $arr['priority']        = $cryptoTrx['priority'];
                } elseif ($type === 'receive') {
                    $arr['senderAddress']   = $cryptoTrx['userAddress'];
                    $arr['receiverAddress'] = $cryptoTrx['merchantAddress'];
                    $arr['userId']          = $cryptoTrx['user_id'];
                    $arr['priority']        = $cryptoTrx['priority'];
                }
             
                // Main process of crypto send
                $withdrawInfoResponse = $this->blockIo->cryptoSendProcess($arr['walletCurrencyCode'], $arr)->getData();

                if (isset($withdrawInfoResponse->status) && $withdrawInfoResponse->status == 401) {
                    return (array) $withdrawInfoResponse;
                }

                $withdrawInfo = $withdrawInfoResponse->data;

                DB::beginTransaction();

                // Create Merchant Crypto Transaction
                $createCryptoTransactionId = $this->blockIo->createCryptoTransaction($arr);

                // Create merchant new withdrawal/Send/Receive crypt api log
                $arr['transactionId']    = $createCryptoTransactionId;
                $arr['withdrawInfoData'] = $withdrawInfo->data;
                if ($type === 'send') {
                    // Need this for showing send address against Crypto Receive Type Transaction in user/admin panel
                    $arr['withdrawInfoData']->senderAddress = $cryptoTrx['merchantAddress'];

                    // Need this for nodejs websocket server
                    $arr['withdrawInfoData']->receiverAddress = $cryptoTrx['userAddress'];
                } elseif ($type === 'receive') {
                    $arr['withdrawInfoData']->senderAddress = $cryptoTrx['userAddress'];
                    $arr['withdrawInfoData']->receiverAddress = $cryptoTrx['merchantAddress'];
                }
                $this->blockIo->createWithdrawalOrSendCryptoApiLog($arr);

                // Update Sender/Receiver Network Address Balance
                if ($type === 'receive') {
                    $this->blockIo->getUpdatedSendWalletBalance($arr);
                }

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
                $data['confirmations']      = $cryptConfirmationsArr[$arr['walletCurrencyCode']];
                $data['walletCurrencyCode'] = $arr['walletCurrencyCode'];
                $data['receiverAddress']    = $arr['receiverAddress'];
                $data['currencySymbol']     = $arr['currencySymbol'];
                $data['currencyId']         = $arr['currencyId'];
                $data['amount']             = $arr['amount'];
                $data['transactionId']      = $arr['transactionId'];

                if ($type === 'send') {
                    $data['userId'] = $arr['endUserId'];
                } elseif ($type === 'receive') {
                    $data['userId'] = $arr['userId'];
                }
                $data['user_full_name'] = $cryptoTrx['user_full_name'];

                //clear cryptoTrx from session
                session()->forget(['cryptoTrx']);
                clearActionSession();
                return [
                    'data'   => $data,
                    'status' => 200,
                ];
            } catch (Exception $e) {
                DB::rollBack();
                session()->forget(['cryptoTrx']);
                clearActionSession();
                return [
                    'message' => $e->getMessage(),
                    'network' => $network,
                    'status'  => 401,
                ];
            }
        }
    }

    //Get merchant network address, merchant network balance and user network address
    public function getMerchantUserNetworkAddressWithMerchantBalance(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $network = $request->network;

            //Get merchant network address
            $merchantAddress = $this->blockIo->getMerchantNetworkAddress($network);
            //Check merchant network address
            $checkMerchantNetworkAddress = $this->blockIo->checkNetworkAddressValidity($network, $merchantAddress);
            if (!$checkMerchantNetworkAddress) {
                return response()->json([
                    'status'  => 401,
                    'message' => 'Invalid merchant ' . $network . ' address',
                ]);
            }

            //Get merchant network address balance
            $merchantAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($network, $merchantAddress);
            //Get Use Wallet Address
            $getUserNetworkWalletAddress = $this->blockIo->getUserNetworkWalletAddress($user_id, $network);

            if ($getUserNetworkWalletAddress->getData()->status == 200) {
                //Check user network wallet address
                $checkUserAddress = $this->blockIo->checkNetworkAddressValidity($network, $getUserNetworkWalletAddress->getData()->userAddress);
                if (!$checkUserAddress) {
                    return response()->json([
                        'status'  => 401,
                        'message' => __('Invalid user :x address', ['x' => $network]),
                    ]);
                }
            } else {
                return response()->json([
                    'status'  => 401,
                    'message' => $getUserNetworkWalletAddress->getData()->message,
                ]);
            }
            return response()->json([
                'status'                 => 200,
                'merchantAddress'        => $merchantAddress,
                'merchantAddressBalance' => $merchantAddressBalance,
                'userAddress'            => $getUserNetworkWalletAddress->getData()->userAddress,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 401,
                'message' => $e->getMessage(),
            ]);
        }
    }

    //validate merchant Address Balance Against Amount
    public function validateMerchantAddressBalanceAgainstAmount(Request $request)
    {
        try {
            $validateMerchantAddressBalance = $this->blockIo->validateNetworkAddressBalance($request->network, $request->amount, $request->merchantAddress, $request->userAddress, $request->priority);
            if (!$validateMerchantAddressBalance['status']) {
                return response()->json([
                    'status' => 401,
                    'message' => __('Network fee :x and amount :y exceeds your :z balance', ['x' => $validateMerchantAddressBalance['network-fee'], 'y' => $request->amount, 'z' => strtoupper($request->network)]),
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'  => 401,
                'message' => $e->getMessage(),
            ]);
        }
    }

    //validate merchant Address Balance Against Amount
    public function validateUserAddressBalanceAgainstAmount(Request $request)
    {
        try {
            $validateUserAddressBalance = $this->blockIo->validateNetworkAddressBalance($request->network, $request->amount, $request->userAddress, $request->merchantAddress, $request->priority);
            if (!$validateUserAddressBalance['status']) {
                return response()->json([
                    'status'      => 401,
                    'message' => __('Network fee :x and amount :y exceeds your :z balance', ['x' => $validateUserAddressBalance['network-fee'], 'y' => $request->amount, 'z' => strtoupper($request->network)]),
                ]);
            } else {
                return response()->json([
                    'status'      => 200,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'  => 401,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function merchantCryptoSentReceivedTransactionPrintPdf($id)
    {
        $id = decrypt($id);
        $data['transaction'] = $transaction = \App\Models\Transaction::with(['currency:id,symbol', 'cryptoAssetApiLog:id,object_id,payload,confirmations'])->where(['id' => $id])->first();

        // Get crypto asset api log details for Crypto_Sent & Crypto_Received (via custom relationship)
        if (!empty($transaction->cryptoAssetApiLog)) {
            $getCryptoDetails = $this->blockIo->getCryptoPayloadConfirmationsDetails($transaction->transaction_type_id, $transaction->cryptoAssetApiLog?->payload, $transaction->cryptoAssetApiLog?->confirmations);
            if (count($getCryptoDetails) > 0) {
                // For "Tracking block io account receiver address changes, if amount is sent from other payment gateways like CoinBase, CoinPayments, etc"
                if (isset($getCryptoDetails['senderAddress'])) {
                    $data['senderAddress'] = $getCryptoDetails['senderAddress'];
                }
                $data['receiverAddress'] = $getCryptoDetails['receiverAddress'];
                $data['confirmations'] = $getCryptoDetails['confirmations'];
                $data['network_fee'] = isset($getCryptoDetails['network_fee']) ? $getCryptoDetails['network_fee'] : 0.00000000;
            }
        }

        generatePDF('blockio::user.transactions.crypto_transaction_pdf', 'crypto-transaction_', $data);
    }

        
    /* 
    * 
    * Extended Function :: Crypto Receive 
    *
    */
    
    // Crypto Receive :: Get user network address, user network balance and merchant network address
    public function getUserNetworkAddressBalanceWithMerchantNetworkAddress(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $network = $request->network;

            //Get Use Wallet Address
            $getUserNetworkWalletAddress = $this->blockIo->getUserNetworkWalletAddress($user_id, $network);
            if ($getUserNetworkWalletAddress->getData()->status == 200) {
                //Check user network wallet address
                $checkUserAddress = $this->blockIo->checkNetworkAddressValidity($network, $getUserNetworkWalletAddress->getData()->userAddress);
                if (!$checkUserAddress) {
                    return response()->json([
                        'status'  => 401,
                        'message' => __('Invalid :x user address', ['x' => $network]),
                    ]);
                }
                //Get user network address balance
                $userAddressBalance = $this->blockIo->getUserBlockIoAddressBalance($network, $getUserNetworkWalletAddress->getData()->userAddress);

                //Get merchant network address
                $merchantAddress = $this->blockIo->getMerchantNetworkAddress($network);

                return response()->json([
                    'status' => 200,
                    'userAddress' => $getUserNetworkWalletAddress->getData()->userAddress,
                    'userAddressBalance' => $userAddressBalance,
                    'merchantAddress' => $merchantAddress,
                ]);
            } else {
                return response()->json([
                    'status'  => 401,
                    'message' => $getUserNetworkWalletAddress->getData()->message,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'  => 401,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
