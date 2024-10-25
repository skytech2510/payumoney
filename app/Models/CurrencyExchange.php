<?php

namespace App\Models;

use App\Http\Controllers\Users\EmailController;
use App\Services\Mail\ExchangeMoneyMailService;
use Illuminate\Database\Eloquent\Model;
use DB, Common, Exception;
use App\Models\{Transaction,
    Currency,
    Wallet
};

class CurrencyExchange extends Model
{
    protected $table    = 'currency_exchanges';
    public $timestamps  = true;
    protected $fillable = [
        'user_id',
        'from_wallet',
        'to_wallet',
        'currency_id',
        'uuid',
        'exchange_rate',
        'amount',
        'type',
        'status',
    ];

    protected $helper;
    protected $email;
    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function fromWallet()
    {
        return $this->belongsTo(Wallet::class, 'from_wallet');
    }

    public function toWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    /**
     * [all exchanges data]
     * @return [void] [query]
     */
    public function getAllExchanges()
    {
        return $this->leftJoin('currencies', 'currencies.id', '=', 'currency_exchanges.currency_id')
            ->select('currency_exchanges.*', 'currencies.code')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getExchangesUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'currency_exchanges.user_id')
            ->where(['currency_exchanges.user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getExchangesUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'currency_exchanges.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'currency_exchanges.user_id')
            ->get();
    }

    public function getExchangesListForCsvExport($from, $to, $status, $currency, $user)
    {

        $conditions = [];

        // status 
        if (! empty($status) && $status != 'all') {
            $conditions['currency_exchanges.status'] = $status;
        }

        // User
        if (! empty($user) && $user != 'all') {
            $conditions['currency_exchanges.user_id'] = $user;
        }
        
        $currencyExchanges = $this->with([
            'fromWallet:id,currency_id',
            'fromWallet.currency',
            'toWallet:id,currency_id',
            'toWallet.currency',
            'user:id,first_name,last_name',
            'currency:id,symbol,code'
        ])->where($conditions);

        // Currency
        if (!empty($currency) && $currency != 'all') {
            $currencyExchanges->whereHas('fromWallet', function ($q) use ($currency) {
                $q->where('currency_id', $currency);
            })->orWhereHas('toWallet', function ($query) use ($currency) {
                $query->where('currency_id', $currency);
            });
        }

        if (!empty($from) && !empty($to)) {
            $currencyExchanges->whereBetween('crypto_exchanges.created_at', [$from, $to]);
        }

        return $currencyExchanges->select('currency_exchanges.*');
    }


    //common functions - starts
    public function createOrUpdateToWallet($arr)
    {
        if (empty($arr['toWallet']))
        {
            //Create To Wallet
            $toWallet              = new Wallet();
            $toWallet->user_id     = $arr['user_id'];
            $toWallet->currency_id = $arr['toWalletCurrencyId'];
            $toWallet->is_default  = 'No';
            $toWallet->balance     = $arr['finalAmount'];
            $toWallet->save();
            $toWallet = $toWallet->id;
        }
        else
        {
            //Update To Wallet
            $arr['toWallet']->balance = ($arr['toWallet']->balance + $arr['finalAmount']);
            $arr['toWallet']->save();
            $toWallet = $arr['toWallet']->id;
        }
        return $toWallet;
    }

    public function createCurrencyExchange($arr, $toWallet)
    {
        $currencyExchange                = new self;
        $currencyExchange->user_id       = $arr['user_id'];
        $currencyExchange->from_wallet   = $arr['fromWallet']->id;
        $currencyExchange->to_wallet     = $toWallet;
        $currencyExchange->currency_id   = $arr['toWalletCurrencyId'];
        $currencyExchange->uuid          = $arr['uuid'];
        $currencyExchange->exchange_rate = $arr['destinationCurrencyExRate'];
        $currencyExchange->amount        = $arr['amount'];
        $currencyExchange->fee           = $arr['fee'];
        $currencyExchange->type          = 'Out';
        $currencyExchange->status        = 'Success';
        $currencyExchange->save();
        return $currencyExchange;
    }

    public function createExchangeFromTransaction($arr, $currencyExchangeId)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->currency_id              = $arr['fromWallet']->currency_id;
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $currencyExchangeId;
        $transaction->transaction_type_id      = Exchange_From;
        $transaction->subtotal                 = $arr['amount'];
        $transaction->percentage               = @$arr['charge_percentage'] ? @$arr['charge_percentage'] : 0;
        $transaction->charge_percentage        = @$arr['charge_percentage'] ? ($arr['formattedChargePercentage']) : 0;
        $transaction->charge_fixed             = @$arr['charge_fixed'] ? @$arr['charge_fixed'] : 0;
        $transaction->total                    = '-' . ($arr['amount'] + $arr['formattedChargePercentage'] + $arr['charge_fixed']);
        $transaction->status                   = 'Success';
        $transaction->save();
    }

    public function createExchangeToTransaction($arr, $currencyExchangeId)
    {
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->currency_id              = $arr['toWalletCurrencyId'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->transaction_reference_id = $currencyExchangeId;
        $transaction->transaction_type_id      = Exchange_To;
        $transaction->subtotal                 = $arr['finalAmount'];
        $transaction->total                    = $arr['finalAmount'];
        $transaction->status                   = 'Success';
        $transaction->save();
    }

    public function updateFromWallet($arr)
    {
        $arr['fromWallet']->balance = ($arr['fromWallet']->balance - ($arr['amount'] + $arr['formattedChargePercentage'] + $arr['charge_fixed']));
        $arr['fromWallet']->save();
    }

    public function processExchangeMoneyConfirmation($arr)
    {
        $response = ['status' => 401];

        try {
            //Backend Validation - Wallet Balance Again Amount Check - Starts here
            $checkWalletBalance = $this->helper->checkWalletBalanceAgainstAmount($arr['amount'] + $arr['fee'], $arr['fromWallet']->currency_id, $arr['user_id']);
            if ($checkWalletBalance == true) {
                $response['exchangeCurrencyId'] = null;
                $response['message'] = __("Sorry, not enough funds to perform the operation.");
                return $response;
                //Backend Validation - Wallet Balance Again Amount Check - Ends here
            } else {
                DB::beginTransaction();

                //Create or Update To Wallet
                $toWallet = self::createOrUpdateToWallet($arr);

                //Create Currency Exchange
                $currencyExchange = self::createCurrencyExchange($arr, $toWallet);

                //create Exchange From Transaction
                self::createExchangeFromTransaction($arr, $currencyExchange->id);

                //create Exchange To Transaction
                self::createExchangeToTransaction($arr, $currencyExchange->id);

                //Update From Wallet
                self::updateFromWallet($arr);

                DB::commit();

                // Notification Email/SMS
                (new ExchangeMoneyMailService)->send($currencyExchange, ['type' => 'exchange', 'medium' => 'email']);
                
                $response['status'] = 200;
                $response['exchangeCurrencyId'] = $currencyExchange->id;
                
                return $response;
            }
        } catch (Exception $e) {
            DB::rollBack();
            $response['exchangeCurrencyId'] = null;
            $response['message'] = $e->getMessage();
            return $response;
        }
    }
}
