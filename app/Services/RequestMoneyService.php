<?php

/**
 * @package RequestMoneyService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services;

use App\Http\Helpers\Common;
use App\Exceptions\Api\V2\{
    RequestMoneyException,
    PaymentFailedException,
    CurrencyException,
};
use App\Models\{
    RequestPayment,
    Currency,
    User,
};
use App\Services\{
    Mail\RequestMoneyAcceptorMailService,
    Sms\RequestMoneyAcceptorSmsService
};

use Exception, DB;

class RequestMoneyService
{
    /**
     * @var Common;
     */
    protected $helper;

    /**
     * Construct the service class
     *
     * @param Common $helper
     *
     * @return void
     */
    public function __construct(Common $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Email validation for request payment
     *
     * @param email $userId
     * @param string $receiverEmail
     * @return bool
     * @throws RequestMoneyException
     */
    public function checkRequestSenderEmail($userId, $receiverEmail): bool
    {
        $user     = User::where('id', '=', $userId)->first(['email']);
        if (!$user) {
            throw new RequestMoneyException(__('The :x does not exist.', ['x' => __('user')]));
        }
        $receiver = User::where('email', '=', $receiverEmail)->first(['email', 'status']);
        if ($receiver) {
            if ($user->email == $receiver->email) {
                throw new RequestMoneyException(__('You cannot request money to yourself.'));
            }
            $status = strtolower($receiver->status);
            if ("active" != $status) {
                throw new RequestMoneyException(__("The recipient is :x .", ["x" => $receiver->status]));
            }
        }
        return true;
    }

    /**
     * Phone number validation for request payment
     *
     * @param int $userId
     * @param string $receiverPhone
     * @return bool
     * @throws RequestMoneyException
     */
    public function checkRequestSenderPhone($userId, $receiverPhone): bool
    {
        $user     = User::where('id', '=', $userId)->first(['formattedPhone']);
        if (!$user) {
            throw new RequestMoneyException(__("User doesn't exists."));
        }
        $receiver = User::where('formattedPhone', '=', $receiverPhone)->first(['formattedPhone', 'status']);
        if (empty($user->formattedPhone)) {
            throw new RequestMoneyException(__('Please set your phone number first!'));
        }
        if ($receiver) {
            if ($user->formattedPhone == $receiver->formattedPhone) {
                throw new RequestMoneyException(__('You Cannot Request Money To Yourself.'));
            }
            $status = strtolower($receiver->status);
            if ("active" != $status) {
                throw new RequestMoneyException(__("The recipient is :x .", ["x" => $receiver->status]));
            }
        }
        return true;
    }

    /**
     * Get available currencies for request money
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCurrencies(): \Illuminate\Database\Eloquent\Collection
    {
        $currencies = Currency::whereHas('fees_limit', function ($query) {
            $query->transactionType(Request_Received)->where(['has_transaction' => 'Yes']);
        })->active()->fiat()->get(['id', 'code', 'symbol', 'type']);
        return $currencies;
    }


    /**
     * Store request money
     *
     * @param double $amount
     * @param int $currency_id
     * @param string $note
     * @param int $userId
     * @param string $processedBy
     * @return array
     * @throws CurrencyException
     * @throws PaymentFailedException
     */
    public function store($emailOrPhone, $amount, $currency_id, $note, $userId, $processedBy)
    {
        $this->checkEmailPhone($processedBy, $emailOrPhone, $userId);

        $currency = Currency::find($currency_id, ['id']);
        if (!$currency) {
            throw new CurrencyException(__("Currency does not exist in the system."));
        }

        $emailFilterValidate = (new Common())->validateEmailInput(trim($emailOrPhone));
        $phoneRegex          = (new Common())->validatePhoneInput(trim($emailOrPhone));
        $senderInfo          = User::where(['id' => $userId])->first(['email']);
        $userInfo            = (new Common())->getEmailPhoneValidatedUserInfo($emailFilterValidate, $phoneRegex, trim($emailOrPhone));
        $receiverName        = $userInfo->full_name ?? "";

        $this->helper->transactionFees($currency_id, $amount, Request_Received);

        $arr                 = [
            'unauthorisedStatus'  => 401,
            'emailFilterValidate' => $emailFilterValidate,
            'phoneRegex'          => $phoneRegex,
            'processedBy'         => $processedBy,
            'user_id'             => $userId,
            'userInfo'            => $userInfo,
            'currency_id'         => $currency_id,
            'uuid'                => unique_code(),
            'amount'              => $amount,
            'receiver'            => $emailOrPhone,
            'note'                => $note,
            'receiverName'        => $receiverName,
            'senderEmail'         => $senderInfo->email,
        ];

        try {
            DB::beginTransaction();

            $requestMoney = new RequestPayment;

            $requestPayment = $requestMoney->createRequestPayment($arr);

            $arr['transaction_reference_id'] = $requestPayment->id;
            $arr['status']                   = $requestPayment->status;

            $transactionId = $requestMoney->createRequestFromTransaction($arr);

            $requestMoney->createRequestToTransaction($arr);

            $requestMoney->createRequestCreatorWallet($arr['user_id'], $arr['currency_id']);

            DB::commit();

            $this->sendNotificationToAcceptor($requestPayment);

            return [
                'status' => true,
                'ref_id' => $transactionId,
                'receiverName' => $receiverName
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw new RequestMoneyException($e->getMessage());
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
    public function checkEmailPhone($processedBy, $emailOrPhone, $userId)
    {
        switch ($processedBy) {
            case 'phone':
                $this->checkRequestSenderPhone($userId, $emailOrPhone);
                break;
            case 'email_or_phone':
                if (false !== strpos($emailOrPhone, '@')) {
                    $this->checkRequestSenderEmail($userId, $emailOrPhone);
                } else {
                    $this->checkRequestSenderPhone($userId, $emailOrPhone);
                }
                break;
            default:
                $this->checkRequestSenderEmail($userId, $emailOrPhone);
                break;
        }
        return true;
    }


    public function sendNotificationToAcceptor($requestPayment)
    {
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($requestPayment->email);
        $phoneRegex          = $this->helper->validatePhoneInput($requestPayment->phone);

        if ($emailFilterValidate && "email" == $processedBy) {
            return (new RequestMoneyAcceptorMailService())->send($requestPayment);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new RequestMoneyAcceptorSmsService())->send($requestPayment);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new RequestMoneyAcceptorMailService())->send($requestPayment);
            } elseif ($phoneRegex) {
                return (new RequestMoneyAcceptorSmsService())->send($requestPayment);
            }
        }
    }
}
