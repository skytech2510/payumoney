<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Event\LoginActivity;
use Session, Auth, DB;
use App\Models\{DeviceLog,
    EmailTemplate,
    VerifyUser,
    Preference,
    UserDetail,
    Wallet,
    User
};
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\{UserVerificationMailService, 
    twoFactorVerificationMailService
};


class LoginController extends Controller
{
    protected $helper;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function index()
    {
        $data['title'] = 'Login';

        if (Auth::check()) {
            return redirect('/dashboard');
        }

        $data['setting'] = settings('general'); 
        captchaCheck(settings('has_captcha'), 'site_key');

        return view('frontend.auth.login', $data);
    }

    public function authenticate(Request $request)
    {
        captchaCheck(settings('has_captcha'), 'secret_key');

        // validation
        if (($request->has_captcha == 'login' || $request->has_captcha == 'login_and_registration') && $request->login_via == 'email_only') {
            $this->validate($request, [
                'email_only'           => 'required',
                'password'             => 'required',
                'g-recaptcha-response' => 'required|captcha',
            ], [
                'g-recaptcha-response.required' => 'Captcha is required.',
                'g-recaptcha-response.captcha'  => 'Please enter correct captcha.',
            ]);
        } elseif (($request->has_captcha == 'login' || $request->has_captcha == 'login_and_registration') && $request->login_via == 'phone_only') {
            $this->validate($request, [
                'phone_only'           => 'required',
                'password'             => 'required',
                'g-recaptcha-response' => 'required|captcha',
            ], [
                'g-recaptcha-response.required' => 'Captcha is required.',
                'g-recaptcha-response.captcha'  => 'Please enter correct captcha.',
            ]);
        }
        elseif (($request->has_captcha == 'login' || $request->has_captcha == 'login_and_registration') && $request->login_via == 'email_or_phone') {
            $this->validate($request, [
                'email_or_phone'       => 'required',
                'password'             => 'required',
                'g-recaptcha-response' => 'required|captcha',
            ], [
                'g-recaptcha-response.required' => 'Captcha is required.',
                'g-recaptcha-response.captcha'  => 'Please enter correct captcha.',
            ]);
        } else {
            $this->validate($request, [
                'password' => 'required',
            ]);
        }

        if ($request->login_via == 'email_only') {
            $loginValue = $request->input('email_only');
        } elseif ($request->login_via == 'phone_only') {
            $loginValue = $request->input('phone_only');
        } else {
            $loginValue = $request->input('email_or_phone');
        }

        // get login type (email, phone, email_or_phone)
        $loginData = $this->getLoginData($loginValue, $request->login_via);

        if (!empty($loginData['value'])) {
            //Check User Status
            $checkLoggedInUser = User::where(['email' => $loginData['value']])->first(['status']);
            if ($checkLoggedInUser->status == 'Inactive') {
                auth()->logout();
                $this->helper->one_time_message('danger', __('Your account is inactivated. Please try again later!'));
                return redirect('/login');
            }

            // check user verification Status
            $checkUserVerificationStatus = $this->checkUserVerificationStatus($loginData['value']);
            if ($checkUserVerificationStatus == true) {
                auth()->logout();
                return redirect('login')->with('status', __('We sent you an activation code.<br>Check your email and click on the link to verify.'));
            } else {
                //Change request type based on user input
                $request->merge([
                    $loginData['type'] => $loginData['value'],
                ]);
                $type = $loginData['type'];
                $data = $request->only($type, 'password');

                if (Auth::attempt($data)) {
                    $preferences = Preference::getAll()->where('field', '!=', 'dflt_lang');
                    if (!empty($preferences)) {
                        foreach ($preferences as $pref)
                        {
                            $pref_arr[$pref->field] = $pref->value;
                        }
                    }
                    if (!empty($preferences)) {
                        Session::put($pref_arr);
                    }

                    // default_currency
                    if (!empty(settings('default_currency'))) {
                        Session::put('default_currency', settings('default_currency'));
                    }

                    //default_timezone
                    $default_timezone = User::with(['user_detail:id,user_id,timezone'])->where(['id' => auth()->user()->id])->first(['id'])->user_detail->timezone;
                    if (!$default_timezone) {
                        Session::put('dflt_timezone_user', session('dflt_timezone'));
                    } else {
                        Session::put('dflt_timezone_user', $default_timezone);
                    }

                    // default_language
                    if (!empty(settings('default_language'))) {
                        Session::put('default_language', settings('default_language'));
                    }

                    // company_name
                    if (!empty(settings('name'))) {
                        Session::put('name', settings('name'));
                    }

                    // company_logo
                    if (!empty(settings('logo'))) {
                        Session::put('company_logo', settings('logo'));
                    }

                    try {
                        DB::beginTransaction();

                        //check default wallet
                        $chkWallet = Wallet::where(['user_id' => Auth::user()->id, 'currency_id' => settings('default_currency')])->first();
                        if (empty($chkWallet)) {
                            $wallet              = new Wallet();
                            $wallet->user_id     = Auth::user()->id;
                            $wallet->currency_id = settings('default_currency');
                            $wallet->balance     = 0.00;
                            $wallet->is_default  = 'No';
                            $wallet->save();
                        }

                        // Store user login information
                        event(new LoginActivity(auth()->user(), 'User'));

                        // user_detail - adding last_login_at and last_login_ip
                        auth()->user()->user_detail()->update([
                            'last_login_at' => Carbon::now()->toDateTimeString(),
                            'last_login_ip' => $request->getClientIp(),
                        ]);

                        DB::commit();

                        Session::put('browser_fingerprint', $request->browser_fingerprint); //putting browser_fingerprint on session to restrict users accessing dashboard
                        
                        return redirect('dashboard')->with('login', 'success');
                    } catch (Exception $e) {
                        DB::rollBack();
                        $this->helper->one_time_message('danger', $e->getMessage());
                        return redirect('/login');
                    }
                } else {
                    $this->helper->one_time_message('danger', __('Unable to login with provided credentials!'));
                    return redirect('/login');
                }
            }
        } else {
            $this->helper->one_time_message('danger', __('Unable to login with provided credentials!'));
            return redirect('/login');
        }
    }

