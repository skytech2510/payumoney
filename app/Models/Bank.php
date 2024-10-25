<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table    = 'banks';
    protected $fillable = ['user_id', 'admin_id', 'currency_id', 'country_id', 'file_id', 'bank_name', 'bank_branch_name', 'bank_branch_city', 'bank_branch_address',
    'account_name', 'account_number', 'swift_code', 'default'];

    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'bank_id');
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'bank_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bank_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    //pm - 1.9
    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
