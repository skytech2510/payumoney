<?php

use App\Exceptions\Api\V2\DepositMoneyException;

use App\Models\{Bank,
    CurrencyPaymentMethod,
    Parameter,
    Transaction,
    Withdrawal
};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

if (!function_exists('setDateForDb')) {
    /**
     * format date time for Database
     *
     * @param  mixed $value
     * @return void
     */
    function setDateForDb($value)
    {
        $separator = session('date_sepa');
        $dateFormat = session('date_format_type');

        if (str_replace($separator, '', $dateFormat) == "mmddyyyy") {
            $value = str_replace($separator, '/', $value);
            $date  = date('Y-m-d', strtotime($value));
        } else {
            $date = date('Y-m-d', strtotime(strtr($value, $separator, '-')));
        }

        return $date;
    }
}

if (!function_exists('unique_code')) {
    /**
     * unique_code
     *
     * @param  int $length
     * @return string
     */
    function unique_code(int $length = 13): string
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception(__('There is currently no random function that meets the standards of cryptographic security.'));
        }

        return strtoupper(substr(bin2hex($bytes), 0, $length));
    }
}

/**
 * [dateFormat description]
 * @param  [type] $value    [any number]
 * @return [type] [formates date according to preferences setting in Admin Panel]
 */
function dateFormat($value, $userId = null) //$userId - needed for using user_id for mobile app (as mobile app does not know auth()->user()->id)
{
    $timezone = '';
    $prefix   = str_replace('/', '', request()->route()->getPrefix());
    if ($prefix == config('adminPrefix')) {
        $timezone = preference('dflt_timezone');
    } else {
        if (!empty($userId)) {
            $user = App\Models\User::with('user_detail:user_id,timezone')->where(['id' => $userId])->first(['id']);
        } else {
            if (!empty(auth()->user()->id)) {
                $user = App\Models\User::with('user_detail:user_id,timezone')->where(['id' => auth()->user()->id])->first(['id']);
            }
        }
        if (!empty(auth()->user()->id) || !empty($userId)) {
            $timezone = $user->user_detail->timezone;
        } else {
            $timezone = 'UTC';
        }
    }
    $today = new DateTime($value, new DateTimeZone(config('app.timezone')));
    $today->setTimezone(new DateTimeZone($timezone));
    $value = $today->format('Y-m-d H:i:s');

    $preferenceData = \App\Models\Preference::where(['category' => 'preference'])->whereIn('field', ['date_format_type', 'date_sepa'])->get(['field', 'value'])->toArray();
    $preferenceData = App\Http\Helpers\Common::key_value('field', 'value', $preferenceData);
    $preference     = $preferenceData['date_format_type'];
    $separator      = $preferenceData['date_sepa'];

    $data   = str_replace(['/', '.', ' ', '-'], $separator, $preference);
    $data   = explode($separator, $data);
    $first  = $data[0];
    $second = $data[1];
    $third  = $data[2];

    $dateInfo = str_replace(['/', '.', ' ', '-'], $separator, $value);
    $datas    = explode($separator, $dateInfo);
    $year     = $datas[0];
    $month    = $datas[1];
    $day      = $datas[2];

    $dateObj   = DateTime::createFromFormat('!m', $month);
    $monthName = $dateObj->format('F');

    $toHoursMin = \Carbon\Carbon::createFromTimeStamp(strtotime($value))->format(' g:i A');
    if ($first == 'yyyy' && $second == 'mm' && $third == 'dd') {
        $value = $year . $separator . $month . $separator . $day . $toHoursMin;
    } elseif ($first == 'dd' && $second == 'mm' && $third == 'yyyy') {
        $value = $day . $separator . $month . $separator . $year . $toHoursMin;
    } elseif ($first == 'mm' && $second == 'dd' && $third == 'yyyy') {
        $value = $month . $separator . $day . $separator . $year . $toHoursMin;
    } elseif ($first == 'dd' && $second == 'M' && $third == 'yyyy') {
        $value = $day . $separator . $monthName . $separator . $year . $toHoursMin;
    } elseif ($first == 'yyyy' && $second == 'M' && $third == 'dd') {
        $value = $year . $separator . $monthName . $separator . $day . $toHoursMin;
    }
    return $value;

}

/**
 * [roundFormat description]
 * @param  [type] $value   [any number]
 * @return [type] [formats to 2 decimal places]
 */
function decimalFormat($value)
{
    $preference = preference('decimal_format_amount', 2);
    return number_format((float) ($value), $preference, '.', '');
}

if (!function_exists('cryptoFormat')) {

    function cryptoFormat($value)
    {
        $preference = preference('decimal_format_amount_crypto', 8);
        return number_format((float) ($value), $preference, '.', '');
    }
}

/**
 * [roundFormat description]
 * @param  [type] $value     [any number]
 * @return [type] [placement of money symbol according to preferences setting in Admin Panel]
 */
function moneyFormat($symbol, $value)
{
    if (!empty($symbol)) {
        if (preference('money_format') == "before") {
            return $symbol . ' ' . $value;
        }
        return $value . ' ' . $symbol;
    }
    return $value;
}

function moneyFormatForDashboardProgressBars($symbol, $value)
{
    return moneyFormat($symbol, $value);
}

//function to set pages position on frontend
function getMenuContent($position)
{
    return \App\Models\Pages::where('position', 'like', "%$position%")->whereStatus('active')->get(['url', 'name']);
}

if (!function_exists('getSocialLink')) {
    function getSocialLink()
    {
        return DB::table('socials')->select('url', 'icon')->whereNotNull('url')->get()
            ->toArray();
    }
}

if (!function_exists('meta')) {
    function meta($url, $field)
    {
        $meta = \App\Models\Meta::where('url', $url)->value($field);

        if ($meta !== null) {
            return $meta;
        } elseif (in_array($field, ['title', 'description', 'keyword'])) {
            return __("Page Not Found");
        }

        return "";
    }
}

function getTime($date)
{
    return date("H:i A", strtotime($date));
}

function changeEnvironmentVariable($key, $value)
{
    $path = base_path('.env');

    if (is_bool(env($key))) {
        $old = env($key) ? 'true' : 'false';
    } elseif (env($key) === null) {
        $old = 'null';
    } else {
        $old = env($key);
    }

    if (file_exists($path)) {
        if ($old == 'null') {
            file_put_contents($path, "\n$key=" . str_replace(' ', '', $value), FILE_APPEND);
        } else {
            file_put_contents($path, str_replace(
                "$key=" . $old, "$key=" . str_replace(' ', '', $value), file_get_contents($path)
            ));
        }
    }
}

function trimExtraZeros($num) {
    $isDecimal = strpos($num, '.') || strpos($num, ',');

    if ($isDecimal === false) {
        return $num;
    } else {
        $dotPos = strpos(strrev($num), '.');
        $commaPos = strpos(strrev($num), ',');

        if ($dotPos < $commaPos) {
            $num =  rtrim(rtrim($num, '0'), '.');
        } else {
            $num =  rtrim(rtrim($num, '0'), ',');
        }

        if (substr($num, -1) == '.') {
            return str_replace('.', '', $num);
        } else if (substr($num, -1) == ',') {
            return str_replace(',', '', $num);
        }
        return $num;
    }
}

