<?php

namespace App\Http\Controllers\Admin;

use Config, Artisan, Session, Hash, Auth, DB, Exception;
use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Event\LoginActivity;
use App\Models\{Admin, Preference};
use App\Services\Mail\PasswordResetMailService;
use Illuminate\Support\Facades\Password;

class AdminController extends Controller
{
    protected $helper, $emailController;

    public function __construct()
    {
        $this->helper          = new Common();
        $this->emailController = new EmailController();
    }

    public function login()
    {
        return redirect()->route('admin');
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request['email'])->first();

        if (!empty($admin)) {
            if ($admin->status != 'Inactive') {
                if (auth('admin')->attempt(['email' => trim($request['email']), 'password' => trim($request['password'])])) {
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
    
                    if (!empty(settings('default_currency'))) {
                        Session::put('default_currency', settings('default_currency'));
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
                    // Store admin login information
                    event(new LoginActivity(auth()->guard('admin')->user(), 'Admin'));
    
                    return redirect()->route('dashboard');
                } else {
                    $this->helper->one_time_message('danger', __('Please check your Email/Password.'));
                    return redirect()->route('admin');
                }
            } else {
                $this->helper->one_time_message('danger', __('This admin ID is already blocked.'));
                return redirect()->route('admin');
            }
        } else {
            $this->helper->one_time_message('danger', __('The :x does not exist.', ['x' => __('admin')]));
            return redirect()->route('admin');
        }
    }

    public function logout()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        auth('admin')->logout();
        return redirect()->route('admin');
    }

    /**
     * Show and manage Admin profile
     *
     * @return Admin profile page view
     */
    public function profile()
    {
        $data['menu'] = 'profile';
        $data['admin'] = Admin::where('id', auth('admin')->user()->id)->first(['id', 'first_name', 'last_name', 'email', 'picture']);

        return view('admin.profile.edit_profile', $data);
    }

    /**
     * Update the specified Admin in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return Admin                    List page view
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'last_name'  => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'picture'    => ['nullable', new CheckValidFile(getFileExtensions(3), true)],
        ]);

        $data['first_name'] = $request->first_name;
        $data['last_name']  = $request->last_name;
        $data['updated_at'] = date('Y-m-d H:i:s');

        try
        {
            if ($request->hasFile('picture')) {
                $adminPicture = $request->file('picture');
                if (isset($adminPicture)) {
                    $response = uploadImage($adminPicture, getDirectory('profile'), '100*100', Admin::where('id', $id)->value('picture'));
    
                    if ($response['status']) {
                        $data['picture'] = $response['file_name'];
                    } else {
                        $this->helper->one_time_message('error', __('Something went wrong. Please try again.'));
                        return redirect(config('adminPrefix').'/profile');
                    }
                }
            }
            Admin::where(['id' => $id])->update($data);

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('profile')]));
            return redirect(config('adminPrefix').'/profile');
        }
        catch (Exception $e)
        {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect(config('adminPrefix').'/profile');
        }
    }

    /**
     * show admin change password operation
     *
     * @return change password page view
     */
    public function changePassword()
    {
        $data['menu'] = 'profile';
        $data['admin_id'] = auth('admin')->user()->id;
        return view('admin.profile.change_password', $data);
    }

    public function passwordCheck(Request $request)
    {
        $admin = Admin::where(['id' => $request->id])->first();

        if (!Hash::check($request->old_pass, $admin->password))
        {
            $data['status'] = true;
            $data['fail'] = __("Your old password is incorrect.");
        }
        else
        {
            $data['status'] = false;
        }
        return json_encode($data);
    }

    /**
     * Change admin password operation perform
     *
     * @return change password page view
     */

    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'old_pass' => 'required',
            'new_pass' => 'required',
        ]);

        $admin = Admin::where(['id' => $request->id])->first(['password']);

        $data['password'] = Hash::make($request->new_pass);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (Hash::check($request->old_pass, $admin->password)) {
            Admin::where(['id' => $request->id])->update($data);
            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('password')]));
            return redirect()->intended(config('adminPrefix')."/profile");
        } else {
            $this->helper->one_time_message('error', __('The current password is incorrect.'));
            return redirect()->intended(config('adminPrefix')."/change-password");
        }
    }

    public function forgetPassword(Request $request)
    {
        $data['email'] = $email = $request->email;
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            $this->helper->one_time_message('error', __('Email address does not match.'));
            return redirect()->route('admin');
        }

        $data['token']      = base64_encode(Password::createToken(\App\Models\Admin::where('email', $email)->first()));
        $data['created_at'] = date('Y-m-d H:i:s');

        DB::table('password_resets')->insert($data);
        $data['resetUrl'] = url(config('adminPrefix').'/password/resets', $data['token']);

        (new PasswordResetMailService)->send($admin, $data);

        $this->helper->one_time_message('success', __('Password reset link has been sent to your email address.'));
        return redirect()->route('admin');
    }

    public function verifyToken($token)
    {
        if (!$token) {
            return redirect()->route('admin.login')->withErrors(__('The :x does not exist.', ['x' => __('token')]));    
        }
        
        $reset = DB::table('password_resets')->where('token', $token)->first();
        
        if ($reset) {
            return view('admin.auth.passwordForm', ['token' => $token]);
        }
        
        return redirect()->route('admin.login')->withErrors(__('Token session has been destroyed. Please try to reset again.'));
    }

    public function confirmNewPassword(Request $request)
    {
        $token    = $request->token;
        $password = $request->new_password;
        $confirm  = DB::table('password_resets')->where('token', $token)->first(['email']);

        $admin           = Admin::where('email', $confirm->email)->first();
        $admin->password = Hash::make($password);
        $admin->save();

        DB::table('password_resets')->where('token', $token)->delete();

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('new password')]));
        return redirect()->to('/admin');
    }
}
