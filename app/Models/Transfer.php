<?php

namespace App\Models;

use App\Http\Controllers\Users\EmailController;
use App\Services\Sms\SendMoneySmsService;
use Illuminate\Database\Eloquent\Model;
use Exception, DB, Common;
use App\Services\Mail\SendMoney\{
    NotifyAdminOnSendMoneyMailService,
    EmailToReceiverMailService, 
};
use App\Models\{
    Transaction,
    Wallet
};

class Transfer extends Model
{
    protected $table = 'transfers';

    protected $fillable = ['sender_id', 'receiver_id', 'currency_id', 'bank_id', 'file_id', 'uuid', 'fee', 'amount', 'note', 'email', 'phone', 'status'];

    protected $helper;
    protected $emailObject;
    public function __construct()
    {
        $this->helper      = new Common();
        $this->emailObject = new EmailController();
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getTransfersUserName($user)
    {
        $getUserEndUserTransfer = $this->where(function ($q) use ($user) {
            $q->where(['sender_id' => $user])->orWhere(['receiver_id' => $user]);
        })
        ->with(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name'])
        ->first(['sender_id', 'receiver_id']);

        if (!empty($getUserEndUserTransfer)) {
            if ($getUserEndUserTransfer->sender_id == $user) {
                return $getUserEndUserTransfer->sender;
            }

            if ($getUserEndUserTransfer->receiver_id == $user) {
                return $getUserEndUserTransfer->receiver;
            }
        }
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getTransfersUsersResponse($search)
    {
        $getTransfersUsers = $this->whereHas('sender', function ($query) use ($search) {
            $query->where('first_name', 'LIKE', '%' . $search . '%')->orWhere('last_name', 'LIKE', '%' . $search . '%');
        })
        ->distinct('sender_id')
        ->with(['sender:id,first_name,last_name'])
        ->get(['sender_id'])
        ->map(function ($transferA) {
            $arr['user_id']    = $transferA->sender_id;
            $arr['first_name'] = $transferA->sender?->first_name;
            $arr['last_name']  = $transferA->sender?->last_name;
            return $arr;
        });

        $getTransfersEndUsers = $this->whereHas('receiver', function ($query) use ($search) {
            $query->where('first_name', 'LIKE', '%' . $search . '%')->orWhere('last_name', 'LIKE', '%' . $search . '%');
        })
        ->distinct('receiver_id')
        ->with(['receiver:id,first_name,last_name'])
        ->get(['receiver_id'])
        ->map(function ($transferB) {
            $arr['user_id']    = $transferB->receiver_id;
            $arr['first_name'] = $transferB->receiver?->first_name;
            $arr['last_name']  = $transferB->receiver?->last_name;
            return $arr;
        });

        if ($getTransfersUsers->isNotEmpty()) {
            return $getTransfersUsers->unique();
        }

        if ($getTransfersEndUsers->isNotEmpty()) {
            return $getTransfersEndUsers->unique();
        }

        if ($getTransfersUsers->isNotEmpty() && $getTransfersEndUsers->isNotEmpty()) {
            $getUniqueTransfersUsers = ($getTransfersUsers->merge($getTransfersEndUsers))->unique();
            return $getUniqueTransfersUsers;
        }
    }

    /**
     * [Transfers Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string]    $status [Status]
     * @param  [null/id]   $user   [User ID]
     * @return [void]      [All Query Results]
     */
    public function getTransfersList($from, $to, $status, $currency, $user)
    {
        $conditions = [];

        $date_range = (!empty($from) && !empty($to)) ? 'Available' : null;

        if (!empty($status) && $status != 'all') {
            $conditions['transfers.status'] = $status;
        }
        if (!empty($currency) && $currency != 'all') {
            $conditions['transfers.currency_id'] = $currency;
        }

        $transfers = $this->with([
            'sender:id,first_name,last_name',
            'receiver:id,first_name,last_name',
            'currency:id,code',
        ])->where($conditions);

        //if user is not empty, check both sender_id & receiver_id columns
        if (!empty($user)) {
            $transfers->where(function ($q) use ($user) {
                $q->where(['transfers.sender_id' => $user])->orWhere(['transfers.receiver_id' => $user]);
            });
        }

        if (!empty($date_range)) {
            $transfers->where(function ($query) use ($from, $to) {
                $query->whereDate('transfers.created_at', '>=', $from)->whereDate('transfers.created_at', '<=', $to);
            })->select('transfers.*');
        } else {
            $transfers->select('transfers.*');
        }

        return $transfers;
    }

    //common functions - starts
    public function createTransfer($arr)
    {
        $transfer              = new self();
        $transfer->sender_id   = $arr['user_id'];
        $transfer->receiver_id = isset($arr['userInfo']) ? $arr['userInfo']->id : null;
        $transfer->currency_id = $arr['currency_id'];
        $transfer->uuid        = $arr['uuid'];
        $transfer->fee         = $arr['fee'];
        $transfer->amount      = $arr['amount'];
        $transfer->note        = $arr['note'];
        if ($arr['emailFilterValidate']) {
            $transfer->email = $arr['receiver'];
        } elseif ($arr['phoneRegex']) {
            $transfer->phone = $arr['receiver'];
        }
        if (isset($transfer->receiver_id)) {
            $transfer->status = 'Success';
        } else {
            $transfer->status = 'Pending';
        }
        $transfer->save();

        return $transfer;
    }

    public function createTransferredTransaction($arr)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->end_user_id              = isset($arr['userInfo']) ? $arr['userInfo']->id : null;
        $transaction->currency_id              = $arr['currency_id'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->transaction_type_id      = Transferred;
        $transaction->user_type                = isset($arr['userInfo']) ? 'registered' : 'unregistered';
        if ($arr['emailFilterValidate']) {
            $transaction->email = $arr['receiver'];
        } elseif ($arr['phoneRegex']) {
            $transaction->phone = $arr['receiver'];
        }
        $transaction->subtotal          = $arr['amount'];
        $transaction->percentage        = @$arr['charge_percentage'] ? @$arr['charge_percentage'] : 0;
        $transaction->charge_percentage = @$arr['charge_percentage'] ? $arr['p_calc'] : 0;
        $transaction->charge_fixed      = @$arr['charge_fixed'] ? @$arr['charge_fixed'] : 0;
        $transaction->total             = '-' . ($arr['total']);
        $transaction->note              = $arr['note'];
        $transaction->status            = $arr['status'];
        $transaction->save();

        return $transaction->id;
    }

    public function createReceivedTransaction($arr)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = isset($arr['userInfo']) ? $arr['userInfo']->id : null;
        $transaction->end_user_id              = $arr['user_id'];
        $transaction->currency_id              = $arr['currency_id'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->transaction_type_id      = Received;
        $transaction->user_type                = isset($arr['userInfo']) ? 'registered' : 'unregistered';
        if ($arr['emailFilterValidate']) {
            $transaction->email = $arr['receiver'];
        } elseif ($arr['phoneRegex']) {
            $transaction->phone = $arr['receiver'];
        }
        $transaction->subtotal          = $arr['amount'];
        $transaction->percentage        = 0;
        $transaction->charge_percentage = 0;
        $transaction->charge_fixed      = 0;
        $transaction->total             = $arr['amount'];
        $transaction->note              = $arr['note'];
        $transaction->status            = $arr['status'];
        $transaction->save();

        if (module('Referral') && settings('referral_enabled') == 'Yes' && isset($arr['userInfo'])) {
            $currency   = Currency::find($arr['currency_id'], ['code']);
            $refAwardData = [
                'userId'          => $arr['userInfo']->id,
                'currencyId'      => $arr['currency_id'],
                'currencyCode'    => $currency->code,
                'presentAmount'   => $arr['amount'],
                'paymentMethodId' => Mts,
                'transactionType' => 'Receive Money',
            ];

            $awardResponse = (new \Modules\Referral\Entities\ReferralAward)->checkReferralAward($refAwardData);
        }

        return (isset($awardResponse)) ? $awardResponse : null;
    }

    public function updateSenderWallet($senderWallet, $totalWithFee)
    {
        $senderWallet->balance = $senderWallet->balance - $totalWithFee;
        $senderWallet->save();
    }

    public function createOrUpdateReceiverWallet($arr)
    {
        if (!empty($arr['transfer_receiver_id']) && isset($arr['userInfo'])) {
            $receiverWallet = Wallet::where(['user_id' => $arr['userInfo']->id, 'currency_id' => $arr['currency_id']])->first(['id', 'balance']);

            if (empty($receiverWallet)) {
                $wallet = Wallet::createWallet($arr['userInfo']->id, $arr['currency_id']);
                $wallet->increment('balance', $arr['amount']);
            } else {
                $receiverWallet->increment('balance', $arr['amount']);
            }
        }
    }

    /**
     * Process Send Money Confirm
     * param  array  $arr
     * param  string $clearSessionFrom
     * return object
     */
    public function processSendMoneyConfirmation($arr = [], $clearSessionFrom)
    {
        $response = ['status' => 401];

        try {
            //Backend Validation - Wallet Balance Again Amount Check - Starts here
            $checkWalletBalance = $this->helper->checkWalletBalanceAgainstAmount($arr['total'], $arr['currency_id'], $arr['user_id']);
            if ($checkWalletBalance) {
                $response['transactionOrTransferId'] = null;
                if ($clearSessionFrom == 'web') {
                    $response['ex']['message'] = __("Not have enough balance.");
                    return $response;
                }
                $response['ex']['message'] = __("Sorry, do not have enough funds to perform the operation.");
                return $response;
                //Backend Validation - Wallet Balance Again Amount Check - Ends here
            } else {
                DB::beginTransaction();

                //Create Transfer
                $transfer = self::createTransfer($arr);

                //Create Transferred Transaction
                $arr['transaction_reference_id'] = $transfer->id;
                $arr['status']                   = $transfer->status;
                $transferredTransactionId        = self::createTransferredTransaction($arr);

                //Create Received Transaction
                $transactionResponse = self::createReceivedTransaction($arr);

                //Update Sender Wallet
                self::updateSenderWallet($arr['senderWallet'], $arr['total']);

                //Create Or Update Receiver Wallet
                $arr['transfer_receiver_id'] = $transfer->receiver_id;
                self::createOrUpdateReceiverWallet($arr);

                DB::commit();

                // Notification Email/SMS
                $this->notificationToSender($transfer);
                (new NotifyAdminOnSendMoneyMailService)->send($transfer, ['type' => 'send', 'medium' => 'email']);
                if (module('Referral') && settings('referral_enabled') == 'Yes' && !empty($transactionResponse)) {
                    if (isset($transactionResponse['email_status']) && $transactionResponse['email_status'] === 200 && !empty($transactionResponse['email_details'])) {
                        $awardInfo = (new \Modules\Referral\Services\Email\ReferralAwardMailService)->send($transactionResponse['email_details']);
                        \Modules\Referral\Jobs\ProcessRewardEmail::dispatch($awardInfo);
                    }
                }

                return $response = [
                    'status' => 200,
                    'transactionOrTransferId' => $transferredTransactionId,
                ];
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->clearSessionWithRedirect('transInfo', $e, 'moneytransfer');
            $response['transactionOrTransferId'] = null;
            $response['ex']['message'] = $e->getMessage();
            return $response;
        }
    }

    public function notificationToSender($transfer)
    {
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($transfer->email);
        $phoneRegex          = $this->helper->validatePhoneInput($transfer->phone);

        if ($emailFilterValidate && "email" == $processedBy) {
            (new EmailToReceiverMailService)->send($transfer);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            (new SendMoneySmsService)->send($transfer);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                (new EmailToReceiverMailService)->send($transfer);
            } elseif ($phoneRegex) {
                (new SendMoneySmsService)->send($transfer);
            }
        }
    }

    public static function updateStatus($transferId, string $status): object
    {
        $transfer = Transfer::find($transferId);

        if ($transfer) {
            $transfer->status = $status;
            $transfer->save();
        }

        return $transfer;
    }
}
