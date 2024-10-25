<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyPaymentMethod extends Model
{
	protected $table = 'currency_payment_methods';

    protected $fillable = [
        'currency_id',
        'method_id',
        'activated_for',
        'method_data',
        'processing_time',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    // Mobile Money
    public static function add(array $fields)
    {
        return CurrencyPaymentMethod::create($fields);
    }

    public static function updateCurrencyPaymentMethod(array $conditions, array $updates)
    {
        $currencyPaymentMethod = CurrencyPaymentMethod::where($conditions)->first();

        $currencyPaymentMethod->update($updates);

        return $currencyPaymentMethod->id;
    }

    public static function deleteCurrencyPaymentMethod(array $conditions)
    {
        $currencyPaymentMethod = CurrencyPaymentMethod::where($conditions)->firstOrFail();
        $currencyPaymentMethod->delete();

        return json_encode(['status' => 200, 'message' => __('Successfully deleted')]);
    }
    // Mobile Money
}
