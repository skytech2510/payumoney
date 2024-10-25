<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserDetail;
use App\Http\Helpers\Common;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Users\EmailController;
use App\Services\Mail\TwoFactorVerificationMailService;

class twoFa
{
    protected $email;
    protected $helper;
    public function __construct(EmailController $email)
    {
        $this->email = $email;
        $this->helper = new Common();
    }

    public function handle($request, Closure $next)
    {
        $userDetail = UserDetail::with(['user:id,email'])->where(['user_id' => auth()->user()->id])->first(['id', 'user_id', 'two_step_verification_type', 'two_step_verification']);

        if (preference('two_step_verification') != "disabled" && $userDetail->two_step_verification_type != "disabled") {
            if (!Session::has('2fa')) {
                if ($userDetail->two_step_verification_type == "google_authenticator") {
                    if (!$userDetail->two_step_verification || empty(getBrowserFingerprint($userDetail->user_id, Session::get('browser_fingerprint')))) {
                        $google2fa                             = app('pragmarx.google2fa');
                        $registration_data                     = $request->all();
                        $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();

                        $request->session()->flash('registration_data', $registration_data);

                        $QR_Image = $google2fa->getQRCodeInline(
                            settings('name'),
                            $userDetail->user->email,
                            $registration_data['google2fa_secret']
                        );
                        $data = [
                            'QR_Image' => $QR_Image,
                            'secret'   => $registration_data['google2fa_secret'],
                        ];
                        session()->put('data', $data);
                        return redirect()->route('google2fa');
                    }
                } else {
                    if (empty(getBrowserFingerprint($userDetail->user_id, Session::get('browser_fingerprint')))) {
                        $this->executeTwoFa();
                        $this->helper->one_time_message('success', __('Authentication code sent successfully.'));
                        return redirect('2fa');
                    }
                }
            }
        }
        return $next($request);
    }

    public function executeTwoFa()
    {
        $userDetail = UserDetail::with('user')->where(['user_id' => auth()->id()])->first();
        $userDetail->two_step_verification_code = otpCode6();
        $userDetail->save();

        $user = $userDetail->user;

        if ($userDetail->two_step_verification_type == 'phone') {
            if (!empty($user->formattedPhone)) {
                $message = $userDetail->two_step_verification_code . ' is your ' . settings('name') . ' 2-factor authentication code. ';
                sendSMS($user->formattedPhone, $message);
            }
        } elseif ($userDetail->two_step_verification_type == 'email') {
            $data['optCode'] = $userDetail->two_step_verification_code;
            (new TwoFactorVerificationMailService())->send($user, $data);
        }
    }
}
