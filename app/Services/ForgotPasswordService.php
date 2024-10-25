<?php

/**
 * @package ForgotPasswordService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 11-1-2023
 */

namespace App\Services;

use DB, Hash, Password;
use App\Models\User;
use App\Exceptions\Api\V2\ForgotPasswordException;
use App\Services\Mail\PasswordResetMailService;

class ForgotPasswordService
{
    /**
     * send forgot password code
     *
     * @param string $email
     * @return void
     */
    public function resetCode($email)
    {
        $user  = User::where('email', $email)->first();
        if (!$user) {
            throw new ForgotPasswordException(__("Email Address does not match."));
        }

        $reset['email'] = $email;
        $reset['code'] = $user['code']  = otpCode6();
        $reset['created_at'] = date('Y-m-d H:i:s');

        $reset['token'] = base64_encode(Password::createToken(User::where('email', $email)->first()));

        DB::table('password_resets')->where('email', $email)->delete();
        DB::table('password_resets')->insert($reset);

        $reset['resetUrl'] = url('password/resets', $reset['token']);

        $response = (new PasswordResetMailService)->send($user, $reset);

        return $response;
    }

    public function verifyCode($code, $email)
    {
        $reset = DB::table('password_resets')->where(['code' => $code, 'email' => $email ])->first();
        if (!$reset) {
            throw new ForgotPasswordException(__("Verify code not valid. Please try again."));
        }

        return [
            'status'  => true,
            'message' => __('Reset code verified.')
        ];

    }

    public function confirmPassword($code, $email, $password)
    {
        $reset = DB::table('password_resets')->where(['code' => $code, 'email' => $email])->first();
        if (!$reset) {
            throw new ForgotPasswordException(__("Verify code not valid. Please try again."));
        }

        $user = User::where('email', $email)->first();
        if (Hash::check($password, $user->password)) {
            throw new ForgotPasswordException(__("The new password you have entered is the same as your current password. Please choose a different password."));
        }

        $user->password = Hash::make($password);
        $user->save();

        DB::table('password_resets')->where(['code' => $code, 'email' => $email])->delete();
        return [
            'status'  => true,
            'message' => __('Password changed successfully.')
        ];
    }



}
