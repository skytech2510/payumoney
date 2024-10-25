<?php

/**
 * @package SendMoneyService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 20-11-2022
 */

namespace App\Services;

use App\Http\Helpers\Common;
use App\Enums\Status;
use App\Exceptions\Api\V2\{
    PaymentFailedException,
    SendMoneyException
};
use App\Models\{
    Transfer,
    Wallet,
    User
};
use App\Services\{
    Mail\ReceiveMoneyMailService,
    Mail\SendMoneyMailService,
    Sms\ReceiveMoneySmsService,
    Sms\SendMoneySmsService
};
use Exception, DB;
use App\Services\Mail\SendMoney\NotifyAdminOnSendMoneyMailService;

class SendMoneyService
{
    /**
     * @var Common;
     */
    protected $helper;
    protected $transactionType;
    protected $transfer;


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
        $this->transactionType = Transferred;
        $this->transfer = new Transfer;
    }


    /**
     * Validates if the payable request can be processed or not
     *
     * @param string $email
     *
     * @return bool
     */
    public function validateEmail($email)
    {
        $receiver = User::whereEmail($email)->first();

        // Check if receiver exists
        if (is_null($receiver)) {
            return true;
        }

        return $this->validateReceiverUserEmail($receiver);
    }


    /**
     * Validates receiver email for sending money
     *
     * @param User|null $receiver
     *
     * @return bool
     *
     * @throws SendMoneyException
     */
    protected function validateReceiverUserEmail($receiver)
    {
        if (is_null($receiver)) {
            return true;
        }

        $user = auth()->user();

        // Check if both user and receiver email addresses are same
        if ($user->email ==  $receiver->email) {
            throw new SendMoneyException(__("You cannot send money to yourself."));
        }

        // Check if receiver is a suspended user
        if (in_array($receiver->status, [Status::SUSPENDED, Status::INACTIVE])) {
            throw new SendMoneyException(__("You cannot send money to a :x user.", ["x" => $receiver->status]));
        }

        return true;
    }



    /**
     * Validates if the payable request can be processed or not
     *
     * @param string $phone
     *
     * @return bool
     *
     * @throws SendMoneyException
     */
    public function validatePhoneNumber($phone)
    {
        $user = auth()->user();

        if (is_null($user->formattedPhone) || empty($user->formattedPhone)) {
            throw new SendMoneyException(__("Please set your phone number first."));
        }

        $receiver = User::select('formattedPhone', 'status')->where("formattedPhone", $phone)->first();

        if (is_null($receiver)) {
            return true;
        }

        return $this->validateReceiverUserPhone($receiver);
    }


    /**
     * Validate receiver phone number for sending money
     *
     * @param User $receiver
     * @return bool
     *
     * @throws SendMoneyException
     */
    protected function validateReceiverUserPhone($receiver)
    {
        // Check if receiver exists
        if (is_null($receiver)) {
            return true;
        }

        $user = auth()->user();

        // Check if both user and receiver email addresses are same
        if ($user->formattedPhone ==  $receiver->formattedPhone) {
            throw new SendMoneyException(__("You cannot send money to yourself."));
        }

        // Check if receiver is a suspended user
        if (in_array($receiver->status, [Status::SUSPENDED, Status::INACTIVE])) {
            throw new SendMoneyException(__("You cannot send money to a :x user.", ["x" => $receiver->status]));
        }

        return true;
    }


    /**
     * Get available currencies of the user
     *
     * @return array
     */
    public function getSelfCurrencies()
    {
        $result = [];

        Wallet::with('currency:id,code,type')
            ->where("user_id", auth()->id())
            ->whereHas("active_currency")
            ->join('fees_limits', 'fees_limits.currency_id', 'wallets.currency_id')
            ->where('fees_limits.has_transaction', 'Yes')
            ->where('fees_limits.transaction_type_id', $this->transactionType)
            ->get()
            ->map(function ($item) use (&$result) {
                $result[$item->currency_id] = [
                    'id' => $item->currency_id,
                    'code' => optional($item->currency)->code,
                    'is_default' => $item->is_default,
                    'type' => optional($item->currency)->type
                ];
            });

        return array_values($result);
    }



    /**
     * Check the requested amount and currency in user wallet and Fees limit
     *
     * @param int $currencyId Currency Id
     * @param float $amount
     *
     * @return array
     *
     * @throws SendMoneyException
     */
    public function validateAmountLimit($currencyId, $amount)
    {
        $userId = auth()->id();

        $currencyFee = $this->helper->transactionFees($currencyId, $amount, $this->transactionType);

        $this->helper->amountIsInLimit($currencyFee, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $currencyFee->total_amount);

        return $currencyFee;

    }


    public function sendMoneyConfirm($identifier, $currencyId, $amount, $totalFees, $note)
    {
        $userId =  auth()->id();
        $identifier = trim($identifier);
        $email = $this->helper->validateEmailInput($identifier);
        $phone = $this->helper->validatePhoneInput($identifier);
        if (!$email && !$phone) {
            throw new SendMoneyException(__("Invalid send money request."));
        }

        $currencyFee = $this->helper->transactionFees($currencyId, $amount, $this->transactionType);

        $this->helper->amountIsInLimit($currencyFee, $amount);

        $this->helper->checkWalletAmount($userId, $currencyId, $currencyFee->total_amount);

        $senderWallet = $this->helper->getWallet($userId, $currencyId);

        $receiver = User::where('email', $identifier)->orWhere('formattedPhone', $identifier)->first();

        $arr = [
            'emailFilterValidate' => $email,
            'phoneRegex' => $phone,
            'processedBy' => preference("processed_by"),
            'user_id' => $userId,
            'currency_id' => $currencyId,
            'uuid' => unique_code(),
            'fee' => $totalFees,
            'amount' => $amount,
            'note' => trim($note),
            'receiver' => $identifier,
            'charge_percentage' => $currencyFee->charge_percentage,
            'charge_fixed' => $currencyFee->charge_fixed,
            'p_calc' => $currencyFee->fees_percentage,
            'total' => $currencyFee->total_amount,
            'senderWallet' => $senderWallet,
        ];

        if (!is_null($receiver)) {
            $arr['userInfo'] = $receiver;
        }

        try {
            DB::beginTransaction();
            //Create Transfer
            $transfer = $this->transfer->createTransfer($arr);
            //Create Transferred Transaction
            $arr['transaction_reference_id'] = $transfer->id;
            $arr['status']                   = $transfer->status;
            $this->transfer->createTransferredTransaction($arr);
            //Create Received Transaction
            $this->transfer->createReceivedTransaction($arr);
            //Update Sender Wallet
            $this->transfer->updateSenderWallet($arr['senderWallet'], $arr['total']);
            //Create Or Update Receiver Wallet
            $arr['transfer_receiver_id'] = $transfer->receiver_id;
            $this->transfer->createOrUpdateReceiverWallet($arr);

            DB::commit();

            // Sms & Email
            $this->notificationToSender($transfer);
            $this->notificationToReceiver($transfer);
            $this->notificationToAdmin($transfer);

            return [
                'status' => true,
                'tr_ref_id' => $transfer->id,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw new SendMoneyException($e->getMessage());
        }

    }

    public function notificationToSender($transfer)
    {
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($transfer->email);
        $phoneRegex          = $this->helper->validatePhoneInput($transfer->phone);

        if ($emailFilterValidate && "email" == $processedBy) {
            return (new SendMoneyMailService())->send($transfer);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new SendMoneySmsService())->send($transfer);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new SendMoneyMailService())->send($transfer);
            } elseif ($phoneRegex) {
                return (new SendMoneySmsService())->send($transfer);
            }
        }
    }

    public function notificationToReceiver($transfer)
    {
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($transfer->email);
        $phoneRegex          = $this->helper->validatePhoneInput($transfer->phone);

        if ($emailFilterValidate && "email" == $processedBy) {
            return (new ReceiveMoneyMailService())->send($transfer);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            return (new ReceiveMoneySmsService())->send($transfer);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                return (new ReceiveMoneyMailService())->send($transfer);
            } elseif ($phoneRegex) {
                return (new ReceiveMoneySmsService())->send($transfer);
            }
        }
    }


    //Admin Notification
    public function notificationToAdmin($transfer)
    {
        (new NotifyAdminOnSendMoneyMailService)->send($transfer, ['type' => 'send', 'medium' => 'email']);

        return true;
    }
}
