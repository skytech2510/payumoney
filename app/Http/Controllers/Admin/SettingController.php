<?php

namespace App\Http\Controllers\Admin;

use Config, Validator, Session, Cache, View, Mail, DB, Exception, Common;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use TechVill\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{Currency,
    PaymentMethod,
    EmailConfig,
    Preference,
    FeesLimit,
    SmsConfig,
    Language,
    Setting
};

class SettingController extends Controller
{
    public $dimension = ['logo' => ['width' => 174, 'height' => 44], 'favicon' => ['width' => 40, 'height' => 40]];
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function general(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'settings';

        if ($request->isMethod('post')) {

            $rules = array(
                'name' => 'required',
            );

            $fieldNames = array(
                'name' => 'Name',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                // Setting
                Setting::where(['name' => 'name'])->update(['value' => preg_replace('/\s+/', ' ', $request->name)]);
                
                changeEnvironmentVariable('APP_NAME', $request->name);

                // Save Logo & Favicon
                foreach ($_FILES["photos"]["error"] as $key => $error) {
                    $tmp_name = $_FILES["photos"]["tmp_name"][$key];
                    $name = str_replace(' ', '_', $_FILES["photos"]["name"][$key]);
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $name = time() . '_' . $key . '.' . $ext;

                    if ($error == 0) {
                        $this->storeImageToFixedDimension($request->photos[$key], $name, $key);
                    }
                }

                Setting::where(['name' => 'head_code'])->update(['value' => is_null($request->head_code) ? '' : trim($request->head_code)]);
                Setting::where(['name' => 'default_currency'])->update(['value' => $request->default_currency]);
                Currency::where('id', $request->default_currency)->update(['rate' => 1]);
                Setting::where(['name' => 'default_language'])->update(['value' => $request->default_language]);

                // recaptcha
                Setting::where(['name' => 'has_captcha'])->update(['value' => $request->has_captcha]);

                // login_via
                Setting::where(['name' => 'login_via'])->update(['value' => $request->login_via]);

                //Currency
                Currency::where('default', '=', '1')->update(['default' => '0']);
                Currency::where('id', $request->default_currency)->update(['default' => '1']);
                Session::put('default_currency', $request->default_currency);

                // Create or update fees-limit on default currency change
                $paymentMethodArray = PaymentMethod::where(['status' => 'Active'])->pluck('id')->toArray();
                $transaction_types = [Deposit, Withdrawal, Transferred, Exchange_From, Request_Received];

                foreach ($transaction_types as $transaction_type) {
                    $feeslimit = FeesLimit::where(['has_transaction' => 'No', 'currency_id' => $request->default_currency])->get(['id', 'has_transaction']);
                    if ($feeslimit->count() > 0) {
                        // update existing has transaciton - no to yes
                        foreach ($feeslimit as $fLimit) {
                            $fLimit->has_transaction = 'Yes';
                            $fLimit->save();
                        }
                    } else {
                        if ($transaction_type == Deposit || $transaction_type == Withdrawal) {
                            foreach ($paymentMethodArray as $key => $value) {
                                $checkFeeslimitMultiplePm = FeesLimit::where(['currency_id' => $request->default_currency, 'transaction_type_id' => $transaction_type, 'payment_method_id' => $value])
                                    ->first(['id', 'currency_id', 'transaction_type_id', 'payment_method_id', 'has_transaction']);
                                if (empty($checkFeeslimitMultiplePm)) {
                                    // insert new records of feeslimit on change of default currency with payment method
                                    $feesLimit = new FeesLimit();
                                    $feesLimit->currency_id = $request->default_currency;
                                    $feesLimit->transaction_type_id = $transaction_type;
                                    $feesLimit->payment_method_id = $value;
                                    $feesLimit->has_transaction = 'Yes';
                                    $feesLimit->save();
                                }
                            }
                        } else {
                            $checkFeeslimitSinglePm = FeesLimit::where(['currency_id' => $request->default_currency, 'transaction_type_id' => $transaction_type])
                                ->first(['id', 'currency_id', 'transaction_type_id', 'has_transaction']);
                            if (empty($checkFeeslimitSinglePm)) {
                                // insert new records of feeslimit on change of default currency with no payment method
                                $feesLimit = new FeesLimit();
                                $feesLimit->currency_id = $request->default_currency;
                                $feesLimit->transaction_type_id = $transaction_type;
                                $feesLimit->has_transaction = 'Yes';
                                $feesLimit->save();
                            }
                        }
                    }
                }

                if (!empty($request->allowed_wallets) && ($key = array_search($request->default_currency, $request->allowed_wallets)) !== false) {
                    $allowedWallets = $request->allowed_wallets;
                    unset($allowedWallets[$key]);
                } else {
                    $allowedWallets = $request->allowed_wallets;
                }

                if (isset($request->allowed_wallets)) {
                    Setting::where(['name' => 'allowed_wallets'])->update(['value' => implode(',', $allowedWallets)]);
                } else {
                    Setting::where(['name' => 'allowed_wallets'])->update(['value' => 'none']);
                }

                Language::where('default', '=', '1')->update(['default' => '0']);
                Language::where('id', $request->default_language)->update(['default' => '1']);

                $lang = Language::find($request->default_language, ['id', 'short_name']);
                Preference::where(['field' => 'dflt_lang', 'category' => 'company'])->update(['value' => $lang->short_name]);
                Cache::forget(config('cache.prefix') . '-settings');
                Cache::forget(config('cache.prefix') . '-preferences');
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('general settings')]));
                return redirect(config('adminPrefix').'/settings');
            }
        }

        $data['result'] = settings('general');
        $data['language'] = $this->helper->key_value('id', 'name', Language::where(['status' => 'Active'])->get(['id', 'name'])->toArray());
        $data['currency'] = $this->helper->key_value('id', 'name', Currency::where(['type' => 'fiat', 'status' => 'Active'])->get(['id', 'name'])->toArray());
        $data['allowedWallets'] = $this->helper->key_value('id', 'code', Currency::where([ 'default' => 0, 'status' => 'Active'])->whereIn('type', ['fiat', 'crypto'])->get(['id', 'code'])->toArray());
        $data['selectedAllowedWallets'] = settings('allowed_wallets');

        return view('admin.settings.general', $data);
    }

    public function adminSecuritySettings(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'admin-security-settings';

        if ($request->isMethod('POST')) {

            // Admin Single ip address access
            if (! empty($request->get('admin_access_ips'))) {
                if ($request->admin_access_ip_setting == 'Enabled') {
                    if (!in_array($request->ip(), ['::1', '127.0.0.1', 'localhost']) && !in_array($request->ip(), array_column((json_decode($request->get('admin_access_ips'))),'value'))) {
                        $this->helper->one_time_message('error', __('The ip addresses you have added doesn\'t belongs to you.'));
                        return redirect(config('adminPrefix').'/settings/admin-security-settings');
                    } elseif (in_array($request->ip(), ['::1', '127.0.0.1', 'localhost']) && !in_array($request->ip(), array_column((json_decode($request->get('admin_access_ips'))),'value'))) {
                        $this->helper->one_time_message('error', __('Please add a local ip address.'));
                        return redirect(config('adminPrefix').'/settings/admin-security-settings');
                    }
                }
                Setting::where(['name' => 'admin_access_ip_setting', 'type' => 'admin_security'])->update(['value' => $request->admin_access_ip_setting]);
                $adminAccessIps = join(',', array_column((json_decode($request->get('admin_access_ips'))), 'value'));
                Setting::where(['name' => 'admin_access_ips', 'type' => 'admin_security'])->update(['value' => $adminAccessIps]);
                Cache::forget(config('cache.prefix') . '-settings');
            } else {
                $this->helper->one_time_message('error', __('You have to add at least one IP before update.'));
                return redirect(config('adminPrefix').'/settings/admin-security-settings');
            }

            // admin url prefix
            if (!empty($request->get('admin_url_prefix'))) {
                $slug = Str::slug($request->admin_url_prefix, '-');
                Preference::where(['field' => 'admin_url_prefix'])->update(['value' => $slug]);
                Cache::forget(config('cache.prefix') . '-preferences');
                Config::set(['adminPrefix' => $slug]);
                View::share('adminPrefix', $slug);
                changeEnvironmentVariable('ADMIN_PREFIX', $slug);
            } else {
                $this->helper->one_time_message('error', __('Admin prefix is not set.'));
                return redirect(config('adminPrefix').'/settings/admin-security-settings');
            }

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('admin security settings')]));
            return redirect(config('adminPrefix').'/settings/admin-security-settings');
        }

        $data['result'] = settings('admin_security');

        $adminAccessIps = explode(',', $data['result']['admin_access_ips']);
        $tempArray = [];
        foreach($adminAccessIps as $key => $value) {
            $tempArray[$key]['value'] = $value;
        }
        $data['adminAccessIPs'] = json_encode($tempArray);

        $pref = Preference::where('category', 'preference')->get();
        $data_arr = [];
        foreach ($pref as $row) {
            $data_arr[$row->category][$row->field] = $row->value;
        }
        $data['prefData'] = $data_arr;

        return view('admin.settings.admin_security', $data);
    }

    protected function storeImageToFixedDimension($image, $fileName, $key)
    {
        $location = public_path("uploads/logos");
        $ext = strtolower($image->getClientOriginalExtension());

        //check extension
        if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'bmp') {
            try {
                $img = Image::make($image->getRealPath());
                $img->resize($this->dimension[$key]['width'], $this->dimension[$key]['height'])->save($location . '/' . $fileName);
                Setting::where(['name' => $key])->update(['value' => $fileName]);
                Cache::forget(config('cache.prefix') . '-settings');
            } catch (Exception $e) {
                $this->helper->one_time_message('error', $e->getMessage());
            }
        } else {
            $this->helper->one_time_message('error', __('The :x format is invalid.', ['x' => __('image')]));
        }
    }

    public function updateSideBarCompanyLogo(Request $request)
    {
        $filename = '';
        $picture = $request->photos['logo'];

        if (isset($picture)) {
            $location = public_path("uploads/logos");
            $ext      = strtolower($picture->getClientOriginalExtension());
            $filename = time() . '.' . $ext;
            $img      = Image::make($picture->getRealPath());
            $img->resize($this->dimension['logo']['width'], $this->dimension['logo']['height'])->save($location . '/' . $filename);
            return response()->json([
                'filename' => $filename,
            ]);
        }
    }

    public function checkSmsGatewaySettings(Request $request)
    {
        $smsConfigs = getSmsConfigDetails();
        if (empty($smsConfigs)) {
            return response()->json([
                'status'  => false,
                'message' => __('Sms settings is inactive or configured incorrectly.'),
            ]);
        }
    }

    //deleteSettingLogo
    public function deleteSettingLogo(Request $request)
    {
        $data = [
            'success' => false,
            'message' => __('The :x does not exist.', ['x' => __('logo')]),
        ];

        $logo = $request->logo;

        if (isset($logo)) {
            $setting = Setting::where('name', 'logo')
                ->where('type', 'general')
                ->where('value', $logo)
                ->first();
    
            if ($setting) {
                $setting->update(['value' => null]);
                Cache::forget(config('cache.prefix') . '-settings');
    
                if ($logo != null) {
                    $dir = public_path('uploads/logos/' . $logo);
                    if (file_exists($dir)) {
                        unlink($dir);
                    }
                }
    
                $data = [
                    'success' => true,
                    'message' => __('The :x has been successfully deleted.', ['x' => __('logo')])
                ];
            }
        }
    
        return $data;
    }

    //deleteSettingFavicon
    public function deleteSettingFavicon(Request $request)
    {
        $data = [
            'success' => false,
            'message' => __('The :x does not exist.', ['x' => __('favicon')]),
        ];

        $favicon = $request->favicon;

        if (isset($favicon)) {
            $setting = Setting::where('name', 'favicon')
                ->where('type', 'general')
                ->where('value', $favicon)
                ->first();

            if ($setting) {
                $setting->update(['value' => null]);
                Cache::forget(config('cache.prefix') . '-settings');

                if ($favicon != null) {
                    $dir = public_path('uploads/logos/' . $favicon);
                    if (file_exists($dir)) {
                        unlink($dir);
                    }
                }
                $data = [
                    'success' => true,
                    'message' => __('The :x has been successfully deleted.', ['x' => __('favicon')])
                ];
            }
        }

        return $data;
    }

    //email settings
    public function email(Request $request)
    {
        if (! $request->isMethod('post')) {
            $data['menu'] = 'settings';
            $data['settings_menu']   = 'email';
            $general        = EmailConfig::find("1")->toArray();
            $data['result'] = $general;

            return view('admin.settings.email', $data);
        } else if ($request->isMethod('post')) {
            $email_config = EmailConfig::find('1');
            if ($email_config) {
                $email_config->email_protocol   = $request->driver;
                $email_config->email_encryption = $request->encryption;
                $email_config->smtp_host        = $request->host;
                $email_config->smtp_port        = $request->port;
                $email_config->smtp_email       = $request->from_address;
                $email_config->smtp_username    = $request->username;
                $email_config->smtp_password    = $request->password;
                $email_config->from_address     = $request->from_address;
                $email_config->from_name        = $request->from_name;
                $email_config->save();
            } else {
                $configIns                   = new EmailConfig();
                $configIns->email_protocol   = $request->driver;
                $configIns->email_encryption = $request->encryption;
                $configIns->smtp_host        = $request->host;
                $configIns->smtp_port        = $request->port;
                $configIns->smtp_email       = $request->from_address;
                $configIns->smtp_username    = $request->username;
                $configIns->smtp_password    = $request->password;
                $configIns->from_address     = $request->from_address;
                $configIns->from_name        = $request->from_name;
                $configIns->save();
            }

            if ($request->driver == "smtp") {
                $rules = array(
                    'driver'       => 'required',
                    'host'         => 'required',
                    'port'         => 'required',
                    'from_address' => 'required',
                    'from_name'    => 'required',
                    'encryption'   => 'required',
                    'username'     => 'required',
                    'password'     => 'required',
                );

                $fieldNames = array(
                    'driver'       => 'Driver',
                    'host'         => 'Host',
                    'port'         => 'Port',
                    'from_address' => 'From Address',
                    'from_name'    => 'From Name',
                    'encryption'   => 'Encryption',
                    'username'     => 'Username',
                    'password'     => 'Password',
                );

                $validator = Validator::make($request->all(), $rules);
                $validator->setAttributeNames($fieldNames);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                } else {
                    $data = $request->all();
                    Config::set([
                        'mail.driver' => isset($data['driver']) ? $data['driver'] : '',
                        'mail.host' => isset($data['host']) ? $data['host'] : '',
                        'mail.port' => isset($data['port']) ? $data['port'] : '',
                        'mail.from' => [
                            'address' => isset($data['from_address']) ? $data['from_address'] : '',
                            'name'=> isset($data['from_name']) ? $data['from_name'] : ''
                        ],
                        'mail.encryption' => isset($data['encryption']) ? $data['encryption'] : '',
                        'mail.username' => isset($data['username']) ? $data['username'] : '',
                        'mail.password' => isset($data['password']) ? $data['password'] : '',
                    ]);

                    $fromInfo = config('mail.from');

                    $user = [];
                    $user['to']       = 'imtiaze.techvill@gmail.com';
                    $user['from']     = $fromInfo['address'];
                    $user['fromName'] = $fromInfo['name'];
                    try {
                        $ok = Mail::send('emails.verify', ['user' => $user], function ($m) use ($user)
                        {
                            $m->from($user['from'], $user['fromName']);
                            $m->to($user['to']);
                            $m->subject('verify smtp settings');
                        });
                        $emailConfig         = EmailConfig::find("1");
                        $emailConfig->status = 1;
                        $emailConfig->save();
                        $this->helper->one_time_message('success', __('The :x has been successfully verified.', ['x' => __('SMTP settings')]));
                    } catch(Exception $e){
                        $emailConfig         = EmailConfig::find("1");
                        $emailConfig->status = 0;
                        $emailConfig->save();
                        $this->helper->one_time_message('error', $e->getMessage());
                        return redirect(config('adminPrefix').'/settings/email');
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('email settings')]));
                    return redirect(config('adminPrefix').'/settings/email');
                }
            }
            else {
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('email settings')]));
                return redirect(config('adminPrefix').'/settings/email');
            }
        }
    }

    //sms settings
    public function sms(Request $request, $type)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'sms';

        if (! $request->isMethod('post')) {
            if ($type == 'twilio') {
                $data['twilio']      = $twilio      = SmsConfig::where(['type' => $type])->first();
                $data['credentials'] = $credentials = json_decode($twilio->credentials);
                return view('admin.settings.sms.twilio', $data);
            } else if ($type == 'nexmo') {
                $data['nexmo']       = $nexmo       = SmsConfig::where(['type' => $type])->first();
                $data['credentials'] = $credentials = json_decode($nexmo->credentials);
                return view('admin.settings.sms.nexmo', $data);
            }
        } else {
            if ($type == 'twilio') {

                $rules = array(
                    'name'                               => 'required',
                    'twilio.account_sid'                 => 'required',
                    'twilio.auth_token'                  => 'required',
                    'twilio.default_twilio_phone_number' => 'required',
                    'status'                             => 'required',
                );

                $fieldNames = array(
                    'name'                               => 'Name',
                    'twilio.account_sid'                 => 'Twilio Key',
                    'twilio.auth_token'                  => 'Twilio Secret',
                    'twilio.default_twilio_phone_number' => 'Twilio Phone Number',
                    'status'                             => 'Status',
                );
                $validator = Validator::make($request->all(), $rules);
                $validator->setAttributeNames($fieldNames);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $twilioSmsConfig = SmsConfig::where(['type' => base64_decode($request->type)])->first();

                if ($twilioSmsConfig && (($request->status == 'Active') || ($request->status == 'Inactive'))) {
                    $twilioSmsConfig->credentials = json_encode($request->twilio);
                    $twilioSmsConfig->status      = $request->status == 'Active' ? 'Active' : 'Inactive';
                    $twilioSmsConfig->save();
                    if ($twilioSmsConfig->status == 'Active') {
                        $nexmoSmsConfig  = SmsConfig::where(['type' => 'nexmo'])->first(['id', 'status']);
                        $nexmoSmsConfig->status = 'Inactive';
                        $nexmoSmsConfig->save();
                    }
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('twilio sms settings')]));
                    return redirect(config('adminPrefix').'/settings/sms/twilio');
                }
            } else if ($type == 'nexmo') {
                $rules = array(
                    'name'                             => 'required',
                    'nexmo.Key'                        => 'required',
                    'nexmo.Secret'                     => 'required',
                    'nexmo.default_nexmo_phone_number' => 'required',
                    'status'                           => 'required',
                );
                $fieldNames = array(
                    'name'                             => 'Name',
                    'nexmo.Key'                        => 'Nexmo Key',
                    'nexmo.Secret'                     => 'Nexmo Secret',
                    'nexmo.default_nexmo_phone_number' => 'Nexmo Phone Number',
                    'status'                           => 'Status',
                );

                $validator = Validator::make($request->all(), $rules);
                $validator->setAttributeNames($fieldNames);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $nexmoSmsConfig = SmsConfig::where(['type' => base64_decode($request->type)])->first();

                if (!empty($nexmoSmsConfig) && (($request->status == 'Active') || ($request->status == 'Inactive'))) {
                    $nexmoSmsConfig->credentials = json_encode($request->nexmo);
                    $nexmoSmsConfig->status      = $request->status == 'Active' ? 'Active' : 'Inactive';
                    $nexmoSmsConfig->save();
                    if ($nexmoSmsConfig->status == 'Active') {
                        $twilioSmsConfig         = SmsConfig::where(['type' => 'twilio'])->first(['id', 'status']);
                        $twilioSmsConfig->status = 'Inactive';
                        $twilioSmsConfig->save();
                    }
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('nexmo sms settings')]));
                    return redirect(config('adminPrefix').'/settings/sms/nexmo');
                }
            }
        }
    }

    // social_links
    public function social_links(Request $request)
    {
        if (!$request->isMethod('post')) {

            $data['menu'] = 'settings';
            $data['settings_menu'] = 'social_links';
            $general = DB::table('socials')->get();

            $data['result'] = $general;
            return view('admin.settings.social', $data);

        } else if ($request->isMethod('post')) {
            $links = $request->all();
            unset($links['_token']);

            foreach ($links as $key => $link) {
                $social = DB::table('socials')->where('name', $key)->first();

                if (!$social) {
                    $key2 = str_replace('_', ' ', $key);

                    $data['name'] = $key;
                    $data['icon'] = "<i class=\"ti-$key2\" aria-hidden=\"true\"></i>";
                    $data['url']  = $link;
                    DB::table('socials')->insert($data);
                } else {
                    DB::table('socials')->where('name', $key)->update(['url' => $link]);
                }
            }

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('social link settings')]));
            return redirect(config('adminPrefix').'/settings/social_links');
        }
    }

    // api informations
    public function api_informations(Request $request)
    {
        if ($request->isMethod('post')) {

            $rules = array(
                'captcha_secret_key' => 'required',
                'captcha_site_key'   => 'required',
            );
            $fieldNames = array(
                'captcha_secret_key' => __('Captcha Secret Key'),
                'captcha_site_key'   => __('Captcha Site Key'),
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                Setting::where(['name' => 'secret_key', 'type' => 'recaptcha'])->update(['value' => $request->captcha_secret_key]);
                Setting::where(['name' => 'site_key', 'type' => 'recaptcha'])->update(['value' => $request->captcha_site_key]);

                $data = $request->all();
                Config::set([
                    'captcha.secret'  => isset($data['captcha_secret_key']) ? $data['captcha_secret_key'] : '',
                    'captcha.sitekey' => isset($data['captcha_site_key']) ? $data['captcha_site_key'] : '',
                ]);
                Cache::forget(config('cache.prefix') . '-settings');
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('api informatin settings')]));
                return redirect(config('adminPrefix').'/settings/api_informations');
            }
        }

        $data['menu'] = 'settings';
        $data['settings_menu'] = 'api_informations';
        $data['recaptcha'] = settings('recaptcha');

        return view('admin.settings.api_credentials', $data);
    }

    public function currencyConversionRateApi(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'exchange_enabled_api' => 'required|in:Disabled,currency_converter_api_key,exchange_rate_api_key',
                'currency_converter_api_key' => ($request->exchange_enabled_api == "Disabled") ? '' : 'required_without_all:exchange_rate_api_key',
                'exchange_rate_api_key' => ($request->exchange_enabled_api == "Disabled") ? '' : 'required_without_all:currency_converter_api_key',
                'crypto_compare_api_key' => ($request->crypto_compare_enabled_api == "Disabled") ? '' : 'required',
                'crypto_compare_enabled_api' => 'required|in:Disabled,Enabled'
            ];
            $messages = [
                'currency_converter_api_key.required_without_all' => __('At least one api key is required'),
                'exchange_rate_api_key.required_without_all' => __('At least one api key is required'),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                Setting::where(['name' => 'currency_converter_api_key', 'type' => 'currency_exchange_rate'])->update(['value' => $request->currency_converter_api_key]);
                Setting::where(['name' => 'exchange_rate_api_key', 'type' => 'currency_exchange_rate'])->update(['value' => $request->exchange_rate_api_key]);
                Setting::where(['name' => 'exchange_enabled_api', 'type' => 'currency_exchange_rate'])->update(['value' => $request->exchange_enabled_api]);
                Setting::where(['name' => 'crypto_compare_api_key', 'type' => 'crypto_compare_rate'])->update(['value' => $request->crypto_compare_api_key]);
                Setting::where(['name' => 'crypto_compare_enabled_api', 'type' => 'crypto_compare_rate'])->update(['value' => $request->crypto_compare_enabled_api]);

                Cache::forget(config('cache.prefix') . '-settings');
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('currency conversion rate api')]));
                return redirect(config('adminPrefix').'/settings/currency-conversion-rate-api');
            }
        }

        $data['menu'] = 'settings';
        $data['settings_menu'] = 'currency_conversion_rate_api';
        $data['currencyExchangeApi'] = settings('currency_exchange_rate');
        $data['cryptoCompareApi'] = settings('crypto_compare_rate');
        return view('admin.settings.currency_conversion_rate_api', $data);
    }

    // preference - form
    public function preference()
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'preference';

        $data['timezones'] = phpDefaultTimeZones();

        $pref = Preference::where('category', 'preference')->get();
        $data_arr = [];
        foreach ($pref as $row) {
            $data_arr[$row->category][$row->field] = $row->value;
        }
        $data['prefData'] = $data_arr;

        return view('admin.settings.preference', $data);
    }

    // preference - save
    public function savePreference(Request $request)
    {
        if ($request->two_step_verification == 'by_google_authenticator') {
            if (!extension_loaded('imagick')) {
                $this->helper->one_time_message('error', __('For google 2fa authenticator, need to enable the Imagick extenstion.'));
                return redirect()->back();
            }
        }

        $post = $request->all();
        unset($post['_token']);

        if ($post['date_format'] == 0) {
            $post['date_format_type'] = 'yyyy' . $post['date_sepa'] . 'mm' . $post['date_sepa'] . 'dd';
        } elseif ($post['date_format'] == 1) {
            $post['date_format_type'] = 'dd' . $post['date_sepa'] . 'mm' . $post['date_sepa'] . 'yyyy';
        } elseif ($post['date_format'] == 2) {
            $post['date_format_type'] = 'mm' . $post['date_sepa'] . 'dd' . $post['date_sepa'] . 'yyyy';
        } elseif ($post['date_format'] == 3) {
            $post['date_format_type'] = 'dd' . $post['date_sepa'] . 'M' . $post['date_sepa'] . 'yyyy';
        } elseif ($post['date_format'] == 4) {
            $post['date_format_type'] = 'yyyy' . $post['date_sepa'] . 'M' . $post['date_sepa'] . 'dd';
        }

        $i = 0;
        foreach ($post as $key => $value) {
            $data[$i]['category'] = "preference";
            $data[$i]['field']    = $key;
            $data[$i]['value']    = $value;
            $i++;
        } foreach ($data as $key => $value) {
            $category = $value['category'];
            $field    = $value['field'];
            $val      = $value['value'];
            $res      = Preference::where(['field' => $field])->first();

            if (empty($res)) {
                DB::insert(DB::raw("INSERT INTO preferences(category,field,value) VALUES ('$category','$field','$val')"));
            } else {
                Preference::where(['category' => 'preference', 'field' => $field])->update(array('field' => $field, 'value' => $val));
            }
        }

        Cache::forget(config('cache.prefix') . '-preferences');

        $pref = Preference::where('category', 'preference')->get();
        if (!empty($pref)) {
            foreach ($pref as $value) {
                $prefer[$value->field] = $value->value;
            }
            Session::put($prefer);
        }

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('preference')]));
        return redirect(config('adminPrefix').'/settings/preference');
    }
}