    protected function getLoginData($loginValue, $loginVia)
    {
        $loginArray = [];
        if ($loginVia == 'phone_only') {
            // phone only
            $loginArray['type'] = 'email';
            $phnUser = User::where(['phone' => ltrim($loginValue, '0')])->orWhere(['formattedPhone' => ltrim($loginValue, '0')])->first(['email']);
            if (!$phnUser) {
                $loginArray['value'] = null;
            } else {
                $loginArray['value'] = $phnUser->email;
            }
        } else if ($loginVia == 'email_or_phone') {
            // email or phone
            $loginArray['type'] = 'email';
            if (strpos($loginValue, '@') !== false) {
                $user = User::where(['email' => $loginValue])->first(['email']);
                if (!$user) {
                    $loginArray['value'] = null;
                } else {
                    $loginArray['value'] = $user->email;
                }
            } else {
                $phoneOrEmailUser = User::where(['phone' => ltrim($loginValue, '0')])->orWhere(['formattedPhone' => ltrim($loginValue, '0')])->first(['email']);
                if (!$phoneOrEmailUser) {
                    $loginArray['value'] = null;
                } else {
                    $loginArray['value'] = $phoneOrEmailUser->email;
                }
            }
        }
        else if ($loginVia == 'email_only') {
            //email only
            $loginArray['type'] = 'email';
            $user = User::where(['email' => $loginValue])->first(['email']);
            if (!$user) {
                $loginArray['value'] = null;
            } else {
                $loginArray['value'] = $user->email;
            }
        }
        return $loginArray;
    }

    //Check User Verification Status
    protected function checkUserVerificationStatus($email)
    {
        $user = User::where(['email' => $email])->first(['id', 'first_name', 'last_name', 'email', 'status']);
        if (preference('verification_mail') == 'Enabled' && $user->user_detail->email_verification == 0) {
            $verifyUser = VerifyUser::where(['user_id' => $user->id])->first(['id']);
            
            if (empty($verifyUser)) {
                $newVerifyUser          = new VerifyUser();
                $newVerifyUser->user_id = $user->id;
                $newVerifyUser->token   = Str::random(40);
                $newVerifyUser->save();
            }

            (new UserVerificationMailService)->send($user);

            return true;
        }

        return false;
    }
}
