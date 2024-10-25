<?php

namespace App\Http\Controllers\Auth;

use App\Services\Mail\UserVerificationMailService;
use App\Http\Controllers\Users\EmailController;
use DB, Validator, Auth, Exception;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use Illuminate\Support\Str;
use App\Models\{RoleUser,
    CryptoProvider,
    VerifyUser,
    QrCode,
    User,
    Role
};

class RegisterController extends Controller
{
    protected $helper;
    protected $email;
    protected $user;
    protected $referralIdentifier;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
        $this->user   = new User();
        if (module('Referral') && settings('referral_enabled') == 'Yes') {
            $this->referralIdentifier = md5(getBrowser($_SERVER['HTTP_USER_AGENT'])['platform'] . $_SERVER['REMOTE_ADDR']);
        }
    }

    public function create()
    {
        $data = [
            'title' => 'Register'
        ];

        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('frontend.auth.register', $data);
    }

    public function storePersonalInfo(Request $request)
    {
        $this->validate($request, [
            'first_name'            => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'last_name'             => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        $data = [
            'formInfo' => $request->all(),
            'enabledCaptcha' => settings('has_captcha'),
            'checkMerchantRole' => Role::where([
                'user_type' => 'User', 
                'customer_type' => 'merchant', 
                'is_default' => 'Yes'
            ])->first(['id']),
            'checkUserRole' => Role::where([
                'user_type' => 'User', 
                'customer_type' => 'user', 
                'is_default' => 'Yes'
            ])->first(['id'])
        ];
        captchaCheck(settings('has_captcha'), 'site_key');

        return view('frontend.auth.userType', $data);

    }

    public function store(Request $request)
    {
        captchaCheck(settings('has_captcha'), 'secret_key');

        if ($request->isMethod('post')) {
            if ($request->has_captcha == 'registration' || $request->has_captcha == 'login_and_registration') {
                $rules = array(
                    'first_name'            => ['required', 'max:30', 'regex:/^[a-zA-Z\s]*$/'],
                    'last_name'             => ['required', 'max:30', 'regex:/^[a-zA-Z\s]*$/'],
                    'email'                 => ['required', 'email', 'unique:users,email'],
                    'password'              => ['required', 'confirmed'],
                    'password_confirmation' => ['required'],
                    'g-recaptcha-response'  => ['required', 'captcha'],
                );
                
                $fieldNames = array(
                    'first_name'            => 'First Name',
                    'last_name'             => 'Last Name',
                    'email'                 => 'Email',
                    'password'              => 'Password',
                    'password_confirmation' => 'Confirm Password',
                    'g-recaptcha-response'  => 'Captcha'
                );

            } else {
                $rules = array(
                    'first_name'            => ['required', 'max:30', 'regex:/^[a-zA-Z\s]*$/'],
                    'last_name'             => ['required', 'max:30', 'regex:/^[a-zA-Z\s]*$/'],
                    'email'                 => ['required', 'email', 'unique:users,email'],
                    'password'              => ['required', 'confirmed'],
                    'password_confirmation' => ['required'],
                );
                $fieldNames = array(
                    'first_name'            => 'First Name',
                    'last_name'             => 'Last Name',
                    'email'                 => 'Email',
                    'password'              => 'Password',
                    'password_confirmation' => 'Confirm Password',
                );
            }

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);
            if ($validator->fails()) {
                return redirect('register')->withErrors($validator)->withInput();
            } else {
                try {
                    DB::beginTransaction();

                    // Create user
                    $user = $this->user->createNewUser($request, 'user');

                    // Assign user type and role to new user
                    RoleUser::insert(['user_id' => $user->id, 'role_id' => $user->role_id, 'user_type' => 'User']);

                    // Create user detail
                    $this->user->createUserDetail($user->id);

                    // Create user's default wallet
                    $this->user->createUserDefaultWallet($user->id, settings('default_currency'));

                    // Create wallets that are allowed by admin
                    if (settings('allowed_wallets') != 'none') {
                        $this->user->createUserAllowedWallets($user->id, settings('allowed_wallets'));
                    }

                    if (module('Referral') && settings('referral_enabled') == 'Yes') {
                        // Save referral code for new User
                        (new \Modules\Referral\Entities\ReferralCode())->saveUserReferralCode($user->id);

                        // Check Cache & Save to Referrals - starts
                        (new \Modules\Referral\Entities\Referral())->saveReferralWithCacheCheck($user->id);

                        // signup referral award
                        (new \Modules\Referral\Entities\ReferralAward())->saveSignupReferralAward($user->id);
                    }

                    if (isActive('BlockIo') && CryptoProvider::getStatus('BlockIo') == 'Active') {
                        $generateUserCryptoWalletAddress = $this->user->generateUserBlockIoWalletAddress($user);
                        if ($generateUserCryptoWalletAddress['status'] == 401) {
                            DB::rollBack();
                            $this->helper->one_time_message('error', $generateUserCryptoWalletAddress['message']);
                            return redirect('/login');
                        }
                    }

                    if (isActive('TatumIo') && CryptoProvider::getStatus('TatumIo') == 'Active') {
                        $generateUserCryptoWalletAddress = $this->user->generateUserTatumIoWalletAddress($user);
                        if ($generateUserCryptoWalletAddress['status'] == 401) {
                            DB::rollBack();
                            $this->helper->one_time_message('error', $generateUserCryptoWalletAddress['message']);
                            return redirect('/login');
                        }
                    }
                    
                    // QR Code
                    QrCode::createUserQrCode($user);

                    $userEmail          = $user->email;
                    $userFormattedPhone = $user->formattedPhone;

                    // Process Registered User Transfers
                    $this->user->processUnregisteredUserTransfers($userEmail, $userFormattedPhone, $user, settings('default_currency'));

                    // Process Registered User Request Payments
                    $this->user->processUnregisteredUserRequestPayments($userEmail, $userFormattedPhone, $user, settings('default_currency'));

                    // Email verification
                    if (!$user->user_detail->email_verification) {
                        if (preference('verification_mail') == "Enabled") {
                            VerifyUser::generateVerificationToken($user->id);
                            try {
                                (new UserVerificationMailService)->send($user);

                                DB::commit();
                                $this->helper->one_time_message('success', __('We sent you an activation code. Check your email and click on the link to verify.'));
                                return redirect('/login');
                            } catch (Exception $e) {
                                DB::rollBack();
                                $this->helper->one_time_message('error', $e->getMessage());
                                return redirect('/login');
                            }
                        }
                    }
                    //email_verification - ends
                    DB::commit();
                    $this->helper->one_time_message('success', __('Registration Successful!'));
                    return redirect('/login');
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect('/login');
                }
            }
        }
    }

    public function verifyUser($token)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();
        if (isset($verifyUser))
        {
            if (!$verifyUser->user->user_detail->email_verification)
            {
                $verifyUser->user->user_detail->email_verification = 1;
                $verifyUser->user->user_detail->save();
                $status = __("Your account is verified. You can now login.");
            }
            else
            {
                $status = __("Your account is already verified. You can now login.");
            }
        }
        else
        {
            return redirect('/login')->with('warning', __("Sorry your email cannot be identified."));
        }
        return redirect('/login')->with('status', $status);
    }

    public function checkUserRegistrationEmail(Request $request)
    {
        $email = User::where(['email' => $request->email])->exists();
        if ($email)
        {
            $data['status'] = true;
            $data['fail']   = __('The email has already been taken!');
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "Email Available!";
        }
        return json_encode($data);
    }

    public function registerDuplicatePhoneNumberCheck(Request $request)
    {
        if (isset($request->carrierCode))
        {
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone), 'carrierCode' => $request->carrierCode])->first(['phone', 'carrierCode']);
        }
        else
        {
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone)])->first(['phone', 'carrierCode']);
        }

        if (!empty($user->phone) && !empty($user->carrierCode))
        {
            $data['status'] = true;
            $data['fail']   = "The phone number has already been taken!";
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "The phone number is Available!";
        }
        return json_encode($data);
    }
}
