<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Currency,
    RequestPayment,
    Transaction,
    FeesLimit,
    Wallet,
    User
};
use Exception;

class RequestMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $notFound           = 404;
    public $email;
    protected $helper;
    protected $requestPayment;

    public function __construct()
    {
        $this->helper         = new Common();
        $this->email          = new EmailController();
        $this->requestPayment = new RequestPayment();
    }

    //Request Money starts here
    public function postRequestMoneyEmailCheckApi()
    {
        if (request('user_id'))
        {
            $user_id       = request('user_id');
            $receiverEmail = request('receiverEmail');
            $user          = User::where('id', '=', $user_id)->first(['email']);
            $receiver      = User::where('email', '=', $receiverEmail)->first(['email','status']);

            if (@$user->email == @$receiver->email)
            {
                $success['status']  = $this->unauthorisedStatus;
                $success['reason']  = 'own-email';
                $success['message'] = 'You Cannot Request Money To Yourself.';
                return response()->json(['success' => $success], $this->successStatus);
            }
            else
            {
                if ($receiver)
                {
                    if ($receiver->status == 'Suspended')
                    {
                        $success['status']  = $this->unauthorisedStatus;
                        $success['reason']  = 'suspended';
                        $success['message'] = 'The recipient is suspended!';
                        return response()->json(['success' => $success], $this->successStatus);
                    }
                    elseif ($receiver->status == 'Inactive')
                    {
                        $success['status']  = $this->unauthorisedStatus;
                        $success['reason']  = 'inactive';
                        $success['message'] = 'The recipient is inactive!';
                        return response()->json(['success' => $success], $this->successStatus);
                    }
                    $success['status'] = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
                else
                {
                    $success['status'] = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
            }
        }
        else
        {
            echo "In else block";exit();return false;
        }
    }

    public function postRequestMoneyPhoneCheckApi()
    {
        if (request('user_id'))
        {
            $user     = User::where('id', '=', request('user_id'))->first(['formattedPhone']);
            $receiver = User::where('formattedPhone', '=', request('receiverPhone'))->first(['formattedPhone','status']);
            if (!empty($user->formattedPhone))
            {
                if (@$user->formattedPhone == @$receiver->formattedPhone)
                {
                    $success['status']  = $this->unauthorisedStatus;
                    $success['reason']  = 'own-phone';
                    $success['message'] = 'You Cannot Request Money To Yourself.';
                    return response()->json(['success' => $success], $this->successStatus);
                }
                else
                {
                    if ($receiver)
                    {
                        if ($receiver->status == 'Suspended')
                        {
                            $success['status']  = $this->unauthorisedStatus;
                            $success['reason']  = 'suspended';
                            $success['message'] = 'The recipient is suspended!';
                            return response()->json(['success' => $success], $this->successStatus);
                        }
                        elseif ($receiver->status == 'Inactive')
                        {
                            $success['status']  = $this->unauthorisedStatus;
                            $success['reason']  = 'inactive';
                            $success['message'] = 'The recipient is inactive!';
                            return response()->json(['success' => $success], $this->successStatus);
                        }
                        $success['status'] = $this->successStatus;
                        return response()->json(['success' => $success], $this->successStatus);
                    }
                    else
                    {
                        $success['status'] = $this->successStatus;
                        return response()->json(['success' => $success], $this->successStatus);
                    }
                }
            }
            else
            {
                $success['status']  = $this->notFound;
                $success['message'] = 'Please set your phone number first!';
                return response()->json(['success' => $success], $this->successStatus);
            }
        }
        else
        {
            echo "In else block";exit();return false;
        }
    }

    //Request Payment Currency List
    public function getRequestMoneyCurrenciesApi()
    {
        $currenciesList        = Currency::where(['status' => 'Active'])->get(['id', 'code', 'symbol', 'type']);
        $feesLimitWallet       = FeesLimit::where(['transaction_type_id' => Request_Received, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $success['currencies'] = $this->requestWalletList($currenciesList, $feesLimitWallet);

        //Set default wallet as selected - starts
        $user_id                            = request('user_id');
        $defaultWallet                      = Wallet::where(['user_id' => $user_id, 'is_default' => 'Yes'])->first(['currency_id']);
        $success['defaultWalletCurrencyId'] = $defaultWallet->currency_id;
        //Set default wallet as selected - ends

        $success['status'] = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Helper Functions Starts here
    public function requestWalletList($currenciesList, $feesLimitWallet)
    {
        $selectedWallet = [];
        foreach ($currenciesList as $currency) {
            foreach ($feesLimitWallet as $flWallet) {
                if ($currency->id == $flWallet->currency_id && $flWallet->has_transaction == 'Yes') {
                    $selectedWallet[$currency->id]['id']     = $currency->id;
                    $selectedWallet[$currency->id]['code']   = $currency->code;
                    $selectedWallet[$currency->id]['symbol'] = $currency->symbol;
                    $selectedWallet[$currency->id]['type'] = $currency->type;
                }
            }
        }
        return $selectedWallet;
    }
    //Helper Functions Ends here

    public function postRequestMoneyPayApi()
    {
        $uid                 = request('user_id');
        $emailOrPhone        = request('emailOrPhone');
        $amount              = request('amount');
        $currency_id         = request('currencyId');
        $note                = request('note');
        $uuid                = unique_code();
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput(trim($emailOrPhone));
        $phoneRegex          = $this->helper->validatePhoneInput(trim($emailOrPhone));
        $senderInfo          = User::where(['id' => $uid])->first(['email']);
        $userInfo            = $this->helper->getEmailPhoneValidatedUserInfo($emailFilterValidate, $phoneRegex, trim($emailOrPhone));
        $receiverName        = isset($userInfo) ? $userInfo->first_name . ' ' . $userInfo->last_name : '';
        $arr                 = [
            'unauthorisedStatus'  => $this->unauthorisedStatus,
            'emailFilterValidate' => $emailFilterValidate,
            'phoneRegex'          => $phoneRegex,
            'processedBy'         => $processedBy,
            'user_id'             => $uid,
            'userInfo'            => $userInfo,
            'currency_id'         => $currency_id,
            'uuid'                => $uuid,
            'amount'              => $amount,
            'receiver'            => $emailOrPhone,
            'note'                => $note,
            'receiverName'        => $receiverName,
            'senderEmail'         => $senderInfo->email,
        ];
        //Get response
        $response = $this->requestPayment->processRequestCreateConfirmation($arr, 'mobile');
        if ($response['status'] != 200)
        {
            if (empty($response['transactionOrReqPaymentId']))
            {
                return response()->json([
                    'status' => false,
                ]);
            }
            return response()->json([
                'status'                       => true,
                'requestMoneyMailErrorMessage' => $response['ex']['message'],
                'tr_ref_id'                    => $response['transactionOrReqPaymentId'],
            ]);
        }
        return response()->json([
            'status'    => true,
            'tr_ref_id' => $response['transactionOrReqPaymentId'],
        ]);
    }

    //Check Request Creator Status (for dashboard and transactions list - user panel)
    public function checkReqCreatorStatusApi(Request $request)
    {
        try
        {
            $transaction                        = Transaction::with(['end_user:id,status'])->find($request->trans_id, ['id', 'end_user_id']);
            $success['status']                  = $this->successStatus;
            $success['transaction-user-status'] = $transaction->end_user->status;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (Exception $e)
        {
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }
    //Request Money Ends here
}
