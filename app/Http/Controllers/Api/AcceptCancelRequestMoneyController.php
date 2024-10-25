<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Currency,
    RequestPayment,
    EmailTemplate,
    Transaction,
    Preference,
    FeesLimit,
    Setting,
    Wallet
};
use Exception;

class AcceptCancelRequestMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $email;
    protected $helper;
    protected $requestPayment;

    public function __construct()
    {
        $this->email          = new EmailController();
        $this->requestPayment = new RequestPayment();
        $this->helper         = new Common();
    }

    //Accept/Cancel request payment starts here
    public function getAcceptRequestEmailOrPhone()
    {
        $requestPayment = RequestPayment::with(['currency:id,symbol,code'])->where('id', request('tr_ref_id'))->first(['email', 'phone', 'amount', 'user_id', 'currency_id']);
        try
        {
            if (!empty($requestPayment->email))
            {
                $success['email'] = $requestPayment->email;
            }
            elseif (!empty($requestPayment->phone))
            {
                $success['phone'] = $requestPayment->phone;
            }
            else
            {
                $success['email'] = null;
                $success['phone'] = null;
            }
            $success['amount']         = $requestPayment->amount;
            $success['currency']       = $requestPayment->currency->code;
            $success['currencySymbol'] = $requestPayment->currency->symbol;
            $success['currency_id']    = $requestPayment->currency->id;
            $success['status']         = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (Exception $e)
        {
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    public function getAcceptRequestAmountLimit()
    {
        $amount      = request('amount');
        $currency_id = request('currency_id');
        $user_id     = request('user_id');

        $RequestAcceptorWallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['id']);
        if ($RequestAcceptorWallet == '')
        {
            $success['reason']  = 'invalidCurrency';
            $success['message'] = "You don't have the requested currency!";
            $success['status']  = '401';
            return response()->json(['success' => $success], $this->successStatus);
        }

        //Wallet Balance Limit Check Starts here
        $wallet              = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['balance']);
        $feesDetails         = FeesLimit::where(['transaction_type_id' => Request_Received, 'currency_id' => $currency_id])->first(['charge_fixed', 'charge_percentage', 'min_limit', 'max_limit']);
        $feesPercentage      = $amount * ($feesDetails->charge_percentage / 100);
        $checkAmountWithFees = $amount + $feesDetails->charge_fixed + $feesPercentage;
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
        if (@$feesDetails)
        {
            if (@$feesDetails->max_limit == null)
            {
                if ((@$amount < @$feesDetails->min_limit))
                {
                    $success['reason']   = 'minLimit';
                    $success['minLimit'] = @$feesDetails->min_limit;
                    $success['message']  = 'Minimum amount ' . formatNumber(@$feesDetails->min_limit);
                    $success['status']   = '401';
                }
                else
                {
                    $success['status'] = $this->successStatus;
                }
            }
            else
            {
                if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
                {
                    $success['reason']   = 'minMaxLimit';
                    $success['minLimit'] = @$feesDetails->min_limit;
                    $success['maxLimit'] = @$feesDetails->max_limit;
                    $success['message']  = 'Minimum amount ' . formatNumber(@$feesDetails->min_limit) . ' and Maximum amount ' . formatNumber(@$feesDetails->max_limit);
                    $success['status']   = '401';
                }
                else
                {
                    $success['status'] = $this->successStatus;
                }
            }
        }
        else
        {
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }

        return response()->json(['success' => $success], $this->successStatus);
        //Amount Limit Check Ends here
    }

    public function getAcceptFeesDetails()
    {
        $user_id     = request('user_id');
        $amount      = request('amount');
        $currency_id = request('currency_id');
        $feesDetails = FeesLimit::where(['transaction_type_id' => Request_Received, 'currency_id' => $currency_id])->first(['charge_percentage', 'charge_fixed']);
        try
        {
            $feesPercentage         = $amount * ($feesDetails->charge_percentage / 100);
            $totalFess              = $feesPercentage + (@$feesDetails->charge_fixed);
            $success['totalAmount'] = $amount + $totalFess;
            $success['totalFees']   = $totalFess;
            $currency               = Currency::where(['id' => $currency_id])->first(['symbol', 'code']);
            $success['currSymbol']  = $currency->symbol;
            $success['currCode']    = $currency->code;
            $success['status']      = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (Exception $e)
        {
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    //only acceptor can accept request payment
    public function requestAcceptedConfirm()
    {
        $tr_ref_id           = request('tr_ref_id');
        $amount              = request('amount');
        $currency_id         = request('currency_id');
        $user_id             = request('user_id');
        $totalFee            = request('totalFees');
        $tr_email_or_phone   = request('tr_email_or_phone');
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($tr_email_or_phone);
        $phoneRegex          = $this->helper->validatePhoneInput($tr_email_or_phone);
        $feesLimit           = $this->helper->getFeesLimitObject([], Request_Received, $currency_id, null, null, ['charge_percentage', 'charge_fixed']);

        $arr = [
            'unauthorisedStatus'  => null,
            'emailFilterValidate' => $emailFilterValidate,
            'phoneRegex'          => $phoneRegex,
            'processedBy'         => $processedBy,
            'requestPaymentId'    => $tr_ref_id,
            'currency_id'         => $currency_id,
            'user_id'             => $user_id,
            'accept_amount'       => $amount,
            'charge_percentage'   => $feesLimit->charge_percentage,
            'percentage_fee'      => $amount * (@$feesLimit->charge_percentage / 100),
            'fixed_fee'           => $feesLimit->charge_fixed,
            'fee'                 => $totalFee,
            'total'               => $amount + ($amount * (@$feesLimit->charge_percentage / 100)) + $feesLimit->charge_fixed,
        ];
        //Get response
        $response = $this->requestPayment->processRequestAcceptConfirmation($arr, 'mobile');
        if ($response['status'] != 200)
        {
            if (empty($response['reqPayment']))
            {
                return response()->json([
                    'status'                             => false,
                    'requestAccptValidationErrorMessage' => $response['ex']['message'],
                ]);
            }
            return response()->json([
                'status'                       => true,
                'requestAccptMailErrorMessage' => $response['ex']['message'],
            ]);
        }
        return response()->json([
            'status' => true,
        ]);
    }

    //both acceptor and creator can cancel request payment - one function for both - logic depends on user_id
    public function requestCancel()
    {
        $tr_email_or_phone = request('tr_email_or_phone');
        $user_id           = request('user_id');

        try
        {
            DB::beginTransaction();

            $TransactionA         = Transaction::find(request('tr_id'), ['id', 'status', 'transaction_type_id', 'transaction_reference_id']);
            $TransactionA->status = "Blocked";
            $TransactionA->save();

            $transaction_type_id  = $TransactionA->transaction_type_id == Request_Received ? Request_Sent : Request_Received;
            $TransactionB         = Transaction::where(['transaction_reference_id' => $TransactionA->transaction_reference_id, 'transaction_type_id' => $transaction_type_id])->first(['id', 'status']);
            $TransactionB->status = "Blocked";
            $TransactionB->save();

            $requestPayment         = RequestPayment::find($TransactionA->transaction_reference_id);
            $requestPayment->status = "Blocked";
            $requestPayment->save();

            $this->sendRequestCancelNotificationToAcceptorOrCreatorForApi($requestPayment, $tr_email_or_phone, $user_id);
            DB::commit();
            return response()->json([
                'status' => $this->successStatus,
            ]);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status'  => $this->unauthorisedStatus,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function sendRequestCancelNotificationToAcceptorOrCreatorForApi($requestPayment, $tr_email_or_phone, $user_id)
    {
        $processedBy = preference('processed_by');
        $emailFilterValidate = filter_var($tr_email_or_phone, FILTER_VALIDATE_EMAIL);
        $phoneRegex = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $tr_email_or_phone);

        $soft_name = settings('name');

        $messageFromCreatorToAcceptor = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid, 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->user)]);

        if ($emailFilterValidate && $processedBy == "email")
        {
            if (checkAppMailEnvironment())
            {
                if ($user_id == $requestPayment->user_id)
                {
                    if (!empty($requestPayment->receiver_id))
                    {
                        //ok
                        $data = $this->onlyEmailToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                            $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->email);
                        return $data;
                    }
                    else
                    {
                        //ok
                        $data = $this->onlyEmailToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $requestPayment->email);
                        return $data;
                    }
                }
                elseif (!empty($requestPayment->receiver_id) && $user_id == $requestPayment->receiver_id)
                {
                    //ok
                    $messageFromAcceptorToCreator = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid, 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->receiver)]);
                    
                    $data = $this->onlyEmailToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name, $requestPayment->user->email);
                    return $data;
                }
            }
        }
        elseif ($phoneRegex && $processedBy == "phone")
        {
            if ($user_id == $requestPayment->user_id)
            {
                if (!empty($requestPayment->receiver_id))
                {
                    $data = $this->onlySmsToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                        $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->carrierCode, $requestPayment->receiver->phone);
                    return $data;
                }
                else
                {
                    $data = $this->onlySmsToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $requestPayment->phone);
                    return $data;
                }
            }
            elseif (!empty($requestPayment->receiver_id) && $user_id == $requestPayment->receiver_id)
            {
                $messageFromAcceptorToCreator = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid , 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->receiver)]);

                $data = $this->onlySmsToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name,
                    $requestPayment->user->carrierCode, $requestPayment->user->phone);
                return $data;
            }
        }
        elseif ($processedBy == "email_or_phone")
        {
            if ($emailFilterValidate)
            {
                if (checkAppMailEnvironment())
                {
                    if ($user_id == $requestPayment->user_id)
                    {
                        if (!empty($requestPayment->receiver_id))
                        {
                            $data = $this->onlyEmailToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                                $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->email);
                            return $data;
                        }
                        else
                        {
                            $data = $this->onlyEmailToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $requestPayment->email);
                            return $data;
                        }
                    }
                    elseif (!empty($requestPayment->receiver_id) && $user_id == $requestPayment->receiver_id)
                    {
                        $messageFromAcceptorToCreator = 'Your request payment #' . $requestPayment->uuid . ' of ' . moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)) .
                        ' has been cancelled by ' . $requestPayment->receiver->first_name . ' ' . $requestPayment->receiver->last_name . '.';
                        $data = $this->onlyEmailToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name, $requestPayment->user->email);
                        return $data;
                    }
                }
            }
            elseif ($phoneRegex)
            {
                if ($user_id == $requestPayment->user_id)
                {
                    if (!empty($requestPayment->receiver_id))
                    {
                        $data = $this->onlySmsToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                            $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->carrierCode, $requestPayment->receiver->phone);
                        return $data;
                    }
                    else
                    {
                        $data = $this->onlySmsToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $requestPayment->phone);
                        return $data;
                    }
                }
                elseif (!empty($requestPayment->receiver_id) && $user_id == $requestPayment->receiver_id)
                {
                    $messageFromAcceptorToCreator = 'Your request payment #' . $requestPayment->uuid . ' of ' . moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)) .
                    ' has been cancelled by ' . $requestPayment->receiver->first_name . ' ' . $requestPayment->receiver->last_name . '.';
                    $data = $this->onlySmsToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name,
                        $requestPayment->user->carrierCode, $requestPayment->user->phone);
                    return $data;
                }
            }
        }
    }

    // Email to registered receiver
    public function onlyEmailToRegisteredRequestReceiverForApi($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = __('Cancellation of Request Payment');
        $message = __('Hi :x :y', ['x' => $requestPaymentFirstName, 'y' => $requestPaymentLastName]) . '<br><br>'; 
        $message .= $messageFromAcceptorToCreator;
        $message .= '<br><br>';
        $message .= __('If you have any questions, please feel free to reply to this mail');
        $message .= '<br><br>';
        $message .= __('Regards') . ',';
        $message .= '<br>';
        $message .= $softName;
        try {
            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (Exception $e)
        {
            DB::rollBack();
        }
    }

    // Email to unregistered receiver
    public function onlyEmailToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = __('Cancellation of Request Payment');
        $message = __('Hi :x', ['x' => $requestPaymentEmail]) . '<br><br>';
        $message .= $messageFromCreatorToAcceptor;
        $message .= '<br><br>';
        $message .= __('If you have any questions, please feel free to reply to this mail');
        $message .= '<br><br>';
        $message .= __('Regards') . ',';
        $message .= '<br>';
        $message .= $softName;
        try {
            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (Exception $e)
        {
            DB::rollBack();
        }
    }

    // Email to registered creator
    public function onlyEmailToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = __('Cancellation of Request Payment');
        $message = __('Hi :x :y', ['x' => $requestPaymentFirstName, 'y' => $requestPaymentLastName]) . '<br><br>'; 
        $message .= $messageFromAcceptorToCreator;
        $message .= '<br><br>';
        $message .= __('If you have any questions, please feel free to reply to this mail');
        $message .= '<br><br>';
        $message .= __('Regards') . ',';
        $message .= '<br>';
        $message .= $softName;
        try {
            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (Exception $e)
        {
            DB::rollBack();
        }
    }

    // Sms to registered receiver
    public function onlySmsToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
        $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserCarrierCode) && !empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                try {
                    // Mail to request creator when a request is cancelled (both sides)
                    $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>';
                    $message .= $messageFromCreatorToAcceptor;
                    sendSMS($RequestPaymentUserCarrierCode . $RequestPaymentUserPhone, $message);
                    $data['status'] = 'Cancelled';
                    return $data['status'];
                }
                catch (Exception $e)
                {
                    DB::rollBack();
                }
            }
        }
    }

    // Sms to unregistered receiver
    public function onlySmsToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $softName, $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                try {
                    // Mail to request creator when a request is cancelled (both sides)
                    $message = 'Hi ' . $RequestPaymentUserPhone . ',<br><br>';
                    $message .= $messageFromCreatorToAcceptor;
                    sendSMS($RequestPaymentUserPhone, $message);
                    $data['status'] = 'Cancelled';
                    return $data['status'];
                }
                catch (Exception $e)
                {
                    DB::rollBack();
                }
            }
        }
    }

    // Sms to registered creator
    public function onlySmsToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
        $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserCarrierCode) && !empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                try {
                    // Mail to request creator when a request is cancelled (both sides)
                    $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>';
                    $message .= $messageFromAcceptorToCreator;
                    sendSMS($RequestPaymentUserCarrierCode . $RequestPaymentUserPhone, $message);
                    $data['status'] = 'Cancelled';
                    return $data['status'];
                }
                catch (Exception $e)
                {
                    DB::rollBack();
                }
            }
        }
    }
    //Accept/Cancel request payment ends here
}