function formatNumber($num = 0, $currencyId = NULL)
{
    $currencyType = 'fiat';
    if ($currencyId !== null) {
        $currencyType = \App\Models\Currency::where('id', $currencyId)->value('type');
    }

    $seperator = preference('thousand_separator', '.');
    $format =  ($currencyType == 'fiat') ? preference('decimal_format_amount', 2) : preference('decimal_format_amount_crypto', 8);

    if ($seperator == '.') {
        $num = trimExtraZeros(number_format($num, (int)$format, ",", "."));
    } else if ($seperator == ',') {
        $num = trimExtraZeros(number_format($num, (int)$format, ".", ","));
    }
    return $num;
}

if (!function_exists('getLanguagesListAtFooterFrontEnd')) {
    function getLanguagesListAtFooterFrontEnd()
    {
        return App\Models\Language::where(['status' => 'Active'])->get(['short_name', 'name']);
    }
}

if (!function_exists('getAppStoreLinkFrontEnd')) {
    function getAppStoreLinkFrontEnd()
    {
        return App\Models\AppStoreCredentials::where(['has_app_credentials' => 'Yes'])->get(['logo', 'link', 'company']);
    }
}

if (!function_exists('isEnabledExchangeApi')) {
    /**
     * Checking which currency exchange api is enabled
     *
     * @return bool
     */
    function isEnabledExchangeApi(): bool
    {
        if (settings('exchange_enabled_api') != 'Disabled') {
            return settings('currency_exchange_rate')[settings('exchange_enabled_api')] ?? false;
        }
        return false;
    }
}

if (!function_exists('getApiCurrencyRate')) {
    /**
     * Get currency exchange rate from external api
     *
     * @return double
     */
    function getApiCurrencyRate($fromCurrencyCode, $toCurrencryCode)
    {
        $exchangeResolver = (new App\Resolvers\ExchangeApiResolver())->resolveService(settings('exchange_enabled_api'), $fromCurrencyCode, $toCurrencryCode);
        return $exchangeResolver->getCurrencyRate();
    }
}

if (!function_exists('getLocalCurrencyRate')) {
    /**
     * Get currency exchange rate based on local exchange rates
     *
     * @return double
     */
    function getLocalCurrencyRate($fromCurrencyRate, $toCurrencyRate)
    {
        $defaultCurrency = (new \App\Http\Helpers\Common)->getCurrencyObject(['default' => 1], ['rate']);
        return ($defaultCurrency->rate / $fromCurrencyRate) * $toCurrencyRate;
    }
}

if (!function_exists('setActionSession')) {
    /**
     * setActionSession
     * @return void
     */
    function setActionSession()
    {
        $key = time();
        $encryptedKey = Illuminate\Support\Facades\Crypt::encrypt($key);
        session(['action-session' => $encryptedKey]);
        session(['session-key' => $key]);
    }
}

if (!function_exists('actionSessionCheck')) {
    /**
     * actionSessionCheck
     * @return void
     */
    function actionSessionCheck()
    {
        if (! Illuminate\Support\Facades\Session::has('action-session')) {
            abort(404);
        }

        $key = session('session-key');
        $encryptedKey = session('action-session');

        if ($key != Illuminate\Support\Facades\Crypt::decrypt($encryptedKey)) {
            abort(404);
        }
    }
}

if (!function_exists('clearActionSession')) {
    /**
     * actionSessionCheck
     * @return void
     */
    function clearActionSession()
    {
        Illuminate\Support\Facades\Session::forget(['action-session', 'session-key']);
    }
}

if (!function_exists('getCurrencyIdOfTransaction')) {
    /**
     * getCurrencyIdOfTransaction
     * @param  array $transactions
     * @return array
     */
    function getCurrencyIdOfTransaction($transactions)
    {
        $currencies = [];
        foreach ($transactions as $transaction)
        {
            $currencies[] = $transaction->currency_id;
        }
        return $currencies;
    }
}

//fixed - for exchange rate - if set to 0 - which is unusual
function generateAmountBasedOnDfltCurrency($data, $currencyWithRate)
{
    $data_map = [];
    foreach ($data as $key => $value)
    {
        foreach ($currencyWithRate as $currencyRate)
        {
            if ($currencyRate->id == $value->currency_id)
            {
                if (!isset($data_map[$value->day][$value->month]))
                {
                    $data_map[$value->day][$value->month] = 0;
                }
                if ($value->currency_id != session('default_currency'))
                {
                    if ($currencyRate->rate != 0)
                    {
                        $data_map[$value->day][$value->month] += abs($value->amount / $currencyRate->rate);
                    }
                    else
                    {
                        $data_map[$value->day][$value->month] = 0;
                    }
                }
                else
                {
                    $data_map[$value->day][$value->month] += abs($value->amount);
                }
            }
        }
    }
    return $data_map;
}

//fixed - for exchange rate - if set to 0 - which is unusual
function generateAmountForTotal($data, $currencyWithRate)
{
    $final = 0;
    foreach ($data as $key => $value)
    {
        foreach ($currencyWithRate as $currencyRate)
        {
            if ($currencyRate->id == $value->currency_id)
            {
                if ($value->currency_id != session('default_currency'))
                {
                    if ($currencyRate->rate != 0)
                    {
                        $final += abs($value->total_charge / $currencyRate->rate);
                    }
                    else
                    {
                        // $data_map[$value->day][$value->month] = 0;
                        $final += 0;
                    }
                }
                else
                {
                    $final += abs($value->total_charge);
                }
            }
        }
    }
    return $final;
}

function checkAppMailEnvironment()
{
    return config('paymoney.mail', true);
}

if (!function_exists('isMailEnabled')) {
    /**
     * Check if APP_MAIL=true in .env file.
     *
     * @return bool
     */
    function isMailEnabled()
    {
        return config('paymoney.mail', true);
    }
}

if (!function_exists('isSmsEnabled')) {
    /**
     * Check if APP_SMS=true in .env file.
     *
     * @return bool
     */
    function isSmsEnabled()
    {
        return config('paymoney.sms', true);
    }
}

function checkAppSmsEnvironment()
{
    return config('paymoney.sms', true);
}

function checkDemoEnvironment()
{
    return config('paymoney.demo', true);
}

function getSmsConfigDetails()
{
    return \App\Models\SmsConfig::where(['status' => 'Active'])->first();
}

