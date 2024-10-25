<?php

namespace App\Models;

use App\Http\Controllers\Users\EmailController;
use App\Services\Sms\ReceiveMoneySmsService;
use Illuminate\Database\Eloquent\Model;
use App\Services\Mail\RequestMoney\{
    NotifyAdminOnRequestMoneyMailService,
    RequestReceiverMailService,
    RequestSenderMailService,
};
use App\Http\Helpers\Common;
use DB, Exception;


class RequestPayment extends Model
{
    protected $table    = 'request_payments';
    protected $fillable = [
        'user_id',
        'receiver_id',
        'currency_id',
        'uuid',
        'amount',
        'accept_amount',
        'email',
        'phone',
        'purpose',
        'note',
        'status',
    ];

    protected $helper;
    protected $emailObject;
    public function __construct()
    {
        $this->helper = new Common();
        $this->emailObject  = new EmailController();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getRequestPaymentsUserName($user)
    {
        $getUserEndUserRequestPayments = $this->where(function ($q) use ($user) {
            $q->where(['user_id' => $user])->orWhere(['receiver_id' => $user]);
        })
            ->with(['user:id,first_name,last_name', 'receiver:id,first_name,last_name'])
            ->first(['user_id', 'receiver_id']);

        if (!empty($getUserEndUserRequestPayments)) {
            if ($getUserEndUserRequestPayments->user_id == $user) {
                return $getUserEndUserRequestPayments->user;
            }

            if ($getUserEndUserRequestPayments->receiver_id == $user) {
                return $getUserEndUserRequestPayments->receiver;
            }
        }
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getRequestPaymentsUsersResponse($search)
    {
        $getRequestPaymentsUsers = $this->whereHas('user', function ($query) use ($search) {
            $query->where('first_name', 'LIKE', '%' . $search . '%')->orWhere('last_name', 'LIKE', '%' . $search . '%');
        })
        ->distinct('user_id')
        ->with(['user:id,first_name,last_name'])
        ->get(['user_id'])
        ->map(function ($requestPaymentA) {
            $arr['user_id']    = $requestPaymentA->user_id;
            $arr['first_name'] = $requestPaymentA->user->first_name;
            $arr['last_name']  = $requestPaymentA->user->last_name;
            return $arr;
        });

        $getRequestPaymentsEndUsers = $this->whereHas('receiver', function ($query) use ($search) {
            $query->where('first_name', 'LIKE', '%' . $search . '%')->orWhere('last_name', 'LIKE', '%' . $search . '%');
        })
        ->distinct('receiver_id')
        ->with(['receiver:id,first_name,last_name'])
        ->get(['receiver_id'])
        ->map(function ($requestPaymentB) {
            $arr['user_id']    = $requestPaymentB->receiver_id;
            $arr['first_name'] = $requestPaymentB->receiver->first_name;
            $arr['last_name']  = $requestPaymentB->receiver->last_name;
            return $arr;
        });

        if ($getRequestPaymentsUsers->isNotEmpty()) {
            return $getRequestPaymentsUsers->unique();
        }

        if ($getRequestPaymentsEndUsers->isNotEmpty()) {
            return $getRequestPaymentsEndUsers->unique();
        }

        if ($getRequestPaymentsUsers->isNotEmpty() && $getRequestPaymentsEndUsers->isNotEmpty()) {
            $getUniqueRequestPaymentsUsers = ($getRequestPaymentsUsers->merge($getRequestPaymentsEndUsers))->unique();
            return $getUniqueRequestPaymentsUsers;
        }
    }

    /**
     * [Exchanges Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string]    $status [Status]
     * @param  [null/id]   $user   [User ID]
     * @return [query]     [All Query Results]
     */

    public function getRequestPaymentsList($from, $to, $status, $currency, $user)
    {
        $conditions = [];

        $date_range = (!empty($from) && !empty($to)) ? 'Available' : null;

        if (!empty($status) && $status != 'all') {
            $conditions['request_payments.status'] = $status;
        }
        if (!empty($currency) && $currency != 'all') {
            $conditions['request_payments.currency_id'] = $currency;
        }
        if (!empty($type) && $type != 'all') {
            $conditions['request_payments.type'] = $type;
        }

        $request_payments = $this->with([
            'user:id,first_name,last_name',
            'receiver:id,first_name,last_name',
            'currency:id,code',
        ])->where($conditions);

        //if user is not empty, check both user_id & receiver_id columns
        if (!empty($user)) {
            $request_payments->where(function ($q) use ($user) {
                $q->where(['request_payments.user_id' => $user])->orWhere(['request_payments.receiver_id' => $user]);
            });
        }

        if (!empty($date_range)) {
            $request_payments->where(function ($query) use ($from, $to) {
                $query->whereDate('request_payments.created_at', '>=', $from)->whereDate('request_payments.created_at', '<=', $to);
            })->select('request_payments.*');
        } else {
            $request_payments->select('request_payments.*');
        }

        return $request_payments;
    }

    //common functions - starts
    public function createRequestPayment($arr)
    {
        $requestPayment              = new self();
        $requestPayment->user_id     = $arr['user_id'];
        $requestPayment->receiver_id = isset($arr['userInfo']) ? $arr['userInfo']->id : null;
        $requestPayment->currency_id = $arr['currency_id'];
        $requestPayment->uuid        = $arr['uuid'];
        $requestPayment->amount      = $arr['amount'];
        if ($arr['emailFilterValidate']) {
            $requestPayment->email = $arr['receiver'];
        } elseif ($arr['phoneRegex']) {
            $requestPayment->phone = $arr['receiver'];
        }
        $requestPayment->status = 'Pending';
        $requestPayment->note = $arr['note'];
        $requestPayment->save();
        return $requestPayment;
    }

    public function createRequestFromTransaction($arr)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->currency_id              = $arr['currency_id'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->transaction_type_id      = Request_Sent;
        if (!empty($arr['userInfo'])) {
            $transaction->end_user_id = $arr['userInfo']->id;
            $transaction->user_type   = 'registered';
        } else {
            $transaction->user_type = 'unregistered';
        }
        if ($arr['emailFilterValidate']) {
            $transaction->email = $arr['receiver'];
        } elseif ($arr['phoneRegex']) {
            $transaction->phone = $arr['receiver'];
        }
        $transaction->subtotal = $arr['amount'];
        $transaction->total    = $arr['amount'];
        $transaction->note     = $arr['note'];
        $transaction->status   = $arr['status'];
        $transaction->save();
        return $transaction->id;
    }

    public function createRequestToTransaction($arr)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = isset($arr['userInfo']) ? $arr['userInfo']->id : null;
        $transaction->end_user_id              = $arr['user_id'];
        $transaction->currency_id              = $arr['currency_id'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->transaction_type_id      = Request_Received;
        if (!empty($arr['userInfo'])) {
            $transaction->user_type = 'registered';
        } else {
            $transaction->user_type = 'unregistered';
        }
        if ($arr['emailFilterValidate']) {
            $transaction->email = $arr['receiver'];
        } elseif ($arr['phoneRegex']) {
            $transaction->phone = $arr['receiver'];
        }
        $transaction->subtotal = $arr['amount'];
        $transaction->total    = '-' . $arr['amount'];
        $transaction->note     = $arr['note'];
        $transaction->status   = $arr['status'];
        $transaction->save();
    }

    public function createRequestCreatorWallet($user_id, $currency_id)
    {
        $createWalletIfNotExist = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['id']);
        if (empty($createWalletIfNotExist)) {
            $wallet              = new Wallet();
            $wallet->user_id     = $user_id;
            $wallet->currency_id = $currency_id;
            $wallet->balance     = 0.00;
            $wallet->is_default  = 'No';
            $wallet->save();
        }
    }

    /**
     * Rquest Create Money Confirm
     * param  array  $arr
     * param  string $clearSessionFrom
     * return object
     */
    public function processRequestCreateConfirmation($arr = [], $clearSessionFrom)
    {
        $response = ['status' => 401];
        try {
            DB::beginTransaction();

            //Create Request Payment
            $requestPayment = self::createRequestPayment($arr);

            //Create Request From Transaction
            $arr['transaction_reference_id'] = $requestPayment->id;
            $arr['status']                   = $requestPayment->status;

            $requestFromTransactionId = self::createRequestFromTransaction($arr);

            //Create RequestTo Transaction
            self::createRequestToTransaction($arr);

            //Create Request Creator Wallet - If it does not exist
            self::createRequestCreatorWallet($arr['user_id'], $arr['currency_id']);

            DB::commit();

            $this->notificationToReceiver($requestPayment);

            $transactionOrReqPaymentId             = ($clearSessionFrom == 'web') ? $requestFromTransactionId : $requestPayment->id;
            $response['transactionOrReqPaymentId'] = $transactionOrReqPaymentId;

            $response['status'] = 200;
            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            if ($clearSessionFrom == 'web') {
                $this->helper->clearSessionWithRedirect('transInfo', $e, 'request_payment/add');
            }
            $response['transactionOrReqPaymentId'] = null;
            $response['ex']['message']             = $e->getMessage();
            return $response;
        }
    }

    public function notificationToReceiver($requestPayment)
    {
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($requestPayment->email);
        $phoneRegex          = $this->helper->validatePhoneInput($requestPayment->phone);
        
        if ($emailFilterValidate && "email" == $processedBy) {
            (new RequestReceiverMailService())->send($requestPayment);
        } elseif ($phoneRegex && "phone" == $processedBy) {
            (new ReceiveMoneySmsService())->send($requestPayment);
        } elseif ("email_or_phone" == $processedBy) {
            if ($emailFilterValidate) {
                (new RequestReceiverMailService())->send($requestPayment);
            } elseif ($phoneRegex) {
                (new ReceiveMoneySmsService())->send($requestPayment);
            }
        }
    }

    public function updateRequestPayment($arr)
    {
        $requestPayment                = $this->with(['user:id,first_name,last_name,phone,carrierCode,email,picture', 'receiver:id,first_name,last_name,picture', 'currency:id,symbol,code'])->find($arr['requestPaymentId']);
        $requestPayment->accept_amount = $arr['accept_amount'];
        $requestPayment->status        = "Success";
        $requestPayment->save();
        return $requestPayment;
    }

    public function udpateRequestCreatorTransaction($arr, $requestPayment)
    {
        //Update Request Creator Transaction Information
        $transaction                    = Transaction::where(['user_id' => $requestPayment->user_id, 'currency_id' => $arr['currency_id'], 'transaction_reference_id' => $arr['requestPaymentId'], 'transaction_type_id' => Request_Sent])->first(['id', 'percentage', 'charge_percentage', 'charge_percentage', 'subtotal', 'total', 'status']);
        $transaction->percentage        = 0;
        $transaction->charge_percentage = 0;
        $transaction->charge_fixed      = 0;
        $transaction->subtotal          = $arr['accept_amount'];
        $transaction->total             = $arr['accept_amount'];
        $transaction->status            = 'Success';
        $transaction->save();


        if (module('Referral') && settings('referral_enabled') == 'Yes') {
            $currency   = Currency::find($arr['currency_id'], ['code']);
            
            $refAwardData = [
                'userId'          => $requestPayment->user_id,
                'currencyId'      => $arr['currency_id'],
                'currencyCode'    => $currency->code,
                'presentAmount'   => $arr['accept_amount'],
                'paymentMethodId' => Mts,
                'transactionType' => 'Accept Money'
            ];

            $awardResponse = (new \Modules\Referral\Entities\ReferralAward)->checkReferralAward($refAwardData);
        }

        return (isset($awardResponse)) ? $awardResponse : null;
    }

    public function udpateRequestAcceptorTransaction($arr, $requestPayment)
    {
        $transaction = Transaction::where(['user_id' => $requestPayment->receiver_id, 'currency_id' => $arr['currency_id'], 'transaction_reference_id' => $arr['requestPaymentId'], 'transaction_type_id' => Request_Received])->first(['id', 'percentage', 'charge_percentage', 'charge_percentage', 'subtotal', 'total', 'status']);

        $transaction->percentage        = @$arr['charge_percentage'] ? @$arr['charge_percentage'] : 0;
        $transaction->charge_percentage = $arr['percentage_fee'];
        $transaction->charge_fixed      = $arr['fixed_fee'];
        $transaction->subtotal          = $arr['accept_amount'];
        $t_total                        = $transaction->subtotal + ($transaction->charge_percentage + $transaction->charge_fixed);
        $transaction->total             = '-' . $t_total;
        $transaction->status            = 'Success';
        $transaction->save();
        return $transaction->id;
    }

    public function updateRequestCreatorWallet($arr, $requestPayment)
    {
        $requestCreatorWallet = Wallet::where(['user_id' => $requestPayment->user_id, 'currency_id' => $arr['currency_id']])->first(['id', 'balance']);
        if (!empty($requestCreatorWallet)) {
            $requestCreatorWallet->balance = $requestCreatorWallet->balance + $arr['accept_amount'];
            $requestCreatorWallet->save();
        } else {
            $requestCreatorWallet              = new Wallet();
            $requestCreatorWallet->balance     = $arr['accept_amount'];
            $requestCreatorWallet->user_id     = $requestPayment->user_id;
            $requestCreatorWallet->currency_id = $arr['currency_id'];
            $requestCreatorWallet->is_default  = 'No';
            $requestCreatorWallet->save();
        }
    }

    public function updateRequestAcceptorWallet($arr)
    {
        $requestAcceptorWallet          = Wallet::where(['user_id' => $arr['user_id'], 'currency_id' => $arr['currency_id']])->first(['id', 'balance']);
        $requestAcceptorWallet->balance = $requestAcceptorWallet->balance - ($arr['accept_amount'] + $arr['fee']);
        $requestAcceptorWallet->save();
    }

    /**
     * Process Request Accept Confirm
     * param  array  $arr
     * param  string $clearSessionFrom
     * return object
     */
    public function processRequestAcceptConfirmation($arr = [], $clearSessionFrom)
    {
        $response = ['status' => 401];

        try {
            //Backend Validation - Wallet Balance Again Amount Check (checked by giving hard-coded value - OK) - Starts here
            $checkWalletBalance = $this->helper->checkWalletBalanceAgainstAmount($arr['total'], $arr['currency_id'], $arr['user_id']);
            if ($checkWalletBalance == true) {
                $response['reqPayment'] = null;
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
                $requestPayment = self::updateRequestPayment($arr);

                //Update Request Creator Transaction
                $awardResponse = self::udpateRequestCreatorTransaction($arr, $requestPayment);

                //Update Request Acceptor Transaction
                $reqAcceptTransactionId = self::udpateRequestAcceptorTransaction($arr, $requestPayment);

                //Update Request Creator Wallet
                self::updateRequestCreatorWallet($arr, $requestPayment);

                //Update Request Acceptor Wallet
                self::updateRequestAcceptorWallet($arr);

                DB::commit();

                $resArray = [];
                $resArray = [
                    'transaction_id'    => $reqAcceptTransactionId,
                    'requestPaymentObj' => $requestPayment,
                ];

                $requestPaymentData     = ($clearSessionFrom == 'web') ? $resArray : $requestPayment->id;
                $response['reqPayment'] = $requestPaymentData;

                if ($arr['emailFilterValidate'] && $arr['processedBy'] == "email") {
                    (new RequestSenderMailService)->send($requestPayment);
                } elseif ($arr['phoneRegex'] && $arr['processedBy'] == "phone") {
                    (new AcceptMoneyCreatorSmsService)->send($requestPayment);
                } elseif ($arr['processedBy'] == "email_or_phone") {
                    if ($arr['emailFilterValidate']) {
                        (new RequestSenderMailService)->send($requestPayment);
                    } elseif ($arr['phoneRegex']) {
                        (new AcceptMoneyCreatorSmsService)->send($requestPayment);
                    }
                }

                //Admin Notification
                $requestPayment['charge_percentage'] = $arr['percentage_fee'];
                $requestPayment['charge_fixed']      = $arr['fixed_fee'];
                
                // Send referral award email/sms to users
                (new NotifyAdminOnRequestMoneyMailService())->send($requestPayment, ['type' => 'request', 'medium' => 'email']);
                if (module('Referral') && settings('referral_enabled') == 'Yes' && !empty($awardResponse)) {
                    if (isset($awardResponse['email_status']) && $awardResponse['email_status'] === 200 && !empty($awardResponse['email_details'])) {
                        $awardInfo = (new \Modules\Referral\Services\Email\ReferralAwardMailService)->send($awardResponse['email_details']);
                        \Modules\Referral\Jobs\ProcessRewardEmail::dispatch($awardInfo);
                    }
                }

                $response['status'] = 200;
                return $response;
            }
        } catch (Exception $e) {
            DB::rollBack();
            if ($clearSessionFrom == 'web') {
                $requestPaymentId = $arr['requestPaymentId'];
                $this->helper->clearSessionWithRedirect('transInfo', $e, "request_payment/accept/$requestPaymentId");
            }
            $response['reqPayment']    = null;
            $response['ex']['message'] = $e->getMessage();
            return $response;
        }
    }
    //common functions - ends
}
