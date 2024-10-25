<?php

/**
 * @package AuthService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 30-11-2022
 */

namespace App\Services;

use App\Services\Mail\UserVerificationMailService;
use App\Exceptions\Api\V2\LoginException;
use App\Models\{
    VerifyUser,
    Wallet,
    User,
};
use Auth, DB;
use Exception;

class AuthService
{
    /**
     * Get User email by login method
     *
     * @param string $email
     * @return array
     */
    public function getUserEmailByLoginMethod($email)
    {
        $loginVia = settings('login_via');
        switch ($loginVia) {
            case 'phone_only':
                return $this->checkUserByPhone($email);
                break;

            case 'email_or_phone':
                if (strpos($email, '@') !== false) {
                    return $this->checkUserByEmail($email);
                } else {
                    return $this->checkUserByPhone($email);
                }
                break;

            default:
                return $this->checkUserByEmail($email);
                break;
        }
    }

    /**
     * Check user by phone number
     *
     * @param string $phone
     * @return array
     */
    public function checkUserByPhone($phone)
    {
        $formattedRequest = ltrim($phone, '0');
        $phnUser          = User::where(['phone' => $formattedRequest])->orWhere(['formattedPhone' => $formattedRequest])->first(['email']);

        if (!$phnUser) {
            throw new LoginException(__("Unable to login with provided credentials"));
        }

        return $phnUser->email;

    }

    /**
     * Check user by email address
     *
     * @param string $email
     * @return array
     */
    public function checkUserByEmail($email)
    {
        $user = User::where(['email' => $email])->first(['email']);

        if (!$user) {
            throw new LoginException(__("Invalid email & credentials"));
        }

        return $user->email;

    }

    /**
     * User login
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws LoginException
     */
    public function login($email, $password)
    {
        try {
            DB::beginTransaction();
            $email = $this->getUserEmailByLoginMethod($email);
            $user  = $this->getActiveUser($email);
            $this->emailVerification($user);
            if (!Auth::attempt(['email' => $email, 'password' => $password])) {
                throw new LoginException(__("Invalid email & credentials"));
            }
            $this->userWallet($user);
            DB::commit();
            return Auth::user();
        } catch (Exception $e) {
            DB::rollback();
            throw new LoginException($e->getMessage());
        }

    }

    public function getActiveUser($email)
    {
        $user = User::where(['email' => $email])->first(['id', 'first_name', 'last_name', 'email', 'status']);
        if (!$user) {
            throw new LoginException(__("No user found, please try again"));
        }

        if($user->status == 'Inactive') {
            throw new LoginException(__("Your account is inactivated. Please try again later"));
        }

        return $user;

    }

    public function emailVerification($user)
    {
        if ('Enabled' == preference('verification_mail')) {
            if (0 == optional($user->user_detail)->email_verification) {
                (new VerifyUser())->createVerifyUser($user->id);
                (new UserVerificationMailService())->send($user);
            }
        }
    }

    public function userWallet($user)
    {
        $wallet = Wallet::where(['user_id' => $user->id, 'currency_id' => settings('default_currency')])->first();
        if (empty($wallet)) {
            $wallet    = new Wallet();
            $wallet->createWallet($user->id, settings('default_currency'));
        }
        return $wallet;
    }

}