function sendSMSwithNexmo($nexmoCredentials, $to, $message)
{
    $trimmedMsg = trim(preg_replace('/\s\s+/', ' ', $message));
    $url        = 'https://rest.nexmo.com/sms/json?' . http_build_query([
        'api_key'    => '' . trim($nexmoCredentials['Key']) . '',
        'api_secret' => '' . trim($nexmoCredentials['Secret']) . '',
        'from'       => '' . $nexmoCredentials['default_nexmo_phone_number'] . '',
        'to'         => '' . $to . '',
        'text'       => '' . strip_tags($trimmedMsg) . '',
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
}

function sendSMSwithTwilio($twilioCredentials, $to, $message)
{
    $accountSID   = $twilioCredentials['account_sid'];
    $authToken    = $twilioCredentials['auth_token'];
    $twilioNumber = $twilioCredentials['default_twilio_phone_number'];
    $trimmedMsg   = trim(preg_replace('/\s\s+/', ' ', $message));

    $client = new \Twilio\Rest\Client($accountSID, $authToken);
    $client->messages->create(
        $to,
        array(
            'from' => $twilioNumber,
            'body' => strip_tags($trimmedMsg)
        )
    );
}

function sendSMS($to, $message)
{
    $smsConfig = getSmsConfigDetails();
    if (!empty($smsConfig)) {
        $smsCredentials = json_decode($smsConfig->credentials, true);
        if (count($smsCredentials) > 0) {
            if ($smsConfig->type == 'nexmo') {
                sendSMSwithNexmo($smsCredentials, $to, $message);
            }
            elseif ($smsConfig->type == 'twilio') {
                sendSMSwithTwilio($smsCredentials, $to, $message);
            }
        }
    }
}

if (!function_exists('otpCode6')) {
    /**
     * otpCode6
     * @return six digit otp code
     */
    function otpCode6()
    {
        return mt_rand(100000, 999999);
    }
}

function getBrowser($agent)
{
    $browserName = 'Unknown';
    $platform = 'Unknown';
    $version  = "";
    $userBrowser = '';

    if (preg_match('/linux/i', $agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $agent)) {
        $platform = 'windows';
    }

    $browsers = [
        'Edg' => 'Microsoft Edge',
        'MSIE' => 'Internet Explorer',
        'Trident' => 'Internet Explorer',
        'Chrome' => 'Google Chrome',
        'Firefox' => 'Mozilla Firefox',
        'Safari' => 'Apple Safari',
        'Opera Mini' => 'Opera Mini',
        'Opera' => 'Opera',
        'Netscape' => 'Netscape'
    ];

    foreach($browsers as $key => $value) {
        if (strpos($agent, $key) !== FALSE) {
            $browserName = $value;
            $userBrowser = $key;
            break;
        }
    }

    // finally get the correct version number
    $known = array('Version', $userBrowser, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        // we will have two since we are not using 'other' argument yet
        // see if version is before or after the name
        if (strripos($agent, "Version") < strripos($agent, $userBrowser)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version == "") {
        $version = "?";
    }

    return [
        'name'     => $browserName,
        'version'  => $version,
        'platform' => $platform,
    ];
}

function getBrowserFingerprint($user_id, $browser_fingerprint)
{
    $getBrowserFingerprint = App\Models\DeviceLog::where(['user_id' => $user_id, 'browser_fingerprint' => $browser_fingerprint])->first(['browser_fingerprint']);
    return $getBrowserFingerprint;
}

function coinPaymentInfo()
{
    $transInfo = session('transInfo');
    $cpm       = \App\Models\CurrencyPaymentMethod::where(['method_id' => $transInfo['payment_method'], 'currency_id' => $transInfo['currency_id']])->first(['method_data']);
    return json_decode($cpm->method_data);
}

function captchaCheck($enabledCaptcha, $key)
{
    if (isset($enabledCaptcha) && ($enabledCaptcha == 'login' || $enabledCaptcha == 'registration' || $enabledCaptcha == 'login_and_registration')) {
        \Illuminate\Support\Facades\Config::set([(($key == 'site_key') ? 'captcha.sitekey' : 'captcha.secret') => settings($key)]);
    }
}

function getLanguageDefault()
{
    return \App\Models\Language::where(['default' => '1'])->first(['id', 'short_name']);
}

function getDefaultCountry()
{
    return \App\Models\Country::where(['is_default' => 'yes'])->first()->short_name;
}

function phpDefaultTimeZones()
{
    $zones_array = array();
    $timestamp   = time();
    foreach (timezone_identifiers_list() as $key => $zone)
    {
        date_default_timezone_set($zone);
        $zones_array[$key]['zone']          = $zone;
        $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
    }
    return $zones_array;
}

function getAuthUserIdentity()
{
    $getAuthUserIdentity = \App\Models\DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'identity'])->first(['verification_type', 'status']);
    return $getAuthUserIdentity;
}

function getAuthUserAddress()
{
    $getAuthUserAddress = \App\Models\DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'address'])->first(['verification_type', 'status']);
    return $getAuthUserAddress;
}

function allowedDecimalPlaceMessage($decimalPosition)
{
    $message = "*Allowed upto " . $decimalPosition . " decimal places.";
    return $message;
}

function allowedImageDimension($width, $height, $panel = null)
{
    if ($panel == 'user')
    {
        $message = "*" . __('Recommended Dimension') . ": " . $width . " px * " . $height . " px";
    }
    else
    {
        $message = "*Recommended Dimension: " . $width . " px * " . $height . " px";
    }
    return $message;
}

/**
 * [CUSTOM AES-256 ENCRYPTION/DECRYPTION METHOD]
 * param  $action [encrypt/decrypt]
 * param  $string [string]
 */
function initAES256($action, $plaintext)
{
    $output   = '';
    $cipher   = "AES-256-CBC";
    $password = 'K8m26hzj22TtZxnzX96vmRAVTzPxNXRB';
    $key      = substr(hash('sha256', $password, true), 0, 32); // Must be exact 32 chars (256 bit)
                                                                // $ivlen    = openssl_cipher_iv_length($cipher);
                                                                // $iv       = openssl_random_pseudo_bytes($ivlen); // IV must be exact 16 chars (128 bit)
    $secretIv = 'UP4n2cr8Bwn83X4h';
    $iv       = substr(hash('sha256', $secretIv), 0, 16);
    if ($plaintext != '')
    {
        if ($action == 'encrypt')
        {
            $output = base64_encode(openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv));
        }
        if ($action == 'decrypt')
        {
            $output = openssl_decrypt(base64_decode($plaintext), $cipher, $key, OPENSSL_RAW_DATA, $iv);
        }
    }
    return $output;
}

