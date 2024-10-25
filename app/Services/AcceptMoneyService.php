<?php

/**
 * @package AcceptMoneyService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services;

use App\Http\Helpers\Common;
use App\Exceptions\Api\V2\AcceptMoneyException;
use App\Services\Mail\{
    AcceptMoneyCreatorMailService,
    RequestMoneyReceiverCancelEmailService,
    RequestMoneyCreatorCancelEmailService
};
use App\Services\Sms\{
    AcceptMoneyCreatorSmsService,
    RequestMoneyCreatorCancelSmsService,
    RequestMoneyReceiverCancelSmsService
};
use App\Models\{
    RequestPayment,
    Transaction,
    User
};
use Exception, DB;
use App\Services\Mail\RequestMoney\NotifyAdminOnRequestMoneyMailService;

class AcceptMoneyService
{
    protected $requestPayment;
    protected $helper;

    public function __construct()
    {
        $this->requestPayment = new RequestPayment();
        $this->helper         = new Common();
    }
    /**
     * Get details of a request payment to accept it
     *
     * @param int $id
     * @return RequestPayment
     * @throws AcceptMoneyException
     */
    public function details($id): RequestPayment
    {
        if (empty($id) || is_null($id)) {
            throw new AcceptMoneyException(__(":x is required.", ["x" => __("Transaction reference number")]));
        }

        $requestPayment = RequestPayment::with('currency:id,symbol,code,type')
            ->where('id', $id)
            ->first(['email', 'phone', 'amount', 'user_id', 'currency_id', 'note']);

        if (!$requestPayment) {
            throw new AcceptMoneyException(__("No transaction was found for this reference number."));
        }

        $requestPayment->formattedAmount = formatNumber(
            $requestPayment->formattedAmount,
            optional($requestPayment->currency)->id
        );

        return $requestPayment;
    }

    /**
     * Check Maximum and minimum amount
     * Check wallet balance
     * @param double $amount
     * @param int $currency_id
     * @param int $user_id
     * @return object
     * @throws AcceptMoneyException
     */
    public function checkAmountLimit($amount, $currencyId, $userId): object
    {
        $currencyFee = $this->helper->transactionFees($currencyId, $amount, Request_Received);

        $this->helper->amountIsInLimit($currencyFee, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $currencyFee->total_amount);

        return $currencyFee;
    }

    /**
     * Email validation for request payment
     *
     * @param int $user_id
     * @param int $trId
     * @throws AcceptMoneyException
     */
    public function checkRequestReceiverEmail($user_id, $trId)
    {
        $receiver = User::where(['id' => $user_id])->first(['email']);
        if (! $receiver) {
            throw new AcceptMoneyException(__('The :x does not exist.', ['x' => __('user')]));
        }

        $requestMoney = RequestPayment::find($trId, ['user_id', 'receiver_id']);

        if ($requestMoney->user_id == $user_id) {
            throw new AcceptMoneyException(__("You cannot request money to yourself."));
        }

        if ($requestMoney->receiver_id != $user_id) {
            throw new AcceptMoneyException(__("Receiver not matched."));
        }

        $user = User::find($requestMoney->user_id, ['email', 'status']);

        if ($user) {
            if ($user->email == $receiver->email) {
                throw new AcceptMoneyException(__('You cannot request money to yourself.'));
            }

            $status = strtolower($user->status);

            if ("active" != $status) {
                throw new AcceptMoneyException(__("The recipient is :x. ", ["x" => __($status)]));
            }
        }
    }

    /**
     * Phone number validation for request payment
     *
     * @param int $user_id
     * @param int $trId
     */
    public function checkRequestReceiverPhone($user_id, $trId)
    {
        $receiver = User::where(['id' => $user_id])->first(['formattedPhone']);
        if (! $receiver) {
            throw new AcceptMoneyException(__('The :x does not exist.', ['x' => __('user')]));
        }

        $requestMoney = RequestPayment::find($trId, ['user_id', 'receiver_id']);

        if ($requestMoney->user_id == $user_id) {
            throw new AcceptMoneyException(__('You cannot request money to yourself.'));
        }

        if (empty($receiver->formattedPhone)) {
            throw new AcceptMoneyException(__("Please set your phone number first."));
        }

        $user = User::find($requestMoney->user_id, ['email', 'status']);
        if ($user->formattedPhone == $receiver->formattedPhone) {
            throw new AcceptMoneyException(__('You cannot request money to yourself.'));
        }

        $status = strtolower($user->status);
        if ("active" != $status) {
            throw new AcceptMoneyException(__("The recipient is :x. ", ["x" => __($status)]));
        }
    }

    /**
     * Store aceept money request
     *
     * @param int $trId
     * @param double $amount
     * @param int $userId
     * @param int $currencyId
     * @param string $emailOrPhone
     * @param string $processedBy
     * @return array
     */
    public function store($trId, $amount, $userId, $currencyId, $emailOrPhone, $processedBy)
    {
        // check Request status
        $this->requestPaymentStatus($trId);
        // Check valid email or phone
        $this->checkEmailPhone($processedBy, $emailOrPhone, $userId, $trId);
        // get fees
        $currencyFee = $this->helper->transactionFees($currencyId, $amount, Request_Received);
        //check limit
        $this->helper->amountIsInLimit($currencyFee, $amount);
         //check wallet balance
        $this->helper->checkWalletAmount($userId, $currencyId, $currencyFee->total_amount);

        $emailFilterValidate = $this->helper->validateEmailInput($emailOrPhone);
        $phoneRegex          = $this->helper->validatePhoneInput($emailOrPhone);
        $arr = [
            'unauthorisedStatus'  => null,
            'emailFilterValidate' => $emailFilterValidate,
            'phoneRegex'          => $phoneRegex,
            'processedBy'         => $processedBy,
            'requestPaymentId'    => $trId,
            'currency_id'         => $currencyId,
            'user_id'             => $userId,
            'accept_amount'       => $amount,
            'charge_percentage'   => $currencyFee->charge_percentage,
            'fixed_fee'           => $currencyFee->charge_fixed,
            'percentage_fee'      => $currencyFee->fees_percentage,
            'fee'                 => $currencyFee->total_fees,
            'total'               => $currencyFee->total_amount,
        ];

        try {
            DB::beginTransaction();
            //Update Request Payment
            $requestPayment = $this->requestPayment->updateRequestPayment($arr);
            //Update Request Creator Transaction
            $this->requestPayment->udpateRequestCreatorTransaction($arr, $requestPayment);
            //Update Request Acceptor Transaction
            $this->requestPayment->udpateRequestAcceptorTransaction($arr, $requestPayment);
            //Update Request Creator Wallet
            $this->requestPayment->updateRequestCreatorWallet($arr, $requestPayment);
            //Update Request Acceptor Wallet
            $this->requestPayment->updateRequestAcceptorWallet($arr);

            DB::commit();

            $this->sendAcceptNotificationToCreator($requestPayment);

            $requestPayment['charge_percentage'] = $arr['percentage_fee'];
            $requestPayment['charge_fixed']     = $arr['fixed_fee'];
            $this->notifyToAdmin($requestPayment);

            return true;

        } catch (Exception $e) {
           DB::rollBack();
           throw new AcceptMoneyException($e->getMessage());
        }

    }

    /**
     * Cancel a request payment by any party [creator or receiver]
     * @param int $trId
     * @param int $user_id
     * @return bool
     */
    public function cancel($trId, $user_id): bool
    {
        try {
            DB::beginTransaction();
            $transaction = $this->userTransaction($trId, $user_id);
            $this->endUserTransaction($transaction);
            $requestPayment = $this->requestPayment($transaction->transaction_reference_id);
            $this->sendCancelNotificationToReceiver($requestPayment);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw new AcceptMoneyException($e->getMessage());
        }
    }

    /**
     * Request Creator canceled the request
     * Send Cancel Notification to receiver
     * @param object $requestPayment
     * @return mixed
     */
    public function sendCancelNotificationToReceiver($requestPayment): mixed
    {
        $processedBy         = preference('processed_by');
        $phoneRegex          = false;
        $emailFilterValidate = false;
        if (!empty($requestPayment->email)) {
            $emailFilterValidate = filter_var($requestPayment->email, FILTER_VALIDATE_EMAIL);
        }
        if (!empty($requestPayment->phone)) {
            $phoneRegex = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $requestPayment->phone);
        }
        if ($emailFilterValidate && "email" == $processedBy) {
            return (new RequestMoneyReceiverCancelEmailService())->send($requestPayment);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new RequestMoneyReceiverCancelSmsService())->send($requestPayment);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new RequestMoneyReceiverCancelEmailService())->send($requestPayment);
            } elseif ($phoneRegex) {
                return (new RequestMoneyReceiverCancelSmsService())->send($requestPayment);
            }
        }
    }

    /**
     * Request receiver canceled the request
     * Send Cancel Notification to creator
     * @param object $requestPayment
     * @return array
     */
    public function sendCancelNotificationToCreator($requestPayment): array
    {
        $processedBy         = preference('processed_by');
        $phoneRegex          = false;
        $emailFilterValidate = false;
        if (!empty($requestPayment->email)) {
            $emailFilterValidate = filter_var($requestPayment->email, FILTER_VALIDATE_EMAIL);
        }
        if (!empty($requestPayment->phone)) {
            $phoneRegex = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $requestPayment->phone);
        }
        if ($emailFilterValidate && "email" == $processedBy) {
            return (new RequestMoneyCreatorCancelEmailService())->send($requestPayment);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new RequestMoneyCreatorCancelSmsService())->send($requestPayment);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new RequestMoneyCreatorCancelEmailService())->send($requestPayment);
            } elseif ($phoneRegex) {
                return (new RequestMoneyCreatorCancelSmsService())->send($requestPayment);
            }
        }
        return true;
    }


    public function sendAcceptNotificationToCreator($requestPayment)
    {
        $processedBy         = preference('processed_by');

        $emailFilterValidate = $this->helper->validateEmailInput($requestPayment->email);
        $phoneRegex          = $this->helper->validatePhoneInput($requestPayment->phone);

        if ($emailFilterValidate && "email" == $processedBy) {
            return (new AcceptMoneyCreatorMailService())->send($requestPayment);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new AcceptMoneyCreatorSmsService())->send($requestPayment);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new AcceptMoneyCreatorMailService())->send($requestPayment);
            } elseif ($phoneRegex) {
                return (new AcceptMoneyCreatorSmsService())->send($requestPayment);
            }
        }

    }

    /**
     *  update user transaction
     *
     * @param int $trId
     * @param int $user_id
     *
     * @throws AcceptMoneyException
     * @return Transaction
     */
    public function  userTransaction($trId, $user_id)
    {
        try {
            $transaction =  Transaction::where(['id' => $trId, 'user_id' => $user_id])
                ->first(['id', 'status', 'transaction_type_id', 'transaction_reference_id']);
            if (empty($transaction)) {
                throw new AcceptMoneyException(__('The :x does not exist.', ['x' => __('transaction')]));
            }
            $transaction->status = 'Blocked';
            $transaction->save();
            return $transaction;
        } catch (Exception $e) {
            throw new AcceptMoneyException(__('The :x does not exist.', ['x' => __('transaction')]));
        }
    }

    /**
     *  update end_user transaction
     *
     * @param $transaction
     *
     * @throws AcceptMoneyException
     * @return bool
     */
    public function endUserTransaction($transaction)
    {
        try {
            $transaction_type  = $transaction->transaction_type_id == Request_Received ? Request_Sent : Request_Received;
            Transaction::where([
                'transaction_reference_id' => $transaction->transaction_reference_id,
                'transaction_type_id' => $transaction_type
            ])
                ->first(['id', 'status'])
                ->update(['status' => 'Blocked']);
            return true;
        } catch (Exception $e) {
            throw new AcceptMoneyException(__('The :x does not exist.', ['x' => __('transaction')]));
        }
    }


    /**
     *  update request_payment transaction
     *
     * @param $reference_id
     *
     * @throws AcceptMoneyException
     * @return RequestPayment
     */
    public function requestPayment($reference_id)
    {
        try {
            $requestPayment =  RequestPayment::find($reference_id);
            $requestPayment->status = 'Blocked';
            $requestPayment->save();
            return $requestPayment;
        } catch (Exception $e) {
            throw new AcceptMoneyException(__('The :x does not exist.', ['x' => __('transaction')]));
        }
    }


    /**
     *  check valid email & phone requst
     *
     * @param int $userId
     * @param int $trId
     * @param string $emailOrPhone
     * @param string $processedBy
     *
     * @return bool
     */
    public function checkEmailPhone($processedBy, $emailOrPhone, $userId, $trId)
    {
        switch ($processedBy) {
            case 'phone':
                $this->checkRequestReceiverPhone($userId, $trId);
                break;
            case 'email_or_phone':
                if (false !== strpos($emailOrPhone, '@')) {
                    $this->checkRequestReceiverEmail($userId, $trId);
                } else {
                    $this->checkRequestReceiverPhone($userId, $trId);
                }
                break;
            default:
                $this->checkRequestReceiverEmail($userId, $trId);
                break;
        }
    }

    /**
     *  check status of request payment
     *
     * @param $trId
     *
     * @throws AcceptMoneyException
     * @return bool
     */
    public function requestPaymentStatus($trId)
    {
        $status = RequestPayment::where('id', $trId)->value('status');

        if (empty($status)) {
            throw new AcceptMoneyException(__("Transaction not found."));
        }

        if ("success" == strtolower($status)) {
            throw new AcceptMoneyException(__("You already accepted the request."));
        }
        return true;
    }

    /**
     *  send notification to admin
     *
     * @param $requestPayment
     *
     * @throws AcceptMoneyException
     * @return bool
     */
    public function notifyToAdmin($requestPayment)
    {
        (new NotifyAdminOnRequestMoneyMailService())->send($requestPayment, ['type' => 'request', 'medium' => 'email']);

        if (!empty($notificationToAdmin['ex'])) {
            throw new AcceptMoneyException($notificationToAdmin['ex']->getMessage());
        }
        return true;
    }
}
