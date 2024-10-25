<?php

namespace App\Http\Controllers\Users;

use Artisan, Validator, Session, Hash, Auth, DB, Exception, Common;
use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Auth\LoginController;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{EmailTemplate,
    DocumentVerification,
    CryptoProvider,
    Transaction,
    UserDetail,
    DeviceLog,
    Country,
    Wallet,
    QrCode,
    User,
    File
};

use App\Services\Mail\TwoFactorVerificationMailService;

class CustomerController extends Controller
{
    protected $helper;
    protected $twoFa;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->twoFa = new LoginController();
        $this->email = new EmailController();
    }

    public function checkUserStatus()
    {
        $data['message'] = __('You are suspended to do any kind of transaction!');
        return view('user.common.check_status', $data);
    }

    public function checkRequestCreatorSuspendedStatus()
    {
        $data['message'] = __('Request Creator is suspended!');
        return view('user.common.check_status', $data);
    }

    public function checkRequestCreatorInactiveStatus()
    {
        $data['message'] = __('Request Creator is inactive!');
        return view('user.common.check_inactive_status', $data);
    }

    public function view2fa()
    {
        return view('user.setting.phone2fa-verify');
    }

    public function verify2fa(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'two_step_verification_code' => 'required|numeric',
        ]);

        if ($validation->passes())
        {
            if ($request->two_step_verification_code == auth()->user()->user_detail->two_step_verification_code)
            {
                Session::put('2fa', '2fa');
                $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first();

                if ($request->remember_me == "true")
                {
                    $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();
                    if (empty($checkDeviceLog))
                    {
                        $deviceLog                      = new DeviceLog();
                        $deviceLog->user_id             = auth()->user()->id;
                        $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                        $deviceLog->browser_agent       = $request->header('user-agent');
                        $deviceLog->ip                  = $request->ip();
                        $deviceLog->save();
                    }
                }

                if ($userDetail->two_step_verification == 0)
                {
                    $userDetail->two_step_verification = 1;
                }
                $userDetail->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('User Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 404,
                'message' => $validation->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    //Google2fa after login- start
    public function viewGoogle2fa()
    {
        $data['data'] = session('google2fa');
        return view('user.setting.google2fa-verify', $data);
    }

    public function verifyGoogle2fa(Request $request)
    {
        $user                   = User::find(auth()->user()->id);
        $user->google2fa_secret = $request->google2fa_secret;
        $user->save();

        return response()->json([
            'status' => true,
        ]);
    }

    public function verifyGoogle2faOtp(Request $request)
    {
        if ($request->remember_otp == "true")
        {
            $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();
            if (empty($checkDeviceLog))
            {
                $deviceLog                      = new DeviceLog();
                $deviceLog->user_id             = auth()->user()->id;
                $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                $deviceLog->browser_agent       = $request->header('user-agent');
                $deviceLog->ip                  = $request->ip();
                $deviceLog->save();
            }
        }

        $user = Auth::user();
        $userDetail                             = UserDetail::where(['user_id' => auth()->user()->id])->first();
        $userDetail->two_step_verification_type = $request->two_step_verification_type;

        $google2fa = app('pragmarx.google2fa');
        $secret = $request->one_time_password;

        $valid = $google2fa->verifyKey($user->google2fa_secret, $secret);


        if ($valid) {
            $userDetail->two_step_verification = 1;
            $userDetail->save();
            Session::forget('google2fa');
            Session::put('2fa', '2fa');
            return response()->json([
                'status'  => true,
                'message' => __('User Verified Successfully!'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => __('Verification Code Does Not Match!'),
            ]);
        }
    }
    //Google2fa after login - end

    public function dashboard()
    {
        $data['menu']  = 'dashboard';
        $data['title'] = 'Dashboard';

        $transaction          = new Transaction();
        $data['transactions'] = $transaction->dashboardTransactionList();
        $data['lastTransaction'] = Transaction::with(['transaction_type:id,name','currency:id,code,type,symbol'])->where('user_id', auth()->user()->id)->latest()->first();
        $data['wallets'] = Wallet::with('currency:id,type,logo,code,status,symbol')->where(['user_id' => Auth::user()->id])->orderBy('balance', 'ASC')->get(['id', 'currency_id', 'balance', 'is_default']);
        $data['user'] = User::where(['id' => Auth::user()->id])->first();
        $data['qrCode'] = QrCode::getQrCode(auth()->id(), 'user', ['qr_image']);

        return view('user.dashboard', $data);
    }

    public function profile()
    {
        $data['menu'] = 'profile';
        $data['sub_menu'] = 'profile';
        $userId = auth()->id();
        $data['user'] = User::find($userId);
        $data['timezones'] = phpDefaultTimeZones();
        $data['is_sms_env_enabled'] = checkAppSmsEnvironment();
        $data['checkPhoneVerification'] = preference('phone_verification');
        $data['countries'] = Country::orderBy('name', 'asc')->get();
        $data['two_step_verification'] = preference('two_step_verification');

        $data['wallets'] = Wallet::whereHas('currency', function ($q) {
            $q->where(['type' => 'fiat']);
        })->with(['currency:id,code'])->where(['user_id' => $userId])->orderBy('balance', 'ASC')->get(['id', 'currency_id', 'is_default']);

        $data['qrCode'] = QrCode::getQrCode($userId, 'user', ['qr_image']);
        $data['defaultWallet'] = Wallet::getDefaultWallet();

        return view('user.profile.index', $data);
    }

    public function profileTwoFa()
    {
        $data['menu'] = 'profile';
        $data['sub_menu'] = 'profile';
        $data['user'] = User::find(Auth::user()->id);
        $data['two_step_verification'] = preference('two_step_verification');
        $data['is_demo'] = checkDemoEnvironment();

        if (!empty(auth()->user()->device_log->browser_fingerprint)) {
            $data['checkDeviceLog'] = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => auth()->user()->device_log->browser_fingerprint])->first(['browser_fingerprint']);
        }

        return view('user.setting.two-fa', $data);
    }

    public function disabledTwoFa(Request $request)
    {
        if ($request->ajax())
        {
            $userDetail                             = UserDetail::where(['user_id' => auth()->user()->id])->first();
            $userDetail->two_step_verification_type = $request->two_step_verification_type;
            $userDetail->save();

            return response()->json([
                'status' => true,
                'twoFaVerificationTypeForResponse' => $request->two_step_verification_type,
            ]);
        }
    }

    public function ajaxTwoFa(Request $request)
    {
        $user = auth()->user();
        if ($user->user_detail->two_step_verification_type !== $request->two_step_verification_type) {
            $data['optCode'] = otpCode6();

            $user->user_detail->update([
                'two_step_verification_code' => $data['optCode'],
            ]);

            if ($request->two_step_verification_type == 'phone') {
                if (checkAppSmsEnvironment() == true) {
                    if (!empty($user->formattedPhone)) {
                        $message = $data['optCode'] . ' is your ' . settings('name') . ' 2-factor verification code. ';
                        sendSMS($user->formattedPhone, $message);
                    }
                }
            } elseif ($request->two_step_verification_type == 'email') {
                (new TwoFactorVerificationMailService)->send($user, $data);
            }

            if ($request->two_step_verification_type == 'email') {
                return response()->json([
                    'status'                           => true,
                    'twoFaVerificationTypeForResponse' => 'email',
                    'twoFa_type'                       => $user->email,
                ]);
            } else {
                return response()->json([
                    'status'                           => true,
                    'twoFaVerificationTypeForResponse' => 'phone',
                    'twoFa_type'                       => str_pad(substr($user->phone, -2), strlen($user->phone), '*', STR_PAD_LEFT),
                ]);
            }
        } else {
            return response()->json([
                'status'                     => false,
                'two_step_verification_type' => $user->user_detail->two_step_verification_type,
            ]);
        }
    }

    //Google2fa in user profile- start
    public function google2fa(Request $request)
    {
        if (auth()->user()->user_detail->two_step_verification_type !== $request->two_step_verification_type)
        {
            $google2fa                             = app('pragmarx.google2fa');
            $registration_data                     = $request->all();
            $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();

            $request->session()->flash('registration_data', $registration_data);

            $QR_Image = $google2fa->getQRCodeInline(
                settings('name'),
                auth()->user()->email,
                $registration_data['google2fa_secret']
            );
            return response()->json([
                'status'                           => true,
                'secret'                           => $registration_data['google2fa_secret'],
                'QR_Image'                         => $QR_Image,
                'twoFaVerificationTypeForResponse' => 'google_authenticator',
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'two_step_verification_type' => auth()->user()->user_detail->two_step_verification_type,
            ]);
        }
    }

    public function completeGoogle2faVerification(Request $request)
    {
        $user                   = User::find(auth()->user()->id);
        $user->google2fa_secret = $request->google2fa_secret;
        $user->save();

        return response()->json([
            'status' => true,
        ]);
    }

    public function google2faOtpVerification(Request $request)
    {
        if ($request->remember_otp == "true")
        {
            $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();

            if (empty($checkDeviceLog))
            {
                $deviceLog                      = new DeviceLog();
                $deviceLog->user_id             = auth()->user()->id;
                $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                $deviceLog->browser_agent       = $request->header('user-agent');
                $deviceLog->ip                  = $request->ip();
                $deviceLog->save();
            }
        }

        $user = Auth::user();
        $userDetail                             = UserDetail::where(['user_id' => auth()->user()->id])->first();
        $userDetail->two_step_verification_type = $request->two_step_verification_type;

        $google2fa = app('pragmarx.google2fa');
        $secret = $request->one_time_password;

        $valid = $google2fa->verifyKey($user->google2fa_secret, $secret);

        if ($valid) {
            $userDetail->two_step_verification = 1;
            $userDetail->save();
            return response()->json([
                'status'  => true,
                'message' => __('User Verified Successfully!'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => __('Verification Code Does Not Match!'),
            ]);
        }
    }
    //Google2fa in user profile- end

    public function ajaxTwoFaSettingsVerify(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'two_step_verification_code' => 'required|numeric',
        ]);

        if ($validation->passes())
        {
            if ($request->two_step_verification_code == auth()->user()->user_detail->two_step_verification_code)
            {

                $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first();

                if ($request->remember_me == "true")
                {
                    $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();
                    if (empty($checkDeviceLog))
                    {
                        $deviceLog                      = new DeviceLog();
                        $deviceLog->user_id             = auth()->user()->id;
                        $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                        $deviceLog->browser_agent       = $request->header('user-agent');
                        $deviceLog->ip                  = $request->ip();
                        $deviceLog->save();
                    }
                }

                if ($userDetail->two_step_verification == 0)
                {
                    $userDetail->two_step_verification = 1;
                }
                $userDetail->two_step_verification_type = $request->twoFaVerificationType;
                $userDetail->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('User Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 404,
                'message' => $validation->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    public function checkPhoneFor2fa(Request $request)
    {
        if (!empty(auth()->user()->carrierCode) && !empty(auth()->user()->phone))
        {
            return response()->json([
                'status'  => true,
                'message' => __('Phone number is set.'),
            ]);
        }
        else
        {
            return response()->json([
                'status'  => false,
                'message' => __('Please set your phone number first!'),
            ]);
        }
    }

    public function updateProfilePassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password'     => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        $user = User::where(['id' => Auth::user()->id])->first();

        if (Hash::check($request->old_password, $user->password))
        {
            $user->password = Hash::make($request->password);
            $user->save();

            $this->helper->one_time_message('success', __('Password Updated successfully!'));
            return redirect()->intended("profile");
        }
        else
        {
            $this->helper->one_time_message('error', __('Old Password is Wrong!'));
            return redirect()->intended("profile");
        }
    }

    public function profileImage(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'file' => new CheckValidFile(getFileExtensions(3), true),
            ]);

        if ($validator->fails()) {
            return array(
                'fail'   => true,
                'errors' => $validator->errors(),
            );
        }
        $filename = '';
        $user     = User::find(Auth::user()->id);

        $picture = $request->file;
        if (isset($picture)) {
            $ext      = strtolower($picture->getClientOriginalExtension());
            $filename = time() . '.' . $ext;

            $dir1 = public_path('/uploads/user-profile/' . $filename);
            $dir2 = public_path('/uploads/user-profile/thumb/' . $filename);

            if (!empty(Auth::user()->picture)) {
                if (file_exists($dir1)) {
                    unlink($dir1);
                }

                if (file_exists($dir2)) {
                    unlink($dir2);
                }
            }

            if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'bmp') {
                $img = Image::make($picture->getRealPath());

                $img->resize(100, 100)->save($dir1);

                $img->resize(70, 70)->save($dir2);

                $user->picture = $filename;
            } else {
                return array(
                    'fail'   => true,
                    'errors' => 'Invalid Image Format!',
                );
            }
        }
        $user->save();

        if($filename != '') {
            $this->helper->one_time_message('success', __('User Image Updated Successfully'));
        }
        return $filename;
    }

    public function generatePhoneVerificationCode(Request $request)
    {
        $six_digit_random_number = otpCode6();

        $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

        if (empty($userDetail->phone_verification_code)) {
            UserDetail::where([
                'user_id' => auth()->user()->id,
            ])->update(['phone_verification_code' => $six_digit_random_number]);
        } else {
            UserDetail::where([
                'user_id'                 => auth()->user()->id,
                'phone_verification_code' => $userDetail->phone_verification_code,
            ])->update(['phone_verification_code' => $six_digit_random_number]);
        }

        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);

        //SMS
        if (!empty($request->phone)) {
            if (!empty($request->carrierCode) && !empty($request->phone)) {
                $message = $six_digit_random_number . ' is your ' . settings('name') . ' verification code. ';

                $data = [];
                if (checkAppSmsEnvironment() == true && preference('phone_verification') == "Enabled") {
                    if (!empty(getSmsConfigDetails()) && getSmsConfigDetails()->status == 'Active') {
                        $data['status']  = true;
                        $data['message'] = 'Yes';
                        sendSMS($request->carrierCode . $phoneFormatted, $message);
                    } else {
                        $data['status']  = false;
                        $data['message'] = 'No';
                    }
                    return json_encode($data);
                }
            }
        }
    }

    public function completePhoneVerification(Request $request)
    {
        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);

        $validation = Validator::make($request->all(), [
            'phone_verification_code' => 'required|numeric',
        ]);

        if ($validation->passes())
        {
            $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

            if ($request->phone_verification_code == $userDetail->phone_verification_code)
            {
                $user                 = User::where(['id' => auth()->user()->id])->first();
                $user->phone          = $phoneFormatted; //
                $user->defaultCountry = $request->defaultCountry;
                $user->carrierCode    = $request->carrierCode;
                $user->formattedPhone = $request->phone;
                $user->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('Phone Number Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 500,
                'message' => $validation->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    //Without PhoneVerification - Add
    public function addPhoneNumberViaAjax(Request $request)
    {
        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);

        $validation = Validator::make($request->all(), [
            'phone' => 'required|unique:users,phone',
        ]);

        if ($validation->passes())
        {
            $user                 = User::findOrFail(auth()->user()->id);
            $user->phone          = $phoneFormatted;
            $user->defaultCountry = $request->defaultCountry;
            $user->carrierCode    = $request->carrierCode;
            $user->formattedPhone = $request->phone;
            $user->save();

            return response()->json([
                'status'     => true,
                'message'    => __('Phone Number Added Successfully!'),
                'class_name' => 'alert-success',
            ]);
        }
        else
        {
            return response()->json([
                'status'     => false,
                'message'    => $validation->errors()->all(),
                'class_name' => 'alert-danger',
            ]);
        }
    }

    public function editGeneratePhoneVerificationCode(Request $request)
    {
        $phoneFormatted = str_replace('+' . $request->code, "", $request->phone);

        $six_digit_random_number = otpCode6();

        $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

        if (!empty($userDetail))
        {
            UserDetail::where([
                'user_id'                 => auth()->user()->id,
                'phone_verification_code' => $userDetail->phone_verification_code,
            ])->update(['phone_verification_code' => $six_digit_random_number]);
        }

        //SMS
        if (!empty($request->phone))
        {
            if (!empty($request->code) && !empty($request->phone))
            {
                $message = $six_digit_random_number . ' is your ' . settings('name') . ' verification code. ';

                $data = [];
                if (checkAppSmsEnvironment() == true && preference('phone_verification') == "Enabled")
                {
                    if (!empty(getSmsConfigDetails()) && getSmsConfigDetails()->status == 'Active')
                    {
                        sendSMS($request->code . $phoneFormatted, $message);

                        $data['status']  = true;
                        $data['message'] = 'Yes';
                    }
                    else
                    {
                        $data['status']  = false;
                        $data['message'] = 'No';
                    }
                    return json_encode($data);
                }
            }
        }
    }

    public function editCompletePhoneVerification(Request $request)
    {
        $phoneFormatted = str_replace('+' . $request->code, "", $request->phone);

        $rules = array(
            'edit_phone_verification_code' => 'required|numeric',
        );

        $fieldNames = array(
            'edit_phone_verification_code' => __('phone verification code'),
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->passes())
        {
            $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

            if ($request->edit_phone_verification_code == $userDetail->phone_verification_code)
            {
                $user                 = User::where(['id' => auth()->user()->id])->first();
                $user->phone          = $phoneFormatted;
                $user->defaultCountry = $request->flag;
                $user->carrierCode    = $request->code;
                $user->formattedPhone = $request->phone;
                $user->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('Phone Number Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 500,
                'message' => $validator->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    //Without PhoneVerification - Update
    public function updatePhoneNumberViaAjax(Request $request)
    {
        $phoneFormatted = str_replace('+' . $request->code, "", $request->phone);

        $validation = Validator::make($request->all(), [
            'phone' => 'unique:users,phone,' . auth()->user()->id,
        ]);

        if ($validation->passes())
        {
            $user = User::findOrFail(auth()->user()->id);

            /*phone*/
            $user->phone          = $phoneFormatted;
            $user->defaultCountry = $request->flag;
            $user->carrierCode    = $request->code;
            $user->formattedPhone = $request->phone;
            /**/

            $user->save();

            return response()->json([
                'status'     => true,
                'message'    => __('Phone Number Updated Successfully!'),
                'class_name' => 'alert-success',
            ]);
        }
        else
        {
            return response()->json([
                'status'     => false,
                'message'    => $validation->errors()->all(),
                'class_name' => 'alert-danger',
            ]);
        }
    }

    public function deletePhoneNumberViaAjax(Request $request)
    {
        $user       = User::find(auth()->user()->id, ['phone', 'carrierCode', 'defaultCountry']);
        $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

        if (!empty($user))
        {
            User::where(['id' => auth()->user()->id])->update([
                'phone'          => null,
                'carrierCode'    => null,
                'defaultCountry' => null,
            ]);

            if (!empty($userDetail))
            {
                UserDetail::where([
                    'user_id'                 => auth()->user()->id,
                    'phone_verification_code' => auth()->user()->user_detail->phone_verification_code,
                ])->update(['phone_verification_code' => null]);
            }
            return response()->json([
                'status'  => 'success',
                'message' => __('Phone Deleted Successfully!'),
            ]);
        }
        else
        {
            return response()->json([
                'status'  => 'error',
                'message' => __('Unable To Delete Phone!'),
            ]);
        }
    }

    public function userDuplicatePhoneNumberCheck(Request $request)
    {
        $req_id = $request->id;

        if (isset($req_id))
        {
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone), 'carrierCode' => $request->carrierCode])->where(function ($query) use ($req_id)
            {
                $query->where('id', '!=', $req_id);
            })->first(['phone', 'carrierCode']);
        }
        else
        {
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone), 'carrierCode' => $request->carrierCode])->first(['phone', 'carrierCode']);
        }

        if (!empty($user->phone) && !empty($user->carrierCode))
        {
            $data['status'] = true;
            $data['fail']   = __("The phone number has already been taken!");
        }
        else
        {
            $data['status']  = false;
            $data['success'] = __("The phone number is Available!");
        }
        return json_encode($data);
    }

    public function logout()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Session::forget('2fa');
        // Session::flush();
        Auth::logout();
        return redirect('login');
    }

    //Personal Identity Verification - start
    public function personalId()
    {
        $data['menu'] = 'profile';
        $data['sub_menu'] = 'profile';

        $data['user'] = User::find(Auth::user()->id);
        $data['two_step_verification'] = preference('two_step_verification');
        $data['documentVerification'] = DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'identity'])->first();

        return view('user.setting.identitiy-verify', $data);
    }

    public function updatePersonalId(Request $request)
    {
        if ($request->isMethod('post')) {
            $user = User::find(auth()->user()->id);
            $user->identity_verified = false;
            $user->save();

            $this->validate($request, [
                'identity_type' => 'required',
                'identity_number' => 'required',
                'identity_file' => ['nullable', new CheckValidFile(getFileExtensions(8))],
            ]);

            $oldFileName = File::where('id', $request->existingIdentityFileID)->value('filename');

            $fileId = $this->insertUserIdentityInfoToFilesTable($request->identity_file);
            if ($fileId && $oldFileName != null) {
                $location = public_path('uploads/user-documents/identity-proof-files/' . $oldFileName);
                if (file_exists($location)) {
                    unlink($location);
                }
            }

            $documentVerification = DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'identity'])->first();
            if (empty($documentVerification)) {
                $createDocumentVerification = new DocumentVerification();
                $createDocumentVerification->user_id = $request->user_id;
                if (!empty($request->identity_file)) {
                    $createDocumentVerification->file_id = $fileId;
                }
                $createDocumentVerification->verification_type = 'identity';
                $createDocumentVerification->identity_type = $request->identity_type;
                $createDocumentVerification->identity_number = $request->identity_number;
                $createDocumentVerification->status = 'pending';
                $createDocumentVerification->save();
            } else {
                $documentVerification->user_id = $request->user_id;
                if (!empty($request->identity_file)) {
                    $documentVerification->file_id = $fileId;
                }
                $documentVerification->verification_type = 'identity';
                $documentVerification->identity_type = $request->identity_type;
                $documentVerification->identity_number = $request->identity_number;
                $documentVerification->status = 'pending';
                $documentVerification->save();
            }
        }
        $this->helper->one_time_message('success', __('User Identity Updated Successfully'));
        return redirect('profile/personal-id');
    }

    protected function insertUserIdentityInfoToFilesTable($identity_file)
    {
        if (!empty($identity_file)) {
            $request = app(\Illuminate\Http\Request::class);
            if ($request->hasFile('identity_file')) {
                $fileName = $request->file('identity_file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $file_extn = strtolower($fileName->getClientOriginalExtension());

                if ($file_extn == 'pdf' || $file_extn == 'png' || $file_extn == 'jpg' || $file_extn == 'jpeg' || $file_extn == 'gif' || $file_extn == 'bmp') {
                    $path = 'uploads/user-documents/identity-proof-files';
                    $uploadPath = public_path($path);
                    $fileName->move($uploadPath, $uniqueName);

                    if (isset($request->existingIdentityFileID)) {
                        $checkExistingFile               = File::where(['id' => $request->existingIdentityFileID])->first();
                        $checkExistingFile->filename     = $uniqueName;
                        $checkExistingFile->originalname = $originalName;
                        $checkExistingFile->save();
                        return $checkExistingFile->id;
                    } else {
                        $file               = new File();
                        $file->user_id      = $request->user_id;
                        $file->filename     = $uniqueName;
                        $file->originalname = $originalName;
                        $file->type         = $file_extn;
                        $file->save();
                        return $file->id;
                    }
                } else {
                    $this->helper->one_time_message('error', __('Invalid File Format!'));
                }
            }
        }
    }
    //Personal Identity Verification - end

    //Personal Address Verification - start
    public function personalAddress()
    {
        $data['menu'] = 'profile';
        $data['sub_menu'] = 'profile';

        $data['user'] = User::find(Auth::user()->id);
        $data['two_step_verification'] = preference('two_step_verification');
        $data['documentVerification'] = DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'address'])->first();

        return view('user.setting.address-verify', $data);
    }

    public function updatePersonalAddress(Request $request)
    {
        if ($request->isMethod('post')) {
            $user = User::find(auth()->user()->id, ['id', 'address_verified']);
            $user->address_verified = false;
            $user->save();

            $this->validate($request, [
                'address_file' => ['nullable', new CheckValidFile(getFileExtensions(8))]
            ]);

            $oldFileName = File::where('id', $request->existingAddressFileID)->value('filename');

            $addressFileId = $this->insertUserAddressProofToFilesTable($request->address_file);
            if ($addressFileId && $oldFileName != null) {
                $location = public_path('uploads/user-documents/address-proof-files/' . $oldFileName);
                if (file_exists($location)) {
                    unlink($location);
                }
            }

            $documentVerification = DocumentVerification::where(['user_id' => $user->id, 'verification_type' => 'address'])->first();
            if (empty($documentVerification)) {
                $createDocumentVerification = new DocumentVerification();
                $createDocumentVerification->user_id = $request->user_id;
                if (!empty($request->address_file)) {
                    $createDocumentVerification->file_id = $addressFileId;
                }
                $createDocumentVerification->verification_type = 'address';
                $createDocumentVerification->status            = 'pending';
                $createDocumentVerification->save();
            } else {
                $documentVerification->user_id = $request->user_id;
                if (!empty($request->address_file)) {
                    $documentVerification->file_id = $addressFileId;
                }
                $documentVerification->status = 'pending';
                $documentVerification->save();
            }
        }
        $this->helper->one_time_message('success', __('User Address Poof Updated Successfully'));
        return redirect('profile/personal-address');
    }

    protected function insertUserAddressProofToFilesTable($address_file)
    {
        if (!empty($address_file)) {
            $request = app(\Illuminate\Http\Request::class);
            if ($request->hasFile('address_file')) {
                $fileName     = $request->file('address_file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $file_extn    = strtolower($fileName->getClientOriginalExtension());

                if ($file_extn == 'pdf' || $file_extn == 'png' || $file_extn == 'jpg' || $file_extn == 'jpeg' || $file_extn == 'gif' || $file_extn == 'bmp') {
                    $path       = 'uploads/user-documents/address-proof-files';
                    $uploadPath = public_path($path);
                    $fileName->move($uploadPath, $uniqueName);

                    if (isset($request->existingAddressFileID)) {
                        $checkExistingFile = File::where(['id' => $request->existingAddressFileID])->first();
                        $checkExistingFile->filename     = $uniqueName;
                        $checkExistingFile->originalname = $originalName;
                        $checkExistingFile->save();
                        return $checkExistingFile->id;
                    } else {
                        $file               = new File();
                        $file->user_id      = $request->user_id;
                        $file->filename     = $uniqueName;
                        $file->originalname = $originalName;
                        $file->type         = $file_extn;
                        $file->save();
                        return $file->id;
                    }
                } else {
                    $this->helper->one_time_message('error', __('Invalid File Format!'));
                }
            }
        }
    }
    //Personal Address Verification - end

    public function updateProfileInfo(Request $request)
    {
        if ($request->isMethod('post'))
        {
            $rules = array(
                'first_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'last_name'  => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            );

            $fieldNames = array(
                'first_name' => __('First Name'),
                'last_name' => __('Last Name'),
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                try {
                    DB::beginTransaction();

                    $user             = User::findOrFail(Auth::user()->id, ['id', 'first_name', 'last_name']); //optimized
                    $user->first_name = $request->first_name;
                    $user->last_name  = $request->last_name;
                    $user->save();

                    $data = $request->all();
                    $data['userId'] = $user->id;

                    $this->addPhoneNumber($data);

                    $UserDetail = UserDetail::with(['user:id', 'country:id'])
                                ->where(['user_id' => Auth::user()->id])
                                ->first(['id', 'user_id', 'country_id', 'address_1', 'address_2', 'city', 'state', 'timezone']);
                    //optimized
                    $UserDetail->user_id    = Auth::user()->id;
                    $UserDetail->country_id = $request->country_id;
                    $UserDetail->address_1  = $request->address_1;
                    $UserDetail->address_2  = $request->address_2;
                    $UserDetail->city       = $request->city;
                    $UserDetail->state      = $request->state;
                    $UserDetail->timezone   = $request->timezone;
                    $UserDetail->save();

                    //Default wallet change - starts
                    $defaultWallet = Wallet::where('user_id', Auth::user()->id)->where('is_default', 'Yes')->first(['id', 'is_default']);
                    $isWalletExist = Wallet::where(['id' => $request->default_wallet, 'user_id' => Auth::user()->id])->exists();

                    if ($isWalletExist) {
                        if ($defaultWallet->id != $request->default_wallet)
                        {
                            //making existing default wallet to 'No'
                            $defaultWallet->is_default = 'No';
                            $defaultWallet->save();

                            //Change to default wallet
                            $walletToDefault             = Wallet::find($request->default_wallet, ['id', 'is_default']);
                            $walletToDefault->is_default = 'Yes';
                            $walletToDefault->save();
                        }
                    }
                    //Default wallet change - ends
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect('profile');
                }
            }
        }
        $this->helper->one_time_message('success', __('Profile Settings Updated Successfully'));
        return redirect('profile');
    }

    public function addPhoneNumber($data)
    {
        $user = User::find($data['userId']);
        if (!empty($data['phone'])) {
            $user->phone          = preg_replace("/[\s-]+/", "", $data['phone']);
            $user->defaultCountry = $data['defaultCountry'];
            $user->carrierCode    = $data['carrierCode'];
            $user->formattedPhone = $data['formattedPhone'];
        } else {
            $user->phone          = null;
            $user->defaultCountry = null;
            $user->carrierCode    = null;
            $user->formattedPhone = null;
        }
        $user->save();
    }

    //check Processed By
    public function checkProcessedBy()
    {
        return response()->json([
            'status'      => true,
            'processedBy' => preference('processed_by'),
        ]);
    }

    public function addOrUpdateUserProfileQrCode(Request $request)
    {
        $user_id = $request->user_id;
        $user = User::where(['id' => $user_id, 'status' => 'Active'])->first(['id', 'formattedPhone', 'email']);
        $qrCode = QrCode::updateQrCode($user);
        return response()->json([
            'status' => true,
            'imgSource' => image($qrCode->qr_image, 'user_qrcode')
        ]);
    }

    public function printUserQrCode($userId, $objectType){
        $this->helper->printQrCode($userId, $objectType);
    }

    public function getWallets()
    {
        $data['provider_status'] = CryptoProvider::getStatus('BlockIo');
        $data['tatum_provider_status'] = CryptoProvider::getStatus('TatumIo');
        $data['wallets'] = Wallet::with(['currency:id,type,logo,code,symbol,status', 'cryptoAssetApiLogs' => function($query) {
            $query->where('object_type', 'wallet_address');
        }])->where(['user_id' => Auth::user()->id])->orderByDesc('balance')->get(['id', 'currency_id', 'balance', 'is_default']);

        return view('user.wallet.index', $data);
    }

    public function updateDefaultCurrency(Request $request)
    {
        $userId = $request->user_id;

        try {
            DB::beginTransaction();
            // Get the existing default wallet and check if the requested default wallet exists
            $defaultWallet = Wallet::where(['user_id' => $userId, 'is_default' => 'Yes'])->first(['id', 'is_default']);
            $isWalletExist = Wallet::where(['id' => $request->default_wallet, 'user_id' => $userId])->exists();

            if ($isWalletExist && optional($defaultWallet)->id != $request->default_wallet) {
                // Set the existing default wallet to 'No' and the requested wallet to 'Yes'
                Wallet::where('id', $defaultWallet->id)->update(['is_default' => 'No']);
                Wallet::where('id', $request->default_wallet)->update(['is_default' => 'Yes']);

                $defaultWallet = Wallet::where(['user_id' => $userId, 'is_default' => 'Yes'])->first(['id', 'currency_id']);
                // Update the default currency of the user
                UserDetail::where('user_id', $userId)->update(['default_currency' => $request->default_wallet]);
            }
            DB::commit();
            $this->helper->one_time_message('success', __('Default Wallet Updated Successfully'));
            return redirect('profile');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('profile');
        }
    }

    public function download($fileName, $fileType)
    {
        $filePath = public_path('/uploads/user-documents/identity-proof-files/' . $fileName);

        if ($fileType == 'address-proof') {
            $filePath = public_path('/uploads/user-documents/address-proof-files/' . $fileName);
        }

        return response()->download($filePath);
    }
}