function getFormatedCurrencyList($rates, $rateAmount)
{
    foreach ($rates as $coin => $coinDetails) {
        if ((INT) $coinDetails['is_fiat'] === 0) {
            if ($rates[$coin]['rate_btc'] != 0) {
                $rate = ($rateAmount / $rates[$coin]['rate_btc']);
            }
            else {
                $rate = $rateAmount;
            }
            $coins[] = [
                'name'     => $coinDetails['name'],
                'rate'     => number_format($rate, 8, '.', ''),
                'iso'      => $coin,
                'icon'     => 'https://www.coinpayments.net/images/coins/' . $coin . '.png',
                'selected' => $coin == 'BTC' ? true : false,
                'accepted' => $coinDetails['accepted'],
            ];
            $aliases[$coin] = $coinDetails['name'];
        }

        if ((INT) $coinDetails['is_fiat'] === 0 && $coinDetails['accepted'] == 1) {
            $renamedCoin = explode('.', $coin);

            $rate           = ($rateAmount / $rates[$coin]['rate_btc']);
            $coins_accept[] = [
                'name'     => $coinDetails['name'],
                'rate'     => number_format($rate, 8, '.', ''),
                'iso'      => $coin,
                'icon'     => 'https://www.coinpayments.net/images/coins/' . ((count($renamedCoin) > 1) ? $renamedCoin[0] : $coin)  . '.png',
                'selected' => $coin == 'BTC' ? true : false,
                'accepted' => $coinDetails['accepted'],
            ];
        }

        if ((INT) $coinDetails['is_fiat'] === 1) {
            $fiat[$coin] = $coinDetails;
        }
    }

    return ['coins' => $coins, 'coins_accept' => $coins_accept, 'fiat' => $fiat, 'aliases' => $aliases];
}
/**
 * [CUSTOM AES-256 ENCRYPTION/DECRYPTION METHOD]
 * param  $action [encrypt/decrypt]
 * param  $string [string]
 */
function convert_string($action, $string) {
    $output         = '';
    $encrypt_method = "AES-256-CBC";
    $secret_key     = 'XXD93D945143F656DD9094450F802743F5457551991C8CXX';
    $secret_iv      = 'XXE8327B11DA84769CB73FE4495C63XX';
    // hash
    $key                   = hash('sha256', $secret_key);
    $initialization_vector = substr(hash('sha256', $secret_iv), 0, 16);
    if ($string != '') {
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $initialization_vector);
            $output = base64_encode($output);
        } if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $initialization_vector);
        }
    }
    return $output;
}

if (!function_exists('getStatuses')) {

    /**
     * Return status, class, colors
     *
     * @return string
     */
    function getStatuses()
    {
        return [
            'success' => ['text' => __('Success'), 'label' => 'success', 'color' => 'green'],
            'pending' => ['text' => __('Pending'), 'label' => 'primary', 'color' => 'blue'],
            'refund' => ['text' => __('Refunded'), 'label' => 'warning', 'color' => 'orange'],
            'blocked' => ['text' => __('Cancelled'), 'label' => 'danger', 'color' => 'red'],
            'active' => ['text' => __('Active'), 'label' => 'success', 'color' => 'green'],
            'inactive' => ['text' => __('Inactive'), 'label' => 'danger', 'color' => 'red'],
            'suspended' => ['text' => __('Suspended'), 'label' => 'warning', 'color' => 'orange'],
            'open' => ['text' => __('Open'), 'label' => 'success', 'color' => 'green'],
            'in progress' => ['text' => __('In Progress'), 'label' => 'primary', 'color' => 'blue'],
            'hold' => ['text' => __('Hold'), 'label' => 'warning', 'color' => 'orange'],
            'closed' => ['text' => __('Closed'), 'label' => 'danger', 'color' => 'red'],
            'approved' => ['text' => __('Approved'), 'label' => 'success', 'color' => 'green'],
            'rejected' => ['text' => __('Rejected'), 'label' => 'danger', 'color' => 'red'],
            'solve' => ['text' => __('Solved'), 'label' => 'success', 'color' => 'green'],
            'moderation' => ['text' => __('Moderation'), 'label' => 'primary', 'color' => 'blue'],
            'disapproved' => ['text' => __('Disapproved'), 'label' => 'danger', 'color' => 'red'],
            'draft' => ['text' => __('Draft'), 'label' => 'default', 'color' => 'default'],
            'cancelled' => ['text' => __('Cancelled'), 'label' => 'danger', 'color' => 'red'],
            'completed' => ['text' => __('Completed'), 'label' => 'info', 'color' => 'blue'],
        ];
    }
}

if (!function_exists('getStatus')) {

    /**
     * Get transactions status by HTML Lable
     *
     * @param string $status
     *
     * @return HTMLString
     */
    function getStatus($status = null)
    {
        if (empty($status)) {
            return '';
        }
        $statuses = getStatuses();

        $status = strtolower($status);
        return $statuses[$status]['text'];
    }
}

if (!function_exists('getStatusLabel')) {

    /**
     * Get transactions status by HTML Lable
     *
     * @param string $status
     *
     * @return HTMLString
     */
    function getStatusLabel($status = null)
    {
        if (empty($status)) {
            return '';
        }
        $statuses = getStatuses();

        $status = strtolower($status);
        return '<span class="label label-' . $statuses[$status]['label'] . '">' . $statuses[$status]['text'] . '</span>';
    }
}

if (!function_exists('getStatusBadge')) {

    /**
     * Get transactions status by HTML Lable
     *
     * @param string $status
     *
     * @return HTMLString
     */
    function getStatusBadge($status = null)
    {
        if (empty($status)) {
            return '';
        }
        $statuses = getStatuses();

        $status = strtolower($status);
        return '<span class="badge badge-' . $statuses[$status]['label'] . '">' . $statuses[$status]['text'] . '</span>';
    }
}

if (!function_exists('getStatusText')) {

    /**
     * Get transactions status by HTML text
     *
     * @param string $status
     *
     * @return HTMLString
     */
    function getStatusText($status = null)
    {
        if (empty($status)) {
            return '';
        }
        $statuses = getStatuses();

        $status = strtolower($status);
        return '<span class="text text-' . $statuses[$status]['color'] . '">' . $statuses[$status]['text'] . '</span>';
    }
}

if (!function_exists('getStatusInputLabel')) {

    /**
     * Transaction edit sender-receiver Title Text
     *
     * @param string $type [TransactionTypeId like Deposit, Withdrawal]
     * @param string $userType [user - receiver]
     *
     * @return HtmlString
     */
    function getStatusInputLabel($type = null, $userType = null)
    {
        if (empty($type) || empty($userType)) {
            return '';
        }

        $transactionTypes = [
            Deposit => ['user' => 'User', 'receiver' => 'Receiver'],
            Exchange_From => ['user' => 'User', 'receiver' => 'Receiver'],
            Exchange_To => ['user' => 'User', 'receiver' => 'Receiver'],
            Withdrawal => ['user' => 'User', 'receiver' => 'Receiver'],
            Payment_Sent => ['user' => 'User', 'receiver' => 'Receiver'],
            Payment_Received => ['user' => 'User', 'receiver' => 'Receiver'],
            Transferred => ['user' => 'Paid By', 'receiver' => 'Paid To'],
            Received => ['user' => 'Paid By', 'receiver' => 'Paid To'],
            Request_Sent => ['user' => 'Request From', 'receiver' => 'Request To'],
            Request_Received => ['user' => 'Request From', 'receiver' => 'Request To']
        ];

        return '<label class="control-label col-sm-3 fw-bold text-end" for="'. $userType .'">' . $transactionTypes[$type][$userType] . '</label>';
    }
}

