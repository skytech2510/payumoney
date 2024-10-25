<?php

namespace App\Http\Controllers\Auth;

// use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Hash, 
    DB
};
use App\Models\{EmailTemplate, 
    User
};
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use Illuminate\Support\Facades\Password;

use App\Services\Mail\PasswordResetMailService;

class ForgotPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $helper, $emailController;
    
    public function __construct()
    {
        $this->middleware('guest');
        $this->helper          = new Common();
        $this->emailController = new EmailController();
    }

    public function forgetPassword(Request $request)
    {
        $email = $request->email;
        $user  = User::where('email', $email)->first();
        
        if (!$user) {
            $this->helper->one_time_message('error', __('Email Address does not match!'));
            return redirect()->route('user.forget_password');
        }

        $userData['email']      = $email;
        $userData['token']      = base64_encode(Password::createToken(User::where('email', $email)->first()));
        $userData['created_at'] = date('Y-m-d H:i:s');

        DB::table('password_resets')->insert($userData);
        $userData['resetUrl'] = url('password/resets', $userData['token']);

        (new PasswordResetMailService)->send($user, $userData);

        $this->helper->one_time_message('success', __('Password reset link has been sent to your email address'));
        
        return redirect()->route('user.forget_password');
    }

    public function verifyToken($token)
    {
        if (!$token) {
            $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('token')]));
            return back();
        }
        $reset = DB::table('password_resets')->where('token', $token)->first();
        if ($reset) {
            $data['token'] = $token;
            return view('frontend.auth.setPassword', $data);
        } else {
            $this->helper->one_time_message('error', __('Token session has been destroyed. Please try to reset again.'));
            return back();
        }
    }

    public function confirmNewPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        $token    = $request->token;
        $password = $request->password;
        $confirm = DB::table('password_resets')->where('token', $token)->first();
        $user           = User::where('email', $confirm->email)->first();
        $user->password = Hash::make($password);
        $user->save();
        DB::table('password_resets')->where('token', $token)->delete();

        $this->helper->one_time_message('success', __('Password changed successfully.'));
        return redirect()->to('/login');
    }
}
