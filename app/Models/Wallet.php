<?php

namespace App\Models;

use App\Models\Currency;
use DB;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table    = 'wallets';
    protected $fillable = ['user_id', 'currency_id', 'balance', 'is_default'];

    // Relationship starts
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function active_currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id')->where('status', 'Active');
    }

    public function currency_exchanges()
    {
        return $this->hasMany(CurrencyExchange::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cryptoAssetApiLogs()
    {
        return $this->hasOne(CryptoAssetApiLog::class, 'object_id');
    }
    // Relationship ends

    public function walletBalance()
    {
        $data = $this->leftJoin('currencies', 'currencies.id', '=', 'wallets.currency_id')
            ->select(DB::raw('SUM(wallets.balance) as amount,wallets.currency_id,currencies.type, currencies.code, currencies.symbol'))
            ->groupBy('wallets.currency_id')
            ->get();

        $array_data = [];
        foreach ($data as $row)
        {
            $array_data[$row->code] = $row->type != 'fiat' ? $row->amount : formatNumber($row->amount);
        }
        return $array_data;
    }

    //Query for Mobile Application - starts
    public function getAvailableBalance($user_id)
    {



        $wallets = $this->with(['currency:id,type,code,type,symbol', 'cryptoAssetApiLogs' => function($query){
            $query->where('object_type', 'wallet_address');
        }])->where(['user_id' => $user_id])
            ->orderBy('balance', 'ASC')
            ->get(['id','currency_id', 'is_default', 'balance'])
            ->map(function ($wallet)
            {
                $walletInfo = [
                    'id' => $wallet->id,
                    'balance' => formatNumber($wallet->balance, $wallet->currency_id),
                    'is_default' => $wallet->is_default,
                    'curr_id' => $wallet->currency_id,
                    'curr_type' => optional($wallet->currency)->type,
                    'curr_code' => optional($wallet->currency)->code,
                    'curr_symbol' => optional($wallet->currency)->symbol,
                    'provider' => (optional($wallet->cryptoAssetApiLogs)->payment_method_id == TatumIo) ? 'tatumio' : ((optional($wallet->cryptoAssetApiLogs)->payment_method_id == BlockIo) ? 'blockio' : ''),
                ];
                return $walletInfo;
            });


        return $wallets;
    }
    //Query for Mobile Application - ends
    public static function createWallet($user_id, $currency_id)
    {
        $wallet              = new self();
        $wallet->user_id     = (int) $user_id;
        $wallet->currency_id = (int) $currency_id;
        $wallet->balance     = 0;
        $wallet->is_default  = 'No';
        $wallet->save();
        return $wallet;
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', 'Yes');
    }

    public function scopeFiat($query)
    {
        return $query->where('type', 'fiat');
    }

    public static function getDefaultWallet()
    {
        $query = self::query();
        return $query->with('currency')->default()->where('user_id', auth()->id())->first();
    }

    /**
     * deductAmountFromWallet
     *
     * @param  int $userId
     * @param  int $currencyId
     * @param  float $amount
     * @return void
     */
    public static function deductAmountFromWallet(int $userId, int $currencyId, float $amount): void
    {
        Wallet::where([
            'user_id' => $userId,
            'currency_id' => $currencyId,
        ])->decrement('balance', $amount);
    }

    /**
     * incrementWalletBalance
     *
     * @param  int $userId
     * @param  int $currencyId
     * @param  float $amount
     * @return void
     */
    public static function incrementWalletBalance(int $userId, int $currencyId, float $amount): void
    {
        Wallet::where([
            'user_id' => $userId,
            'currency_id' => $currencyId,
        ])->increment('balance', $amount);
    }
}