if (! function_exists('getPaymoneySettings')) {

    /**
     * Get Paymoney configurations info
     *
     * @param string $type
     *
     * @return array
     */
    function getPaymoneySettings($type = null)
    {
        if (empty($type)) {
            return false;
        }
        $array = [
            'transaction_types' => [
                'web' => [
                    'sent' => [Deposit, Transferred, Exchange_From, Exchange_To, Request_Sent, Withdrawal, Payment_Sent],
                    'received' => [Received, Request_Received, Payment_Received]
                ],
                'mobile' => [
                    'sent' => ['Deposit' => Deposit, 'Transferred' => Transferred, 'Exchange_From' => Exchange_From, 'Exchange_To' => Exchange_To, 'Request_Sent' => Request_Sent, 'Withdrawal' => Withdrawal, 'Payment_Sent' => Payment_Sent],
                    'received' => ['Received' => Received, 'Request_Received' => Request_Received, 'Payment_Received' => Payment_Received]
                ],

            ],
            'payment_methods' => [
                'web' => [
                    'all' => [Mts, Stripe, Paypal, PayUmoney, Bank, Coinpayments, Payeer, Crypto, Coinbase],
                    'deposit' => [Mts, Stripe, Paypal, PayUmoney, Bank, Coinpayments, Payeer, Coinbase],
                    'withdrawal' => [Paypal, Bank, Crypto],
                    'fiat' => [
                        'deposit' => [Mts, Stripe, Paypal, PayUmoney, Bank, Coinpayments, Payeer, Coinbase],
                        'withdrawal' => [Mts, Paypal, Bank],
                    ],
                    'crypto' => [
                        'deposit' => [Mts, Coinpayments, Coinbase],
                        'withdrawal' => [Mts, Crypto],
                    ]
                ],
                'mobile' => [
                    'all' => ['Stripe' => Stripe, 'Paypal' => Paypal, 'Bank' => Bank, 'Coinpayments' => Coinpayments, 'Crypto' => Crypto, 'Coinbase' => Coinbase],
                    'deposit' => ['Stripe' => Stripe,'Paypal' => Paypal, 'Bank' => Bank, 'Coinpayments' => Coinpayments, 'Coinbase' => Coinbase],
                    'withdrawal' => ['Paypal' => Paypal, 'Bank' => Bank, 'Crypto' => Crypto],
                    'fiat' => [
                        'deposit' => ['Stripe' => Stripe, 'Paypal' => Paypal, 'Bank' => Bank, 'Coinbase' => Coinbase, 'Coinpayments' => Coinpayments],
                        'withdrawal' => ['Paypal' => Paypal, 'Bank' => Bank],
                    ],
                    'crypto' => [
                        'deposit' => ['Coinpayments' => Coinpayments, 'Coinbase' => Coinbase],
                        'withdrawal' => ['Crypto' => Crypto],
                    ]
                ]
            ]
        ];

        // Module Transaction Types
        $modules = getAllModules();
        foreach ($modules as $module) {
            if (!empty(config($module->get('alias') . '.transaction_type_settings'))) {
                // Web
                $array['transaction_types']['web']['sent'] = array_merge($array['transaction_types']['web']['sent'], config($module->get('alias') . '.transaction_type_settings')['web']['sent']);
                $array['transaction_types']['web']['received'] = array_merge($array['transaction_types']['web']['received'], config($module->get('alias') . '.transaction_type_settings')['web']['received']);

                // Mobile
                $array['transaction_types']['mobile']['sent'] = array_merge($array['transaction_types']['mobile']['sent'], config($module->get('alias') . '.transaction_type_settings')['mobile']['sent']);
                $array['transaction_types']['mobile']['received'] = array_merge($array['transaction_types']['mobile']['received'], config($module->get('alias') . '.transaction_type_settings')['mobile']['received']);
            }
        }

        // Mobile MOney - Payment method
        if (config('mobilemoney.is_active')) {
            if (defined('MobileMoney')) {
                $array['payment_methods']['web']['all'][] = MobileMoney;
                $array['payment_methods']['web']['deposit'][] = MobileMoney;
                $array['payment_methods']['web']['withdrawal'][] = MobileMoney;
                $array['payment_methods']['web']['fiat']['deposit'][] = MobileMoney;
                $array['payment_methods']['web']['fiat']['withdrawal'][] = MobileMoney;
            }
        }

        // Transaction Types
        $array['transaction_types']['web']['all'] = array_merge($array['transaction_types']['web']['sent'], $array['transaction_types']['web']['received']);
        $array['transaction_types']['mobile']['all'] = array_merge($array['transaction_types']['mobile']['sent'], $array['transaction_types']['mobile']['received']);

        return $array[$type];
    }
}

if (!function_exists('preference')) {

    /**
     * Get preference values
     *
     * @param string $field [return specific value]
     * @param string $default [take default value as optional if not provide]
     *
     * @return void
     */
    function preference($field = null, $default = null)
    {
        $preference = new App\Models\Preference();

        if (is_null($field)) {
            return $preference->getAll()->pluck('value', 'field')->toArray();
        }

        $value = $default;
        $preferences = $preference->getAll()->pluck('value', 'field')->toArray();

        if (array_key_exists($field, $preferences)) {
            $value = $preferences[$field];
        }

        return $value;
    }
}

if (!function_exists('settings')) {

    /**
     * Get settings values
     *
     * @param string $field [return specific value, if don't match provide type values]
     *
     * @return string
     * @return array
     */
    function settings($field = null)
    {
        $setting = new App\Models\Setting();

        if (is_null($field)) {
            return $setting->getAll()->pluck('value', 'name')->toArray();
        }

        $settings = $setting->getAll()->pluck('value', 'name')->toArray();

        if (array_key_exists($field, $settings)) {
            $result = $settings[$field];
        } else {
            $result = $setting->getAll()->where('type', $field)->pluck('value', 'name')->toArray();
        }

        return $result;
    }
}

if (!function_exists('isDefault')) {

    /**
     * Get is_default status by HTML Label
     *
     * @param string $status
     *
     * @return HTMLString
     */
    function isDefault($status = null)
    {
        if (empty($status)) {
            return '';
        }
        $statuses = [
            'yes' => ['text' => __('Yes'), 'label' => 'success', 'color' => 'green'],
            'no' => ['text' => __('No'), 'label' => 'danger', 'color' => 'red']
        ];

        $status = strtolower($status);
        return '<span class="label label-' . $statuses[$status]['label'] . '">' . $statuses[$status]['text'] . '</span>';
    }
}

function dataTableOptions(array $options = [])
{
    $default = [
        'order' => [[0, 'desc']],
        'pageLength' => preference('row_per_page'),
        'language' => preference('language'),
    ];

    return array_merge($default, $options);
}

if (!function_exists('templateHeaderText')) {

    /**
     * Get Email or sms template header text
     *
     * @param string $heading
     *
     * @return String
     */
    function templateHeaderText($heading)
    {
        $heading = str_replace('!', '', $heading);

        if (str_contains($heading, 'Notice of ')) {
            $heading = str_replace('Notice of ', '', $heading);
        } else if (str_contains($heading, 'Notice for')) {
            $heading = str_replace('Notice for ', '', $heading);
        } else if (str_contains($heading, 'Notice to')) {
            $heading = str_replace('Notice to ', '', $heading);
        }

        return __('Compose :x Template', ['x' => $heading]);
    }
}

