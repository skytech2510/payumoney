<?php

namespace App\Models;

use App\Models\Deposit;
use App\Models\Fee;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table    = 'payment_methods';
    protected $fillable = ['name', 'status'];
    public $timestamps  = false;

    public function fee()
    {
        return $this->hasOne(Fee::class);
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'payment_method_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function currency_payment_method()
    {
        return $this->hasOne(CurrencyPaymentMethod::class, 'method_id');
    }

    public function fees_limit()
    {
        return $this->hasOne(FeesLimit::class, 'payment_method_id');
    }
    /**
     * To get payment gateways credentials by specific id and currency
     * @param [int] $id 
     * @param [int] $currencyId 
     * @return void
    */
    public function getCredetial($id, $currencyId)
    {
        $PaymentMethod         = PaymentMethod::find($id, ['id', 'name']);
        $method                = ucfirst(strtolower($PaymentMethod->name));
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $methodId])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
        $methodData            = json_decode($currencyPaymentMethod->method_data);
        return $methodData;
    }

    public function scopeActive($query)
    {
        return $query->where('status', "Active");
    }

}
