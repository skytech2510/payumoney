<?php

/**
 * @package UserProfileService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 05-12-2022
 */

namespace App\Services;

use App\Exceptions\Api\V2\UserProfileException;
use App\Http\Helpers\Common;
use App\Models\{
    Country,
    Transaction,
    UserDetail,
    Wallet,
    User,
};
use Hash;

class UserProfileService
{
    /**
     * Get User Profile Summary
     *
     * @param int $userId
     * @return array (user, last_30_days_transaction, total_wallets, defaultWallet)
     */
    public function getProfileSummary($userId)
    {
        $user = $this->getUser($userId);
        $last_30_days_transaction = $this->UserTransactionCount($user->id, 30);
        $wallets = Wallet::where(['user_id' => $userId]);
        $user->total_wallets = $wallets->count();
        $defaultWallet = $wallets->default()
            ->with(['currency:id,code'])
            ->first(['currency_id']);

        $user->last_30_days_transaction =  $last_30_days_transaction;
        $user->defaultWallet =  $defaultWallet;

        return $user;

    }

    /**
     * Get User Profile details
     *
     * @param int $userId
     * @return array (user, wallets, timezones, countries)
     */
    public function getProfileDetails($userId)
    {
        $user = $this->getUser($userId);

        $wallets = (new Common)->getUserWallets(['currency:id,code,type'],
                        ['user_id' => $userId],
                        ['id', 'currency_id', 'is_default']
                    );
        $countries = Country::get(['id', 'name', 'short_name']);
        $user->wallets = $wallets;
        $user->total_wallets = $wallets->count();
        $user->last_30_days_transaction = $this->UserTransactionCount($user->id, 30);
        $user->timezones = phpDefaultTimeZones();
        $user->countries =  $countries;
        return $user;
    }

    /**
     * Update user information
     *
     * @param int $userId
     * @param array $userInfo
     * @param array $userDetailInfo
     * @return void
     */
    public function updateProfileInformation($userId, $userInfo, $userDetailInfo)
    {
        if (!empty($userInfo)) {
            User::where('id', $userId)->update($userInfo);
        }
        if (!empty($userDetailInfo)) {
            UserDetail::where('user_id', $userId)->update($userDetailInfo);
        }
    }

    /**
     * Upload user profile image
     *
     * @param int $userId
     * @param $image
     * @return void
     * @throws UserProfileException
     */
    public function uploadImage($userId, $image)
    {
        $user      = User::find($userId, ['id', 'picture']);
        $extension = strtolower($image->getClientOriginalExtension());
        if (!in_array($extension, getFileExtensions(3))) {
            $fileExtensions = implode(", ", getFileExtensions(3));
            throw new UserProfileException(__('The file must be an image (:x)', ['x' => $fileExtensions]));
        }
        $response = uploadImage($image, getDirectory('profile'), '100*100', $user->picture, '70*70');
        if (true === $response['status']) {
            $user->picture = $response['file_name'];
            $user->save();
        }
        return $response;
    }

    /**
     * Change User Password
     *
     * @param int $userId
     * @param string $oldPassword
     * @param string $password
     * @return array $response (message)
     */
    public function changePassword($userId, $oldPassword, $password)
    {
        $user = User::where(['id' => $userId])->first(['id', 'password']);
        if (!$user) {
            throw new UserProfileException(__("User not found"));
        }
        if (!Hash::check($oldPassword, $user->password)) {
            throw new UserProfileException(__("Old Password is Wrong!"));
        }
        $user->password = Hash::make($password);
        $user->save();
        $response['message'] = __('Password Updated successfully!');
        return $response;
    }

    /**
     * Change User Phone
     *
     * @param int $userId
     * @param string $userPhoneInfo
     * @return void
     * @throws UserProfileException
     */
    public function phoneUpdate($userId, $userPhoneInfo)
    {
        $user = User::where(['id' => $userId])->first(['id', 'password']);
        if (!$user) {
            throw new UserProfileException(__("User not found"));
        }

        if (isset($userPhoneInfo['phone'])) {
            $formattedPhone = str_replace('+' . $userPhoneInfo['carrierCode'], "", $userPhoneInfo['phone']);
            $user->phone     = preg_replace("/[\s-]+/", "", $formattedPhone);
            $user->defaultCountry = $userPhoneInfo['defaultCountry'];
            $user->carrierCode    = $userPhoneInfo['carrierCode'];
            $user->formattedPhone = '+' . $userPhoneInfo['carrierCode'] . ltrim($userPhoneInfo['phone'], '0');
            $user->save();
        } else {
            $user->phone  = null;
            $user->defaultCountry = null;
            $user->carrierCode    = null;
            $user->formattedPhone = null;
            $user->save();
        }
    }

    public function UserTransactionCount($userId, $day)
    {
       return Transaction::where('user_id', $userId)
            ->where('created_at', '>', now()->subDays($day)->endOfDay())
            ->count();
    }

    public function getUser($userId)
    {
        $user = User::with('user_detail', 'user_detail.country:id')
                    ->where(['id' => $userId])->first();
        if (!$user) {
            throw new UserProfileException(__("User not found"));
        }
        return $user;

    }

}