if (!function_exists('n_as_k_c')) {
    function n_as_k_c() {
        if(!g_e_v()) {
            return true;
        }
        if(!g_c_v()) {
            try {
                $d_ = g_d();
                $e_ = g_e_v();
                $e_ = explode('.', $e_);
                $c_ = md5($d_ . $e_[1]);
                if($e_[0] == $c_) {
                    p_c_v();
                    return false;
                } else {
                    return true;
                }
            } catch(\Exception $e) {
                return true;
            }
        }
        return false;
    }

}

if (!function_exists('xss_clean')) {

    function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        return $data;
    }
}

if (!function_exists('stripBeforeSave')) {
    /**
     * stripBeforeSave method
     * This function strips or skips HTML tags
     *
     * @param string $string [The text that will be stripped]
     * @param array $options
     *
     * @return string
     */
    function stripBeforeSave($string = null, $options = ['skipAllTags' => true, 'mergeTags' => false])
    {
        $finalString = [];
        if ($options['skipAllTags'] === false) {
            $allow = '<h1><h2><h3><h4><h5><h6><p><b><br><hr><i><pre><small><strike><strong><sub><sup><time><u><form><input><textarea><button><select><option><label><frame><iframe><img><audio><video><a><link><nav><ul><ol><li><table><th><tr><td><thead><tbody><div><span><header><footer><main><section><article>';
            if (isset($options['mergeTags']) && $options['mergeTags'] === true && isset($options['allowedTags'])) {
                $allow .= is_array($options['allowedTags']) ? implode('', $options['allowedTags']) : trim($options['allowedTags']);
            } else {
                $allow = isset($options['allowedTags']) && is_array($options['allowedTags']) ? implode('', $options['allowedTags']) : trim($options['allowedTags']);
            }
            if (is_array($string)) {
                foreach ($string as $key => $value) {
                    $finalString[$key] = strip_tags($value, $allow);
                }
            } else {
                $finalString = strip_tags($string, $allow);
            }
        } else {
            if (is_array($string)) {
                foreach ($string as $key => $value) {
                    $finalString[$key] = strip_tags($value);
                }
            } else {
                $finalString = strip_tags($string);
            }
        }
        return !empty($finalString) ? $finalString : null;
    }
}

if (!function_exists('getTransactionTypes')) {

    function getTransactionTypes($type = null)
    {
        $transactionTypes = [];

        $fetchTransctionTypes =  \App\Models\TransactionType::get();
        foreach ($fetchTransctionTypes as $value) {
            $transactionTypes[strtolower($value->name)] = $value->name;
        }

        if ($type) {
            return $transactionTypes[$type];
        }

        return $transactionTypes;
    }
}

if (!function_exists('getColumnValue')) {

    function getColumnValue($object, $columnOne = 'first_name', $columnTwo = 'last_name', $emptyReturn = '-')
    {
        $name = [];
        if (optional($object)->{$columnOne}) {
            $name[] = $object->{$columnOne};
        }
        if (optional($object)->{$columnTwo}) {
            $name[] = $object->{$columnTwo};
        }

        return count($name) ? implode(' ', $name) : $emptyReturn;
    }
}

if (!function_exists('getStripeMonths')) {

    function getStripeMonths()
    {
        $number = range(1, 12);
        $data = [];
        foreach ($number as $num) {
            $value = sprintf('%02d', $num);

            $data[] = '<option value="' . $value . '">' . $value . '</option>';
        }

        return implode(' ', $data);
    }
}

if (!function_exists('currencyTransactionTypes')) {
    function currencyTransactionTypes($transactionType)
    {
        $activateFor = [];
        $transactionType = request()->ajax() ? json_decode($transactionType) : $transactionType;
        if (!empty($transactionType)) {
            foreach ($transactionType as $key => $value) {
                $activateFor[$value] = '';
            }
        } else {
            $activateFor = ['' => ''];
        }

        return $activateFor;
    }
}


if (!function_exists('miniCollection')) {

    /**
     * Returns a new \App\Lib\MiniCollection object
     * @param array $hayStack optional
     * @return \App\Lib\MiniCollection;
     */
    function miniCollection($hayStack = [], $nested = false)
    {
        return new \App\Http\Helpers\MiniCollection($hayStack, $nested);
    }
}

if (!function_exists('getColor')) {
    /**
     * getColor
     *
     * @param  mixed $status
     * @return string
     */
    function getColor($status): string
    {
        switch ($status) {
            case 'Success':
            case 'Approved':
            case 'Open':
            Case 'Solve':
                return 'text-success';
                break;
            case 'Pending':
            case 'Moderation':
            case 'Refunded':
            case 'Refund':
                return 'text-warning';
                break;
            case 'Cancelled':
            case 'Disapproved':
            case 'Closed':
            case 'Blocked':
            case 'Rejected':
                return 'text-danger';
                break;

            default:
                return '5E';
                break;
        }
    }
}

if (!function_exists('getBgColor')) {
    /**
     * getColor
     *
     * @param  mixed $status
     * @return string
     */
    function getBgColor($status): string
    {
        switch (ucfirst($status)) {
            case 'Moderation':
            case 'Pending':
            case 'Hold':
                return 'warning';
                break;
            case 'Closed':
            case 'Disapproved':
            case 'Cancelled':
                return 'danger';
                break;
            case 'Approved':
            case 'Active':
            case 'Open':
            case 'Solved':
                return 'success';
                break;
            case 'In Progress':
            case 'Completed':
                return 'primary';
                break;
            default:
                return 'A5E';
                break;
        }
    }
}

function cryptoApiLogDetails($transaction)
{
    if (module('BlockIo') && !empty($transaction->cryptoAssetApiLog)) {
        $getCryptoDetails = (new \Modules\BlockIo\Classes\BlockIo)->getCryptoPayloadConfirmationsDetails($transaction->transaction_type_id, $transaction->cryptoAssetApiLog->payload, $transaction->cryptoAssetApiLog->confirmations);
        if (count($getCryptoDetails) > 0)  {
            if (isset($getCryptoDetails['senderAddress'])) {
                $data['senderAddress'] = $getCryptoDetails['senderAddress'];
            }
            if (isset($getCryptoDetails['receiverAddress'])) {
                $data['receiverAddress'] = $getCryptoDetails['receiverAddress'];
            }
            $data['network_fee'] = isset($getCryptoDetails['network_fee']) ? formatNumber($getCryptoDetails['network_fee'], $transaction->currency_id) : 0.00000000;

            $data['confirmations'] = $getCryptoDetails['confirmations'];

            return $data;
        }
    }
}

