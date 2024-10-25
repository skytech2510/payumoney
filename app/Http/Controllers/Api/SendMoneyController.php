<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Currency,
    FeesLimit,
    Transfer,
    Wallet,
    User
};

class SendMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $notFound           = 404;
    protected $helper;
    protected $transfer;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->transfer = new Transfer();
    }

    //Send Money Starts here
    public function postSendMoneyEmailCheckApi()
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
                $success['message'] = 'You cannot send money to yourself!';
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

    public function postSendMoneyPhoneCheckApi()
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
                    $success['message'] = 'You cannot send money to yourself!';
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

    public function getSendMoneyCurrenciesApi()
    {
        $user_id = request('user_id');

        /*Check Whether Currency is Activated in feesLimit*/
        $walletList                      = Wallet::with('currency:id,code,type')->where(['user_id' => $user_id])->whereHas('active_currency')->get(['currency_id', 'is_default']);
        $checkWhetherCurrencyIsActivated = FeesLimit::where(['transaction_type_id' => Transferred, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $success['currencies']           = $this->walletList($walletList, $checkWhetherCurrencyIsActivated);
        $success['status']               = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Helper Functions Starts here
    public function walletList($activeWallet, $feesLimitWallet)
    {
        $selectedWallet = [];
        foreach ($activeWallet as $aWallet) {
            foreach ($feesLimitWallet as $flWallet) {
                if ($aWallet->currency_id == $flWallet->currency_id && $flWallet->has_transaction == 'Yes') {
                    $selectedWallet[$aWallet->currency_id]['id']         = $aWallet->currency_id;
                    $selectedWallet[$aWallet->currency_id]['code']       = optional($aWallet->currency)->code;
                    $selectedWallet[$aWallet->currency_id]['is_default'] = $aWallet->is_default;
                    $selectedWallet[$aWallet->currency_id]['type'] = optional($aWallet->currency)->type;
                }
            }
        }
        return $selectedWallet;
    }
    //Helper Functions Ends here

    public function postSendMoneyFeesAmountLimitCheckApi()
    {
        $currency_id = request('sendCurrency');
        $user_id     = request('user_id');
        $amount      = request('sendAmount');
        $feesDetails = $this->helper->getFeesLimitObject(['currency:id,code,symbol,type'],Transferred, $currency_id, null, null, ['charge_percentage', 'charge_fixed', 'currency_id', 'min_limit', 'max_limit']);;

        //Wallet Balance Limit Check Starts here
        $feesPercentage      = $amount * ($feesDetails->charge_percentage / 100);
        $checkAmountWithFees = $amount + $feesDetails->charge_fixed + $feesPercentage;
        $wallet              = $this->helper->getUserWallet([],['user_id' => $user_id, 'currency_id' => $currency_id], ['balance']);
        if (@$wallet)
        {
            if ((@$checkAmountWithFees) > (@$wallet->balance) || (@$wallet->balance < 0))
            {
                $success['reason']  = 'insufficientBalance';
                $success['message'] = "Sorry, not enough funds to perform the operation.";
                $success['status']  = '401';
                return response()->json(['success' => $success], $this->successStatus);
            }
        }
        //Wallet Balance Limit Check Ends here

        //Amount Limit Check Starts here
        if (isset($feesDetails)) {
            if (empty($feesDetails->max_limit)) {
                if (isset($amount) && $amount < $feesDetails->min_limit) {
                    $success['reason']   = 'minLimit';
                    $success['minLimit'] = $feesDetails->min_limit;
                    $success['message']  = 'Minimum amount ' . formatNumber($feesDetails->min_limit, optional($feesDetails->currency)->id);
                    $success['status']   = '401';
                } else {
                    $feesPercentage                = $amount * ($feesDetails->charge_percentage / 100);
                    $feesFixed                     = $feesDetails->charge_fixed;
                    $totalFess                     = $feesPercentage + $feesFixed;
                    $totalAmount                   = $amount + $totalFess;
                    $success['sendAmount']         = $amount;
                    $success['sendCurrency']       = $currency_id;
                    $success['totalFees']          = $totalFess;
                    $success['sendAmountDisplay']  = formatNumber($amount, optional($feesDetails->currency)->id);
                    $success['totalFeesDisplay']   = formatNumber($totalFess, optional($feesDetails->currency)->id);
                    $success['totalAmountDisplay'] = formatNumber($totalAmount, optional($feesDetails->currency)->id);
                    $success['currCode']           = optional($feesDetails->currency)->code;
                    $success['currSymbol']         = optional($feesDetails->currency)->symbol;
                    $success['currType']         = optional($feesDetails->currency)->type;
                    $success['status']             = $this->successStatus;
                }
            } else {
                if (isset($amount) && ($amount < $feesDetails->min_limit || $amount > $feesDetails->max_limit)) {
                    $success['reason']   = 'minMaxLimit';
                    $success['minLimit'] = $feesDetails->min_limit;
                    $success['maxLimit'] = $feesDetails->max_limit;
                    $success['message']  = 'Minimum amount ' . formatNumber($feesDetails->min_limit, optional($feesDetails->currency)->id) . ' and Maximum amount ' . formatNumber($feesDetails->max_limit, optional($feesDetails->currency)->id);
                    $success['status']   = '401';
                } else {
                    $feesPercentage                = $amount * ($feesDetails->charge_percentage / 100);
                    $feesFixed                     = $feesDetails->charge_fixed;
                    $totalFess                     = $feesPercentage + $feesFixed;
                    $totalAmount                   = $amount + $totalFess;
                    $success['sendAmount']         = $amount;
                    $success['sendCurrency']       = $currency_id;
                    $success['totalFees']          = $totalFess;
                    $success['sendAmountDisplay']  = formatNumber($amount, optional($feesDetails->currency)->id);
                    $success['totalFeesDisplay']   = formatNumber($totalFess, optional($feesDetails->currency)->id);
                    $success['totalAmountDisplay'] = formatNumber($totalAmount, optional($feesDetails->currency)->id);
                    $success['currCode']           = optional($feesDetails->currency)->code;
                    $success['currSymbol']         = optional($feesDetails->currency)->symbol;
                    $success['currType']         = optional($feesDetails->currency)->type;
                    $success['status']             = $this->successStatus;
                }
            }
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $feesPercentage                = 0;
            $feesFixed                     = 0;
            $totalFess                     = $feesPercentage + $feesFixed;
            $totalAmount                   = $amount + $totalFess;
            $success['sendAmount']         = $amount;
            $success['sendCurrency']       = $currency_id;
            $success['totalFees']          = $totalFess;
            $success['sendAmountDisplay']  = formatNumber($amount, optional($feesDetails->currency)->id);
            $success['totalFeesDisplay']   = formatNumber($totalFess, optional($feesDetails->currency)->id);
            $success['totalAmountDisplay'] = formatNumber($totalAmount, optional($feesDetails->currency)->id);
            $success['currCode']           = optional($feesDetails->currency)->code;
            $success['currSymbol']         = optional($feesDetails->currency)->symbol;
            $success['currType']         = optional($feesDetails->currency)->type;
            $success['status']             = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        //Amount Limit Check Ends here
    }

    public function postSendMoneyPayApi()
    {
        $user_id             = request('user_id');
        $emailOrPhone        = request('emailOrPhone');
        $currency_id         = request('currency_id');
        $amount              = request('amount');
        $totalFees           = request('totalFees');
        $total_with_fee      = $amount + $totalFees;
        $note                = request('note');
        $unique_code         = unique_code();
        $emailFilterValidate = $this->helper->validateEmailInput($emailOrPhone);
        $phoneRegex          = $this->helper->validatePhoneInput($emailOrPhone);
        $processedBy         = preference('processed_by');
        $feesDetails         = $this->helper->getFeesLimitObject([], Transferred, $currency_id, null, null, ['charge_percentage', 'charge_fixed']);
        $user                = User::where(['id' => $user_id])->first(['email']);
        $senderWallet        = $this->helper->getUserWallet([], ['user_id' => $user_id, 'currency_id' => $currency_id], ['id', 'balance']);
        $userInfo            = $this->helper->getEmailPhoneValidatedUserInfo($emailFilterValidate, $phoneRegex, trim($emailOrPhone));

        $arr = [
            'emailFilterValidate' => $emailFilterValidate,
            'phoneRegex'          => $phoneRegex,
            'processedBy'         => $processedBy,
            'user_id'             => $user_id,
            'userInfo'            => $userInfo,
            'currency_id'         => $currency_id,
            'uuid'                => $unique_code,
            'fee'                 => $totalFees,
            'amount'              => $amount,
            'note'                => trim($note),
            'receiver'            => trim($emailOrPhone),
            'charge_percentage'   => $feesDetails->charge_percentage,
            'charge_fixed'        => $feesDetails->charge_fixed,
            'p_calc'              => $amount * ($feesDetails->charge_percentage / 100),
            'total'               => $total_with_fee,
            'senderWallet'        => $senderWallet,
        ];
        //Get response
        $response = $this->transfer->processSendMoneyConfirmation($arr, 'mobile');
        if ($response['status'] != 200)
        {
            if (empty($response['transactionOrTransferId']))
            {
                return response()->json([
                    'status'                          => false,
                    'sendMoneyValidationErrorMessage' => $response['ex']['message'],
                ]);
            }
            return response()->json([
                'status'                    => true,
                'sendMoneyMailErrorMessage' => $response['ex']['message'],
                'tr_ref_id'                 => $response['transactionOrTransferId'],
            ]);
        }
        return response()->json([
            'status'    => true,
            'tr_ref_id' => $response['transactionOrTransferId'],
        ]);
    }
    //Send Money Ends here
}
