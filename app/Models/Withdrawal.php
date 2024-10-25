<?php

namespace App\Models;

use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\WithdrawalDetail;
use App\Services\Mail\Withdrawal\NotifyAdminOnWithdrawMailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class Withdrawal extends Model
{
    protected $table    = 'withdrawals';
    public $timestamps  = true;
    protected $fillable = ['user_id', 'currency_id', 'payment_method_id', 'uuid', 'charge_percentage', 'charge_fixed', 'subtotal', 'amount', 'payment_method_info', 'status'];

    //
    protected $email;
    protected $helper;
    public function __construct()
    {
        $this->email  = new EmailController(); //needed to send email notification
        $this->helper = new Common();
    }
    //

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
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

    public function withdrawal_detail()
    {
        return $this->hasOne(WithdrawalDetail::class, 'withdrawal_id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getWithdrawalsUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'withdrawals.user_id')
            ->where(['user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getWithdrawalsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'withdrawals.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'withdrawals.user_id')
            ->get();
    }

    /**
     * [Withdrawals Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string]    $status [Status]
     * @param  [string]    $pm     [Payment Methods]
     * @param  [null/id]   $user   [User ID]
     * @return [query]     [All Query Results]
     */
    public function getWithdrawalsList($from, $to, $status, $currency, $pm, $user)
    {
        $conditions = [];

        if (empty($from) || empty($to))
        {
            $date_range = null;
        }
        else if (empty($from))
        {
            $date_range = null;
        }
        else if (empty($to))
        {
            $date_range = null;
        }
        else
        {
            $date_range = 'Available';
        }

        if (!empty($status) && $status != 'all')
        {
            $conditions['withdrawals.status'] = $status;
        }
        if (!empty($currency) && $currency != 'all')
        {
            $conditions['withdrawals.currency_id'] = $currency;
        }
        if (!empty($pm) && $pm != 'all')
        {
            $conditions['withdrawals.payment_method_id'] = $pm;
        }
        if (!empty($user))
        {
            $conditions['withdrawals.user_id'] = $user;
        }

        $withdrawals = $this->with([
            'user:id,first_name,last_name',
            'currency:id,code',
            'payment_method:id,name',
        ])->where($conditions);

        if (!empty($date_range))
        {
            $withdrawals->where(function ($query) use ($from, $to)
            {
                $query->whereDate('withdrawals.created_at', '>=', $from)->whereDate('withdrawals.created_at', '<=', $to);
            })
            ->select('withdrawals.*');
        }
        else
        {
            $withdrawals->select('withdrawals.*');
        }
        //
        return $withdrawals;
    }

    //for front-end - WithdrawalController.php - common functions (need to reuse in mobile app too) - starts
    public function createWithdrawal($arr)
    {
        $withdrawal                      = new Withdrawal();
        $withdrawal->user_id             = $arr['user_id'];
        $withdrawal->currency_id         = $arr['currency_id'];
        $withdrawal->payment_method_id   = $arr['payment_method_id'];
        $withdrawal->uuid                = $arr['uuid'];
        $withdrawal->charge_percentage   = $arr['charge_percentage'];
        $withdrawal->charge_fixed        = $arr['charge_fixed'];
        $withdrawal->subtotal            = $arr['subtotal'];
        $withdrawal->amount              = $arr['amount'];
        $withdrawal->payment_method_info = $arr['payment_method_info'];
        $withdrawal->status              = 'Pending';
        $withdrawal->save();

        return $withdrawal;
    }

    public function createWithdrawalDetail($arr)
    {
        $withdrawalDetail                = new WithdrawalDetail();
        $withdrawalDetail->withdrawal_id = $arr['transaction_reference_id'];
        $withdrawalDetail->type          = $arr['payoutSetting']->type;
        
        if ($arr['payment_method_id'] == Paypal) {
            $withdrawalDetail->email = $arr['payoutSetting']->email;
        } elseif ($arr['payment_method_id'] == Bank) {
            $withdrawalDetail->account_name        = $arr['payoutSetting']->account_name;
            $withdrawalDetail->account_number      = $arr['payoutSetting']->account_number;
            $withdrawalDetail->bank_branch_name    = $arr['payoutSetting']->bank_branch_name;
            $withdrawalDetail->bank_branch_city    = $arr['payoutSetting']->bank_branch_city;
            $withdrawalDetail->bank_branch_address = $arr['payoutSetting']->bank_branch_address;
            $withdrawalDetail->country             = $arr['payoutSetting']->country;
            $withdrawalDetail->swift_code          = $arr['payoutSetting']->swift_code;
            $withdrawalDetail->bank_name           = $arr['payoutSetting']->bank_name;
        } elseif ($arr['payment_method_id'] == Crypto) {
            $withdrawalDetail->crypto_address = $arr['payoutSetting']->crypto_address;
        } elseif (config('mobilemoney.is_active') && $arr['payment_method_id'] == (defined('MobileMoney') ? MobileMoney : '')) {
            $withdrawalDetail->mobilemoney_id = $arr['payoutSetting']->mobilemoney_id;
            $withdrawalDetail->mobile_number = $arr['payoutSetting']->mobile_number;
        }
        $withdrawalDetail->save();
    }

    public function createWithdrawalTransaction($arr)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->currency_id              = $arr['currency_id'];
        $transaction->payment_method_id        = $arr['payment_method_id'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->transaction_type_id      = Withdrawal;
        $transaction->subtotal                 = $arr['amount'];
        $transaction->percentage               = $arr['percentage'];
        $transaction->charge_percentage        = $arr['charge_percentage'];
        $transaction->charge_fixed             = $arr['charge_fixed'];
        $transaction->total                    = '-' . ($transaction->subtotal + $transaction->charge_percentage + $transaction->charge_fixed);
        $transaction->status                   = 'Pending';
        $transaction->save();

        return $transaction->id;
    }

    public function updateWallet($arr)
    {
        $arr['wallet']->balance = ($arr['wallet']->balance - $arr['totalAmount']);
        $arr['wallet']->save();
    }

    public function processPayoutMoneyConfirmation($arr = [])
    {
        $response = ['status' => 401];

        try {
            //Backend Validation - Wallet Balance Again Amount Check - Starts here
            $checkWalletBalance = $this->helper->checkWalletBalanceAgainstAmount($arr['totalAmount'], $arr['currency_id'], $arr['user_id']);
            if ($checkWalletBalance == true) {
                $response['withdrawalTransactionId'] = null;
                $response['message'] = __("Sorry, not enough funds to perform the operation.");
                return $response;
                //Backend Validation - Wallet Balance Again Amount Check - Ends here
            } else {
                DB::beginTransaction();

                //Create Withdrawal
                $withdrawal = self::createWithdrawal($arr);

                //Create Withdrawal Detail
                $arr['transaction_reference_id'] = $withdrawal->id;
                self::createWithdrawalDetail($arr);

                //Create Withdrawal Transaction
                $transactionId = self::createWithdrawalTransaction($arr);

                //Update Wallet
                self::updateWallet($arr);

                DB::commit();

                // Notificaton email/SMS
                (new NotifyAdminOnWithdrawMailService)->send($withdrawal, ['type' => 'withdraw', 'medium' => 'email']);

                $response['status'] = 200;
                $response['withdrawalTransactionId'] = $transactionId;

                return $response;
            }
        } catch (Exception $e) {
            DB::rollBack();
            $response['withdrawalTransactionId'] = null;
            $response['message'] = $e->getMessage();
            return $response;
        }
    }
    //for front-end - WithdrawalController.php - common functions (need to reuse in mobile app too) - ends
    
    /**
     * updateStatus
     *
     * @param  int $withdrawalId
     * @param  string $status
     * @return object
     */
    public static function updateStatus(int $withdrawalId, string $status): object
    {
        $withdrawal = Withdrawal::find($withdrawalId);
        
        if ($withdrawal) {
            $withdrawal->status = $status;
            $withdrawal->save();
        }

        return $withdrawal;
    }

    public static function isBankPaymentMethod($withdrawal)
    {
        return optional($withdrawal->payment_method)->name === 'Bank';
    }

    public static function getBankPaymentMethodInfo($withdrawal)
    {
        if (!empty($withdrawal->withdrawal_detail)) {
            $accountNumber = '*****' . substr($withdrawal->withdrawal_detail->account_number, -4);
            return $withdrawal->withdrawal_detail->account_name . ' (' . $accountNumber . ')' . ' ' . $withdrawal->withdrawal_detail->bank_name;
        }
        return '-';
    }

    public static function getDefaultPaymentMethodInfo($withdrawal)
    {
        return !empty($withdrawal->payment_method_info) ? $withdrawal->payment_method_info : '-';
    }
}