if (!function_exists('getTransactionInfo')) {
    /**
     * getTransactionInfo
     *
     * @param  mixed $type
     * @return array
     */
    function getTransactionInfo($type, $transaction = null): array
    {
        switch ($type) {
            case 'Deposit':
                return [
                    'name' => 'Deposited',
                    'type' => 'Deposited By',
                    'user' => getColumnValue($transaction?->user),
                    'currency' => 'Currency',
                    'print' => 'user.deposit.print'
                ];
                break;
            case 'Withdrawal':
                return [
                    'name' => 'Withdrawal',
                    'type' => 'Withdrawal By',
                    'user' => getColumnValue($transaction?->user),
                    'currency' => 'Currency',
                    'print' => 'user.withdrawal.print'
                ];
                break;
            case 'Transferred':
                return [
                    'name' => 'Transferred',
                    'type' => 'Transferred To',
                    'user' => ($transaction?->user_type == 'unregistered') ? (($transaction?->email) ? $transaction?->email : $transaction?->phone) : getColumnValue($transaction?->end_user),
                    'currency' => 'Currency',
                    'print' => 'user.send_money.print'

                ];
                break;
            case 'Received':
                return [
                    'name' => 'Received',
                    'type' => 'Received From',
                    'user' => getColumnValue($transaction?->end_user),
                    'currency' => 'Currency',
                    'print' => 'user.send_money.print'
                ];
                break;
            case 'Exchange_From':
                return [
                    'name' => 'Exchanged',
                    'type' => 'Exchanged By',
                    'user' => getColumnValue($transaction?->user),
                    'currency' => 'Exchanged From',
                    'print' => 'user.exchange_transaction.print'
                ];
                break;
            case 'Exchange_To':
                return [
                    'name' => 'Exchanged',
                    'type' => 'Exchanged By',
                    'user' => getColumnValue($transaction?->user),
                    'currency' => 'Exchanged To',
                    'print' => 'user.exchange_transaction.print'
                ];
                break;
            case 'Request_Sent':
                return [
                    'name' => 'Requested',
                    'type' => 'Requested To',
                    'user' => ($transaction?->user_type == 'unregistered') ? (($transaction?->email) ? $transaction?->email : $transaction?->phone) : getColumnValue($transaction?->end_user),
                    'currency' => 'Currency',
                    'print' => 'user.request_money.print'
                ];
                break;
            case 'Request_Received':
                return [
                    'name' => 'Requested',
                    'type' => 'Requested From',
                    'user' => getColumnValue($transaction?->end_user),
                    'currency' => 'Currency',
                    'print' => 'user.request_money.print'
                ];
                break;
            case 'Payment_Sent':
                return [
                    'name' => 'Payment',
                    'type' => 'Business Name',
                    'user' => $transaction?->merchant?->business_name,
                    'currency' => 'Currency',
                    'print' => 'user.merchant_payment.print'
                ];
                break;
            case 'Payment_Received':
                return [
                    'name' => 'Payment',
                    'type' => 'Payment By',
                    'user' => getColumnValue($transaction?->end_user),
                    'currency' => 'Currency',
                    'print' => 'user.merchant_payment.print'
                ];
                break;
            case 'Crypto_Sent':
                return [
                    'name' => 'Crypto Sent',
                    'type' => 'Receiver',
                    'user' => getColumnValue($transaction?->end_user),
                    'currency' => 'Currency',
                    'print' => 'user.crypto_send_receive.print'
                ];
                break;
            case 'Crypto_Received':
                return [
                    'name' => 'Received',
                    'type' => 'Sender',
                    'user' => getColumnValue($transaction?->end_user),
                    'currency' => 'Currency',
                    'print' => 'user.crypto_send_receive.print'
                ];
                break;

            case 'Referral_Award':
                return [
                    'name' => 'Award',
                    'type' => 'Awarded User',
                    'user' => getColumnValue($transaction?->user),
                    'currency' => 'Currency',
                    'print' => 'user.transactions.referral_award_print'
                ];
                break;

            default:
                return [
                    'name' => 'Transaction',
                    'type' => 'Transaction By',
                    'user' => getColumnValue($transaction?->user),
                    'currency' => 'Currency',
                    'print' => 'user.transactions.print'
                ];
                break;
        }
    }
}

if (!function_exists('getTransactionPaymentMethod')) {
    /**
     * getTransactionPaymentMethod
     *
     * @param  mixed $methodName
     * @return string
     */
    function getTransactionPaymentMethod(?string $methodName): ?string
    {
        return ('Mts' == $methodName) ? settings('name') : $methodName;
    }
}

if (!function_exists('generateQrcode')) {
    /**
     * generateQrcode
     *
     * @param  mixed $secret
     * @param  mixed $size
     * @return void
     */
    function generateQrcode(string $secret, string $size = '200x200')
    {
        return !empty($secret) ? 'https://api.qrserver.com/v1/create-qr-code/?data='. $secret .'&size='. $size : '';
    }
}

if (! function_exists('payment_option')) {
    /**
     * payment_option
     *
     * @param  mixed $method
     * @return void
     */
    function payment_option($method)
    {
        $attributes = [
            'data-obj' => json_encode($method->getAttributes()),
            'value' => $method->id,
            'data-type' => $method->type,
        ];

        switch ($method->type) {
            case Paypal:
                $text = $method->paymentMethod->name . ' (' . $method->email . ')';
                break;
            case Bank:
                $text = $method->paymentMethod->name . ' (' . $method->account_name . ')';
                break;
            case Crypto:
                $text = $method->paymentMethod->name . ' (' . $method->currency->code . ' - ' . $method->crypto_address . ')';
                break;
            case MobileMoney:
                if (config('mobilemoney.is_active')) {
                    $text = $method->paymentMethod->name . ' (' . ($method->mobilemoney->mobilemoney_name ?? '') . ' ****' . substr($method->mobile_number, -4) . ')';
                } else {
                    $text = $method->paymentMethod->name . ' (' . $method->account_number . ')';
                }
                break;
            default:
                $text = $method->paymentMethod->name . ' (' . $method->account_number . ')';
                break;
        }

        $attributes_str = implode(' ', array_map(function ($key, $value) {
            return $key . '="' . htmlspecialchars($value) . '"';
        }, array_keys($attributes), $attributes));

        $isSelected = '';

        if (old('withdrawal_method_id') && old('withdrawal_method_id') == $method->id) {
            $isSelected = 'selected="selected"';
        } elseif (!empty(session('withdrawalData')) && session('withdrawalData')['withdrawal_method_id'] == $method->id) {
            $isSelected = 'selected="selected"';
        }

        return '<option ' . $attributes_str. $isSelected . '>' . htmlspecialchars($text) . '</option>';
    }
}

if (! function_exists('getPaymentDetails')) {
    /**
     * getPaymentDetails
     *
     * @param  mixed $payout
     * @return void
     */
    function getPaymentDetails($payout)
    {
        if ($payout->payment_method->name == "Bank") {
            if ($payout->withdrawal_detail) {
                return $payout->withdrawal_detail->account_name;
            } else {
                return '-';
            }
        } elseif ($payout->payment_method->name == "Mts") {
            return settings('name');
        } else {
            return $payout->payment_method_info;
        }
    }
}

