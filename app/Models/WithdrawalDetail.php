<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalDetail extends Model
{
    protected $table = 'withdrawal_details';

    protected $fillable = ['withdrawal_id', 'type', 'email', 'account_name', 'account_number', 'bank_branch_name', 'bank_branch_city', 'bank_branch_address', 'country', 'swift_code','bank_name'];


    public function payment_method()
    {
        return $this->hasOne(PaymentMethod::class, 'id', 'type');
    }


    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class, 'withdrawal_id');
    }

    // MobileMoney
    public function mobilemoney()
    {
        return $this->belongsTo(MobileMoney::class, 'mobilemoney_id');
    }
}
