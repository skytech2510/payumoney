<?php

/**
 * @package WalletService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 05-12-2022
 */

namespace App\Services;

use App\Exceptions\Api\V2\WalletException;
use App\Models\{
    Wallet,
};

class WalletService
{
    /**
     * Change default wallet
     *
     * @param int $userId
     * @param int $defaultWalletId
     * @return void
     */
    public function changeDefaultWallet($userId, $defaultWalletId)
    {
        if (!empty($defaultWalletId) || !is_null($defaultWalletId)) {
            $defaultWallet = Wallet::where(['user_id' => $userId, 'is_default' => 'Yes'])->first(['id', 'is_default']);
            $isWalletExist = Wallet::where(['currency_id' => $defaultWalletId, 'user_id' => $userId])->exists();
            if ($isWalletExist) {
                if ($defaultWallet->id != $defaultWalletId) {
                    $defaultWallet->is_default = 'No';
                    $defaultWallet->save();
                    Wallet::where(['user_id' => $userId, 'currency_id' => $defaultWalletId])->update(['is_default' => 'Yes']);
                }
            }
        }
    }

    /**
     * Get default wallet balance based on user id
     *
     * @param int $userId
     * @return array (defaultWalletBalance)
     * @throws WalletException
     */
    public function defaultWalletBalance($userId)
    {
        $wallet = Wallet::with(['currency:id,code,type'])
                        ->default()
                        ->where(['user_id' => $userId])
                        ->first(['currency_id', 'balance']);
        if (!$wallet) {
            throw new WalletException(__("No :X found.", ["X" => __("Wallet")]));
        }
        $response['defaultWalletBalance'] = moneyFormat(optional($wallet->currency)->code, formatNumber($wallet->balance, $wallet->currency_id));
        return $response;
    }
}
