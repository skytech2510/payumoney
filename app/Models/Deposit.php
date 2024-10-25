<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Common;

class Deposit extends Model
{
    protected $table = 'deposits';
    protected static $helper;

    protected $fillable = ['user_id', 'currency_id', 'payment_method_id', 'bank_id', 'file_id', 'uuid', 'charge_percentage', 'charge_fixed', 'amount', 'status'];

    public function __construct()
    {
        self::$helper  = new Common();
    }

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

    //new
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    //new
    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }


    /*
    end of relationships
     */

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getDepositsUsersName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'deposits.user_id')
            ->where(['user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getDepositsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'deposits.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'deposits.user_id')
            ->get();
    }

    /**
     * [Deposits Filtering Results]
     * @param  [null/date] $from     [start date]
     * @param  [null/date] $to       [end date]
     * @param  [string]    $status   [Status]
     * @param  [string]    $pm       [Payment Methods]
     * @param  [string]    $currency [Currency]
     * @param  [null/id]   $user     [User ID]
     * @return [query]     [All Query Results]
     */
    public function getDepositsList($from, $to, $status, $currency, $pm, $user)
    {
        $conditions = [];

        if (empty($from) || empty($to)) {
            $date_range = null;
        } else if (empty($from)) {
            $date_range = null;
        } else if (empty($to)) {
            $date_range = null;
        } else {
            $date_range = 'Available';
        }

        if (!empty($status) && $status != 'all') {
            $conditions['status'] = $status;
        }
        if (!empty($pm) && $pm != 'all') {
            $conditions['payment_method_id'] = $pm;
        }
        if (!empty($currency) && $currency != 'all') {
            $conditions['currency_id'] = $currency;
        }
        if (!empty($user)) {
            $conditions['user_id'] = $user;
        }

        $deposits = $this->with([
            'user:id,first_name,last_name',
            'currency:id,code',
            'payment_method:id,name',
        ])->where($conditions);

        if (!empty($date_range)) {
            $deposits->where(function ($query) use ($from, $to)
            {
                $query->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
            })
            ->select('deposits.*');
        } else {
            $deposits->select('deposits.*');
        }
        return $deposits;
    }

    public static function success($currencyId, $payment_method_id, $user_id, $sessionValue, $status="Success", $type="payment_gateway", $fileId=null, $bankId=null)
    {
        $amount  = (double) $sessionValue['totalAmount'];
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $payment_method_id])->first(['charge_percentage', 'charge_fixed']);
        $p_calc  = $sessionValue['amount'] * (@$feeInfo->charge_percentage / 100);
        $uuid    = $sessionValue['uuid'];

        $deposit                    = new self();
        $deposit->uuid              = $uuid;
        $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
        $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
        $deposit->status            = $status;
        $deposit->user_id           = $user_id;
        $deposit->currency_id       = $currencyId;
        $deposit->payment_method_id = $payment_method_id;
        $deposit->amount            = $present_amount            = ($amount - ($p_calc+@$feeInfo->charge_fixed));
        $deposit->file_id           = $fileId;
        $deposit->bank_id           = $bankId;
        $deposit->save();

        $transaction                           = new Transaction();
        $transaction->user_id                  = $user_id;
        $transaction->currency_id              = $currencyId;
        $transaction->payment_method_id        = $payment_method_id;
        $transaction->transaction_reference_id = $deposit->id;
        $transaction->transaction_type_id      = Deposit;
        $transaction->uuid                     = $uuid;
        $transaction->subtotal                 = $present_amount;
        $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
        $transaction->charge_percentage        = $deposit->charge_percentage;
        $transaction->charge_fixed             = $deposit->charge_fixed;
        $total_fees                            = $deposit->charge_percentage + $deposit->charge_fixed;
        $transaction->total                    = $sessionValue['amount'] + $total_fees;
        $transaction->payment_status           = $status;
        $transaction->status                   = $status;
        $transaction->file_id                  = $fileId;
        $transaction->bank_id                  = $bankId;
        $transaction->save();
        if ($payment_method_id == Stripe || $payment_method_id == Paypal ) {

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user_id, 'currency_id' => $currencyId],
                ['balance' => 0]
            );

            $wallet->balance += $present_amount;
            $wallet->save();
        }

        $data['deposit']     = $deposit;
        $data['transaction'] = $transaction;
        return $data;
    }

    // MobileMoney
    public function mobilemoney()
    {
        return $this->belongsTo(MobileMoney::class, 'mobilemoney_id');
    }
    
    /**
     * updateStatus
     *
     * @param  int $depositId
     * @param  string $status
     * @return object
     */
    public static function updateStatus(int $depositId, string $status): object
    {
        $deposit = Deposit::find($depositId);
        
        if ($deposit) {
            $deposit->status = $status;
            $deposit->save();
        }

        return $deposit;
    }
}
