<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $table = 'merchants';

    protected $fillable = [
        'user_id',
        'merchant_group_id',
        'merchant_uuid',
        'business_name',
        'site_url',
        'type',
        'note',
        'logo',
        'fee',
        'status',
    ];

    public function transactions()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    public function transaction_merchants()
    {
        return $this->hasMany(Transaction::class, 'merchant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //pm_v2.3
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function merchant_payment()
    {
        return $this->hasMany(MerchantPayment::class, 'merchant_id');
    }

    public function merchant_group()
    {
        return $this->belongsTo(MerchantGroup::class, 'merchant_group_id');
    }

    public function appInfo()
    {
        return $this->hasOne(MerchantApp::class, 'merchant_id', 'id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getMerchantsUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'merchants.user_id')
            ->where(['merchants.user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getMerchantsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'merchants.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'merchants.user_id')
            ->get();
    }

    /**
     * [Merchants Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string]    $status [Status]
     * @param  [null/id]   $user   [User ID]
     * @return [void]      [All Query Results]
     */
    public function getMerchantsList($from, $to, $status, $user)
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
            $conditions['merchants.status'] = $status;
        }
        if (!empty($user))
        {
            $conditions['merchants.user_id'] = $user;
        }
        //
        $merchants = $this->with(['merchant_group:id,name','user:id,first_name,last_name'])->where($conditions);
        if (!empty($date_range))
        {
            $merchants->where(function ($query) use ($from, $to)
            {
                $query->whereDate('merchants.created_at', '>=', $from)->whereDate('merchants.created_at', '<=', $to);
            })
            ->select('merchants.*');
        }
        else
        {
            $merchants->select('merchants.*');
        }
        //
        return $merchants;
    }
}
