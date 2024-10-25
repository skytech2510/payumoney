<?php

/**
 * @package ForgotPasswordService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 11-1-2023
 */

namespace App\Services;

use App\Exceptions\Api\V2\RegistrationException;
use App\Models\{
    RoleUser,
    User,
    QrCode,
    VerifyUser,
};
use App\Services\Mail\UserVerificationMailService;
use Exception;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    protected $user;

    /**
     * send forgot password code
     *
     * @param string $email
     * @return void
     */
    public function userRegistration($request)
    {
        try {
            DB::beginTransaction();
            $this->user = new User();
            $user = $this->user->createNewUser($request, 'user');
            RoleUser::insert(['user_id' => $user->id, 'role_id' => $user->role_id, 'user_type' => 'User']);
            $this->user->createUserDetail($user->id);
            $this->user->createUserDefaultWallet($user->id, settings('default_currency'));
            if ('none' != settings('allowed_wallets')) {
                $this->user->createUserAllowedWallets($user->id, settings('allowed_wallets'));
            }
            QrCode::createUserQrCode($user);
            $userEmail          = $user->email;
            $userFormattedPhone = $user->formattedPhone;
            $this->user->processUnregisteredUserTransfers($userEmail, $userFormattedPhone, $user, settings('default_currency'));
            $this->user->processUnregisteredUserRequestPayments($userEmail, $userFormattedPhone, $user, settings('default_currency'));

            $this->emailVerification($user);

            DB::commit();

            return [
                'status'  => true,
                'message' => __('Registration Successful.')
            ];

        } catch (Exception $e) {
           DB::rollBack();
           throw new RegistrationException($e->getMessage());
        }

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

}
