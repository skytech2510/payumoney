<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table    = 'currencies';
    protected $fillable = ['type', 'name', 'symbol', 'code', 'rate', 'logo', 'status', 'default', 'exchange_from', 'allow_address_creation', 'status'];
    public $timestamps  = false;

    /**
     * Relationships - starts
     */
    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'currency_id');
    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class, 'currency_id');
    }

    public function currency_exchange()
    {
        return $this->hasOne(CurrencyExchange::class, 'currency_id');
    }

    public function payment_request()
    {
        return $this->hasOne(RequestPayment::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'currency_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'currency_id');
    }

    public function fees_limit()
    {
        return $this->hasMany(FeesLimit::class, 'currency_id');
    }

    public function currency_payment_method()
    {
        return $this->hasOne(CurrencyPaymentMethod::class, 'currency_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'currency_id');
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'currency_id');
    }

    public function cryptoAssetSetting()
    {
        return $this->hasOne(CryptoAssetSetting::class, 'currency_id');
    }
    
    /**
     * Relationships - ends
     */

    /*
    Get Single Currency
     */
    public function getCurrency($constraints, $selectOptions)
    {
        return $this->where($constraints)->first($selectOptions);
    }

    /*
    Get All Currencies
     */
    public function getAllCurrencies($constraints, $selectOptions)
    {
        return $this->where($constraints)->get($selectOptions);
    }

    public function scopeActive($query)
    {
        return $query->where('status', "Active");
    }

    public function scopeFiat($query)
    {
        return $query->where('type', "fiat");
    }

    public function scopeType($query, $value)
    {
        return $query->where('type', $value);
    }


}
