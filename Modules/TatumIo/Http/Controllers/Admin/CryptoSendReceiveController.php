<?php

namespace Modules\TatumIo\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\TatumIo\Class\TatumIoTransaction;

class CryptoSendReceiveController extends Controller
{
    protected $helper;
    protected $currency;

    public function __construct()
    {
        $this->currency = new \App\Models\Currency();
        $this->helper = new \App\Http\Helpers\Common();
    }

    /**
     * Crypto Sent via admin start from here
     *
     */

    /* Crypto Sent :: Create */
    public function cryptoSentInitiate($network)
    {
        $data['network'] = $network = decrypt($network);
        $data['currency'] = \App\Models\Currency::where(['code' => $network, 'type' => 'crypto_asset'])->first(['id', 'type', 'status']);
        $currencyId = $data['currency']->id;

        if ($data['currency']->status != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the :x first for making any transaction', ['x' => $network]));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');
        }

        if (CryptoProvider::getStatus('TatumIo') != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the provider first for making any transaction'));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');
        }

        setActionSession();
        // Get those users who has selected network wallets
        $data['users'] = \App\Models\User::whereHas('wallets.cryptoAssetApiLogs', function ($q) use ($currencyId, $network) {
            $q->where('wallets.currency_id', $currencyId);
            $q->where(['crypto_asset_api_logs.payment_method_id' => TatumIo, 'crypto_asset_api_logs.network' => $network]);
        })
            ->whereStatus('Active')
            ->get();

        $data['minTatumIoLimit'] = json_encode(getTatumIoMinLimit());

        return view('tatumio::admin.crypto.send.create', $data);
    }

    //Get merchant network address, merchant network balance and user network address
    public function getMerchantUserNetworkAddressWithMerchantBalance(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $network = $request->network;

            $tatumIo = new TatumIoTransaction($network);
            $tatumIo->tatumIoAsset();
            $tatumIo->checkUserTatumWallet($user_id);

            return response()->json([
                'status' => 200,
                'merchantAddress' => $tatumIo->getMerchantAddress(),
                'merchantAddressBalance' => $tatumIo->getMerchantBalance(),
                'userAddress' => $tatumIo->getUserAddress(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage(),
            ]);
        }
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
        return view('tatumio::admin.crypto.send.confirmation', $data);

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
        $priority = $request->priority;
        $currency = $this->currency->getCurrency(['code' => $network, 'type' => 'crypto_asset'], ['id', 'symbol']);

        //merge currency symbol with request array
        $request->merge(['currency_symbol' => $currency->symbol]);
        $request->merge(['currency_id' => $currency->id]);
        $request->merge(['user_full_name' => getColumnValue($data['users'])]);

        $tatumIo = new TatumIoTransaction($network);
        $tatumIo->tatumIoAsset();
        $tatumIo->checkUserTatumWallet($userId);

        if ($type == 'send') {
            $availableBalance = $tatumIo->getMerchantBalance();
            $getNetworkFeeEstimate = $tatumIo->getEstimatedFees($merchantAddress, $userAddress, $amount, $priority);
        } else {

            $availableBalance = $tatumIo->getUserBalance();
            $getNetworkFeeEstimate = $tatumIo->getEstimatedFees($userAddress, $merchantAddress, $amount, $priority);
        }

        if ($amount + $getNetworkFeeEstimate > $availableBalance) {
            return [
                'status' => 401,
                'message' => __('Insufficient Balance'),
            ];
        }
        //unset users - not needed in confirm page
        unset($data['users']);
        //Call network fee API of tatum io

        //merge network fee with request array
        $request->merge(['network_fee' => $getNetworkFeeEstimate]);

        //Put data in session for success page
        session(['cryptoTrx' => $request->all()]);

        //for confirm page only
        $data['cryptoTrx'] = $request->only('currency_symbol', 'currency_id', 'network', 'amount', 'network_fee', 'user_id', 'user_full_name');

        return [
            'cryptoTrx' => $data['cryptoTrx'],
            'status' => 200,
        ];

    }

    /* Crypto Receive :: Create */
    public function cryptoReceiveInitiate($network)
    {
        $data['menu'] = 'crypto_providers';
        $data['network'] = $network = decrypt($network);
        $data['currency'] = \App\Models\Currency::where(['code' => $network, 'type' => 'crypto_asset'])->first(['id', 'type', 'status']);
        $currencyId = $data['currency']->id;

        if ($data['currency']->status != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the :x first for making any transaction', ['x' => $network]));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');
        }

        if (CryptoProvider::getStatus('TatumIo') != 'Active') {
            $this->helper->one_time_message('error', __('Please activate the provider first for making any transaction'));
            return redirect()->route('admin.crypto_providers.list', 'TatumIo');
        }

        setActionSession();
        $data['users'] = \App\Models\User::whereHas('wallets.cryptoAssetApiLogs', function ($q) use ($currencyId, $network) {
            $q->where('wallets.currency_id', $currencyId);
            $q->where(['crypto_asset_api_logs.payment_method_id' => TatumIo, 'crypto_asset_api_logs.network' => $network]);
        })
            ->get();

        $data['minTatumIoLimit'] = json_encode(getTatumIoMinLimit());

        return view('tatumio::admin.crypto.receive.create', $data);
    }

    //Get merchant network address, merchant network balance and user network address
    public function getUserNetworkAddressWithUserBalance(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $network = $request->network;

            $tatumIo = new TatumIoTransaction($network);
            $tatumIo->tatumIoAsset();
            $tatumIo->checkUserTatumWallet($user_id);

            return response()->json([
                'status' => 200,
                'userAddress' => $tatumIo->getUserAddress(),
                'userAddressBalance' => $tatumIo->getUserBalance(),
                'merchantAddress' => $tatumIo->getMerchantAddress(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage(),
            ]);
        }
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
            return redirect()->route('admin.tatum.crypto_receive.create', ['code' => encrypt($request->network)]);
        }
        //for confirm page only
        $data['cryptoTrx'] = $response['cryptoTrx'];
        return view('tatumio::admin.crypto.receive.confirmation', $data);
    }

    /* Crypto Sent :: success */
    public function eachUserCryptoSentSuccess(Request $request)
    {
        actionSessionCheck();

        $res = $this->cryptoSendReceiveSuccess($request, 'send');

        if ($res['status'] == 401) {
            $this->helper->one_time_message('error', $res['message']);
            return redirect()->route('admin.tatum.crypto_send.create', [encrypt($request->network)]);
        }
        return view('tatumio::admin.crypto.send.success', $res['data']);
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

        return view('tatumio::admin.crypto.receive.success', $res['data']);
    }

    public function cryptoSendReceiveSuccess($request, $type)
    {
        $network = $request->network;
        $cryptoTrx = session('cryptoTrx');

        if (empty($cryptoTrx)) {
            return [
                'message' => null,
                'network' => $network,
                'status' => 401,
            ];
        }

        // Backend validation of sender crypto wallet balance -- for multiple tab submit
        $request['network'] = $cryptoTrx['network'];
        $request['merchantAddress'] = $cryptoTrx['merchantAddress'];
        $request['userAddress'] = $cryptoTrx['userAddress'];
        $request['amount'] = $cryptoTrx['amount'];
        $request['priority'] = $cryptoTrx['priority'];

        $tatumIo = new TatumIoTransaction($network);
        $tatumIo->tatumIoAsset();
        $tatumIo->checkUserTatumWallet($cryptoTrx['user_id']);

        if ($type == 'send') {
            $availableBalance = $tatumIo->getMerchantBalance();
            $getNetworkFeeEstimate = $tatumIo->getEstimatedFees($cryptoTrx['merchantAddress'], $cryptoTrx['userAddress'], $request['amount'], $cryptoTrx['priority']);
        } else {

            $availableBalance = $tatumIo->getUserBalance();
            $getNetworkFeeEstimate = $tatumIo->getEstimatedFees($cryptoTrx['userAddress'], $cryptoTrx['merchantAddress'], $request['amount'], $cryptoTrx['priority']);
        }

        if ($cryptoTrx['amount'] + $getNetworkFeeEstimate > $availableBalance) {
            return [
                'status' => 401,
                'message' => __('Insufficient Balance'),
            ];
        } else {

            try {
                $uniqueCode = unique_code();
                $arr = [
                    'walletCurrencyCode' => $cryptoTrx['network'],
                    'amount' => $cryptoTrx['amount'],
                    'networkFee' => $cryptoTrx['network_fee'],
                    'userId' => null,
                    'endUserId' => null,
                    'currencyId' => $cryptoTrx['currency_id'],
                    'currencySymbol' => $cryptoTrx['currency_symbol'],
                    'uniqueCode' => $uniqueCode,
                ];

                if ($type === 'send') {
                    $arr['senderAddress'] = $cryptoTrx['merchantAddress'];
                    $arr['receiverAddress'] = $cryptoTrx['userAddress'];
                    $arr['endUserId'] = $cryptoTrx['user_id'];
                    $arr['priority'] = $cryptoTrx['priority'];
                } elseif ($type === 'receive') {
                    $arr['senderAddress'] = $cryptoTrx['userAddress'];
                    $arr['receiverAddress'] = $cryptoTrx['merchantAddress'];
                    $arr['userId'] = $cryptoTrx['user_id'];
                    $arr['priority'] = $cryptoTrx['priority'];
                }

                if ($type == 'send') {
                    $withdrawInfoResponse = $tatumIo->sendCryptoAdminToUser($cryptoTrx['amount'], $cryptoTrx['priority']);
                } else {
                    $withdrawInfoResponse = $tatumIo->sendCryptoUserToAdmin($cryptoTrx['amount'], $cryptoTrx['priority']);
                }

                if (!isset($withdrawInfoResponse->txId)) {
                    return [
                        'message' => isset($withdrawInfoResponse->cause) ?  $withdrawInfoResponse->cause : __('Transaction Failed, please try again'),
                        'network' => $network,
                        'status' => 401,
                    ];
                }

                DB::beginTransaction();

                // Create Merchant Crypto Transaction
                $createCryptoTransactionId = $tatumIo->createCryptoTransaction($arr);

                // Create merchant new withdrawal/Send/Receive crypt api log
                $arr['transactionId'] = $createCryptoTransactionId;
                $arr['withdrawInfoData'] = $withdrawInfoResponse;
                $arr['withdrawInfoData']->network_fee = $getNetworkFeeEstimate;

                if ($type === 'send') {
                    // Need this for showing send address against Crypto Receive Type Transaction in user/admin panel
                    $arr['withdrawInfoData']->senderAddress = $cryptoTrx['merchantAddress'];
                    // Need this for nodejs websocket server
                    $arr['withdrawInfoData']->receiverAddress = $cryptoTrx['userAddress'];
                } elseif ($type === 'receive') {
                    $arr['withdrawInfoData']->senderAddress = $cryptoTrx['userAddress'];
                    $arr['withdrawInfoData']->receiverAddress = $cryptoTrx['merchantAddress'];
                }
                $tatumIo->createWithdrawalOrSendCryptoApiLog($arr);

                // Update Sender/Receiver Network Address Balance
                if ($type === 'receive') {
                    $tatumIo->getUpdatedSendWalletBalance($arr);
                }

                DB::commit();

                // Initially after 1 confirmations of tatumio response, websocket queries will be executed
                $cryptConfirmationsArr = [
                    'BTC' => 1,
                    'BTCTEST' => 1,
                    'DOGE' => 1,
                    'DOGETEST' => 1,
                    'LTC' => 1,
                    'LTCTEST' => 1,
                    'TRXTEST' => 1,
                    'TRX' => 1,
                    'ETH' => 1,
                    'ETHTEST' => 1,
                ];
                $data['confirmations'] = $cryptConfirmationsArr[$arr['walletCurrencyCode']];
                $data['walletCurrencyCode'] = $arr['walletCurrencyCode'];
                $data['receiverAddress'] = $arr['receiverAddress'];
                $data['currencySymbol'] = $arr['currencySymbol'];
                $data['currencyId'] = $arr['currencyId'];
                $data['amount'] = $arr['amount'];
                $data['transactionId'] = $arr['transactionId'];

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
                    'data' => $data,
                    'status' => 200,
                ];
            } catch (Exception $e) {
                DB::rollBack();
                session()->forget(['cryptoTrx']);
                clearActionSession();
                return [
                    'message' => $e->getMessage(),
                    'network' => $network,
                    'status' => 401,
                ];
            }
        }
    }

    //validate merchant Address Balance Against Amount
    public function validateMerchantAddressBalanceAgainstAmount(Request $request)
    {
        $sender = $request->merchantAddress;
        $receiver = $request->userAddress;
        $amount = $request->amount;
        $priority = $request->priority;
        $network = $request->network;


        try {

            $tatumIo = new TatumIoTransaction($network);
            $tatumIo->tatumIoAsset();

            $merchantBalance = $tatumIo->getMerchantBalance();

            $networkFees = $tatumIo->getEstimatedFees($sender, $receiver, $amount, $priority);


            if ($merchantBalance < ($amount + $networkFees)) {
                return response()->json([
                    'status' => 401,
                    'message' => __('Network fee :x and amount :y exceeds your :z balance', ['x' => $networkFees, 'y' => $request->amount, 'z' => strtoupper($request->network)]),
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => $e->getMessage(),
            ]);
        }
    }

    //validate merchant Address Balance Against Amount
    public function validateUserAddressBalanceAgainstAmount(Request $request)
    {

        $sender = $request->userAddress;
        $receiver = $request->merchantAddress;
        $amount = $request->amount;
        $priority = $request->priority;
        $network = $request->network;

        $userId = $request->userId;

        try {

            $tatumIo = new TatumIoTransaction($network);
            $tatumIo->tatumIoAsset();

            $tatumIo->checkUserTatumWallet($userId);
            $userBalance = $tatumIo->getUserBalance();
            $networkFees = $tatumIo->getEstimatedFees($sender, $receiver, $amount, $priority);

            if ($userBalance < ($amount + $networkFees)) {
                return response()->json([
                    'status'      => 401,
                    'message' => __('Network fee :x and amount :y exceeds your :z balance', ['x' => $networkFees, 'y' => $request->amount, 'z' => strtoupper($request->network)]),
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

}