if (!function_exists('generateOptions')) {
    /**
     * Return all options
     *
     * @param array $options
     * @param null $selected
     * @param bool $optionKey
     * @param string $extraAttr
     *
     * @return string
     */
    function generateOptions($options = [], $selected = null, $optionKey = false, $extraAttr = '')
    {
        if (empty($options)) {
            return '';
        }

        $data = [];
        foreach ($options as $key => $value) {
            $val = $optionKey ? $key : $value;

            if (is_array($selected)) {
                $data[] = '<option ' . $extraAttr . ' value="' . $val . '" ' . (in_array($val, $selected) ? 'selected' : '') . '>' . __($value) . '</option>';
            } else {
                $data[] = '<option ' . $extraAttr . ' value="' . $val . '" ' . ($selected == $val ? 'selected' : '') . '>' . __($value) . '</option>';
            }
        }

        return implode(' ', $data);
    }
}

if (! function_exists('replaceUnderscoresWithSpaces')) {
    /**
     * Replace underscores with spaces in a string.
     *
     * @param string $string
     * @return string
     */
    function replaceUnderscoresWithSpaces(string $string): string
    {
        return str_replace('_', ' ', $string);
    }
}

if (! function_exists('getRecipientFromNotificationSetting')) {
    /**
     * get recipient email/SMS from notification settings for sending mail/SMS
     *
     * @param array $string
     * @return array
     */
    function getRecipientFromNotificationSetting(array $options): array
    {
        $emailSetting = \App\Models\NotificationSetting::getSettings(['nt.alias' => $options['type'], 'notification_settings.recipient_type' => $options['medium'], 'notification_settings.status' => 'Yes']);

            if ($emailSetting->isNotEmpty()) {
                $email = $emailSetting[0]['recipient'];
                $admin = \App\Models\Admin::where('email', $email)->first(['id', 'first_name', 'last_name']);
                if (!is_null($admin)) {
                    $admin = getColumnValue($admin);
                }
            } else {
                $admin = \App\Models\Admin::first(['id', 'first_name', 'last_name', 'email']);
                $email = $admin->email;

                if (!is_null($admin)) {
                    $admin = getColumnValue($admin);
                }
            }

        return ['name' => $admin, 'email' => $email];
    }
}

function calculateFee($transaction)
{
    return $transaction->charge_percentage + $transaction->charge_fixed;
}

function getFormatFee($transaction)
{
    return formatNumber(calculateFee($transaction), $transaction->currency_id);
}

function getmoneyFormatFee($transaction)
{
    return moneyFormat($transaction->currency?->symbol, getFormatFee($transaction));
}

function getPaymentMethodInfo($withdrawal)
{
    if (Withdrawal::isBankPaymentMethod($withdrawal)) {
        return Withdrawal::getBankPaymentMethodInfo($withdrawal);
    } else {
        return Withdrawal::getDefaultPaymentMethodInfo($withdrawal);
    }
}

if (!function_exists('setPaymentData')) {
    function setPaymentData($data)
    {
        session(['paymentData' => $data]);
    }
}

if (!function_exists('getPaymentData')) {

    function getPaymentData($forget=null)
    {
        $data = Session::get('paymentData');

        if (is_null($data)) {
            throw new Exception(__('Payment Session has been ended, please try again'));
        }

        if ($forget == 'forget') {
            session()->forget(['paymentData']);
        }

        return $data;
    }
}

if (!function_exists('getBankList')) {

    function getBankList($currencyId, $paymentType)
    {
        $banks = Bank::where(['currency_id' => $currencyId])->get(['id', 'bank_name', 'is_default', 'account_name', 'account_number']);
        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $currencyId)
            ->where('activated_for', 'like', '%'. $paymentType .'%')
            ->where('method_data', 'like', "%bank_id%")
            ->get(['method_data']);

        $bankList = bankList($banks, $currencyPaymentMethods);

        if (count($bankList) == 0) {
            throw new DepositMoneyException(__("Banks does not exist for selected currency."));
        }
        return $bankList;
    }

 }

 if (!function_exists('bankList')) {

    function bankList($banks, $currencyPaymentMethods)
    {
        $selectedBanks = [];
        foreach ($banks as $bank) {
            foreach ($currencyPaymentMethods as $cpm) {
                if (!empty($cpm->method_data)) {
                    $methodData = json_decode($cpm->method_data);
                }
                if (isset($methodData->bank_id) && $bank->id == $methodData->bank_id) {
                    $selectedBanks[] = [
                        'id' => $bank->id,
                        'bank_name' => $bank->bank_name,
                        'is_default' => $bank->is_default,
                        'account_name' => $bank->account_name,
                        'account_number' => $bank->account_number,
                    ];
                }
            }
        }
        return $selectedBanks;
    }
}

if (!function_exists('setPaymentParam')) {

    function setPaymentParam($data)
    {
        $encodedData = urlencode(base64_encode((json_encode($data))));
        $uniqueCode = $data['uuid'];

        Parameter::create([
            'unique_code' => $uniqueCode,
            'parameter' => $encodedData,
        ]);

        return $uniqueCode;

    }
}

if (!function_exists('getPaymentParam')) {

    function getPaymentParam($param)
    {
        $encodeData = Parameter::where('unique_code', $param)->first();

        if ($encodeData) {
            $data = json_decode(base64_decode(urldecode($encodeData->parameter)), true);
            isTransactionExist($data['uuid']);
            return $data;
        } else {
            throw new Exception(__('Payment Url not valid'));
        }

    }
}

if (!function_exists('gatewayPaymentUrl')) {
    function gatewayPaymentUrl($data) {

        return route('gateway.pay', ['params' => setPaymentParam($data), 'gateway' => $data['gateway']]);
    }
}


 if (!function_exists('callAPI')) {

    function callAPI($method, $url, $data)
    {
        $response = null;

        switch ($method) {
            case "POST":
                $response = Http::post($url, $data);
                break;
            case "PUT":
                $response = Http::put($url, $data);
                break;
            default:
                // For other methods (default to GET)
                $response = Http::get($url, $data);
        }

        if ($response->failed()) {
            // Handle connection failure or other errors
            return null;
        }

        return $response->body();
    }

 }

 if (!function_exists('isTransactionExist')) {

    function isTransactionExist($uuid) {

        $transaction = Transaction::where(['uuid' => $uuid, 'status' => 'Success'])->first();

        if ( !is_null($transaction) ) {
            throw new Exception(__('Transaction already exist, please try again'));
        }

    }
}


if (!function_exists('isGatewayValidMethod')) {

    function isGatewayValidMethod($gateway) {

        $allowedPaymentMethods = [Stripe, Paypal, Coinbase, Coinpayments, PayUmoney, Payeer];

        if (in_array($gateway, $allowedPaymentMethods) && (!isset(request()->execute) || request()->execute !== 'api')) {
            throw new Exception(__('Payment Failed, please try again'));
        }

        if ($gateway == Bank && (!isset(request()->bank) || !isset(request()->attachment)) ) {
            throw new Exception(__('Payment Details not available'));
        }

    }
}
