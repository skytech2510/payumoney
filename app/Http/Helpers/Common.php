<?php
namespace App\Http\Helpers;

use App\Http\Controllers\Users\EmailController;
use App\Models\NotificationSetting;
use Session, Config, Exception;
use App\Exceptions\Api\V2\{
    AmountLimitException,
    WalletException,
    FeesException
};
use App\Models\{PermissionRole,
    PayoutSetting,
    EmailTemplate,
    Permission,
    FeesLimit,
    Currency,
    RoleUser,
    QrCode,
    Wallet,
    User
};


class Common
{
    public function __construct()
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $this->email = new EmailController();
    }

    public static function one_time_message($class, $message)
    {
        if ($class == 'error')
        {
            $class = 'danger';
        }
        Session::flash('alert-class', 'alert-' . $class);
        Session::flash('message', $message);
    }

    // key_value [key,value, collection]
    public static function key_value($key, $value, $collection)
    {
        $return_value = [];
        foreach ($collection as $row)
        {
            $return_value[$row[$key]] = $row[$value];
        }
        return $return_value;
    }

    /*
     * @param $user_id
     * @param $permissions
     * @static has_permission
     */
    public static function has_permission($user_id, $permissions = '')
    {
        $permissions = explode('|', $permissions);

        $permission_id = [];
        $i             = 0;

        $prefix = str_replace('/', '', request()->route()->getPrefix());
        if ($prefix == config('adminPrefix'))
        {
            $user_type = 'Admin';
        }
        else
        {
            $user_type = 'User';
        }

        $userPermissions = Permission::whereIn('name', $permissions)->get(['id']);
        foreach ($userPermissions as $value)
        {
            $permission_id[$i++] = $value->id;
        }
        $role = RoleUser::where(['user_id' => $user_id, 'user_type' => $user_type])->first(['role_id']);
        if (count($permission_id) && isset($role->role_id))
        {
            $has_permit = PermissionRole::where('role_id', $role->role_id)->whereIn('permission_id', $permission_id);
            return $has_permit->count();
        }
        else
        {
            return 0;
        }
    }

    /**
     * Undocumented function
     *
     * @param  [type] $host
     * @param  [type] $user
     * @param  [type] $pass
     * @param  [type] $name
     * @param  string $tables
     * @return void
     */
    public function backup_tables($host, $user, $pass, $name, $tables = '*')
    {
        try {
            $con = mysqli_connect($host, $user, $pass, $name);
        }
        catch (Exception $e)
        {
            print_r($e->getMessage());
        }

        if (mysqli_connect_errno())
        {
            $this->one_time_message('danger', "Failed to connect to MySQL: " . mysqli_connect_error());
            return 0;
        }

        $con->set_charset("utf8mb4");

        if ($tables == '*')
        {
            $tables = array();
            $result = mysqli_query($con, 'SHOW TABLES');
            while ($row = mysqli_fetch_row($result))
            {
                $tables[] = $row[0];
            }
        }
        else
        {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        $return = '';
        foreach ($tables as $table)
        {
            $result     = mysqli_query($con, 'SELECT * FROM ' . $table);
            $num_fields = mysqli_num_fields($result);

            $row2 = mysqli_fetch_row(mysqli_query($con, 'SHOW CREATE TABLE ' . $table));
            $return .= "\n\n" . str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $row2[1]) . ";\n\n";

            for ($i = 0; $i < $num_fields; $i++)
            {
                while ($row = mysqli_fetch_row($result))
                {
                    $return .= 'INSERT INTO ' . $table . ' VALUES(';
                    for ($j = 0; $j < $num_fields; $j++)
                    {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
                        if (isset($row[$j]))
                        {
                            $return .= '"' . $row[$j] . '"';
                        }
                        else
                        {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1))
                        {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
            }

            $return .= "\n\n\n";
        }

        $backup_name = date('Y-m-d-His') . '.sql';

        $directoryPath = public_path("uploads/db-backups");

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, config('paymoney.file_permission'), true);
        }

        $handle = fopen($directoryPath . '/' . $backup_name, 'w+');
        fwrite($handle, $return);
        fclose($handle);

        return $backup_name;
    }

    //  Check user status
    public function getUserStatus($userStatus)
    {
        if ($userStatus == 'Suspended')
        {
            return 'Suspended';
        }
        elseif ($userStatus == 'Inactive')
        {
            return 'Inactive';
        }
    }

    public function checkWalletBalanceAgainstAmount($totalWithFee, $currency_id, $user_id)
    {
        //Backend Validation - Wallet Balance Again Amount Check - Starts here
        $wallet = Wallet::where(['currency_id' => $currency_id, 'user_id' => $user_id])->first(['id', 'balance']);
        if (!empty($wallet))
        {
            if (($totalWithFee > $wallet->balance) || ($wallet->balance < 0))
            {
                return true;
            }
        }
        //Backend Validation - Wallet Balance Again Amount Check - Ends here
    }

    /**
     * [Get Current Date & Time - Carbon]
     * return [string] [Cardbon Date Time]
     */
    public function getCurrentDateTime()
    {
        return dateFormat(now());
    }

    public function clearSessionWithRedirect($sessionArr, $exception, $path)
    {
        Session::forget($sessionArr);
        clearActionSession();
        $this->one_time_message('error', $exception->getMessage());
        return redirect($path);
    }

    public function returnUnauthorizedResponse($unauthorisedStatus, $exception)
    {
        $success            = [];
        $success['status']  = $unauthorisedStatus;
        $success['message'] = $exception->getMessage();
        return response()->json(['success' => $success], $unauthorisedStatus);
    }

    public function validateEmailInput($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public function validatePhoneInput($value)
    {
        return preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $value);
    }

    public function getEmailPhoneValidatedUserInfo($emailFilterValidate, $phoneRegex, $emailOrPhone)
    {
        $selectOptions = ['id', 'first_name', 'last_name', 'email', 'carrierCode', 'phone', 'picture'];
        if ($emailFilterValidate)
        {
            $userInfo = User::where(['email' => $emailOrPhone])->first($selectOptions);
        }
        elseif ($phoneRegex)
        {
            $userInfo = User::where(['formattedPhone' => $emailOrPhone])->first($selectOptions);
        }
        return $userInfo;
    }

    /**
     * fetch Deposit Active Fees Limit
     * @param array $withOptions Data needs to be fetched with lazy loading
     * @param  int $transactionType Transaction type id
     * @param  int $currencyId Currency Id
     * @param  int $paymentMethodId Payment method id
     * @param  array $options
     * @return object|null
     */
    public function getFeesLimitObject($withOptions = [], $transactionType, $currencyId, $paymentMethodId, $hasTransaction, $options)
    {
        return FeesLimit::with($withOptions)
            ->where('transaction_type_id', $transactionType)
            ->where('currency_id', $currencyId)
            ->when(!is_null($hasTransaction), function ($query) use ($hasTransaction) {
                return $query->where('has_transaction', $hasTransaction);
            })
            ->when(!is_null($paymentMethodId), function ($query) use ($paymentMethodId) {
                return $query->where('payment_method_id', $paymentMethodId);
            })
            ->first($options);
    }

    /**
     * Get Wallet Object
     * param  array  $withOptions   [eagar loaded relations]
     * param  array $constraints   [where or other conditions]
     * param  array $selectOptions [specific fields]
     * return object
     */
    public function getUserWallet($withOptions = [], $constraints, $selectOptions)
    {
        return Wallet::with($withOptions)->where($constraints)->first($selectOptions);
    }

    /**
     * Get All Wallets
     * param  array  $withOptions   [eagar loaded relations]
     * param  array $constraints   [where or other conditions]
     * param  array $selectOptions [specific fields]
     * return collection
     */
    public function getUserWallets($withOptions = [], $constraints, $selectOptions)
    {
        return Wallet::with($withOptions)->where($constraints)->get($selectOptions);
    }

    /**
     * Get Currency
     * @param  array    $constraints
     * @param  array    $selectOptions
     * @return Object
     */
    public function getCurrencyObject($constraints, $selectOptions)
    {
        return Currency::where($constraints)->first($selectOptions);
    }

    /**
     * Get Payout Setting
     * @param  array    $constraints
     * @param  array    $selectOptions
     * @return Object
     */
    public function getPayoutSettingObject($withOptions = [], $constraints, $selectOptions)
    {
        return PayoutSetting::with($withOptions)->where($constraints)->first($selectOptions);
    }

    /**
    * [It will print QR code for express, standard merchant, customer profile]
    * @param  [type] $id             [Can be merchant ID or User ID]
    * @param  [type] $objectType     [standard_merchant, express_merchant]
    * @return [type] [description]
    */
    public function printQrCode($id, $objectType)
    {
        $data['qrCode'] = $qrCode = QrCode::where(['object_id' => $id, 'object_type' => $objectType, 'status' => 'Active'])->first(['qr_image']);
        if (empty($qrCode)) {
            $this->one_time_message('error', __('The :x does not exist.', ['x' => __('QR code')]));
            return redirect('merchants');
        }

        $data['QrCodeSecret'] = urlencode($qrCode->secret);

        if ($objectType == 'standard_merchant' || $objectType == 'express_merchant') {
            generatePDF('user.merchant.qrCodePDF', 'merchant_qrcode_', $data);
        } else if ($objectType == "user") {
            generatePDF('user.profile.qrCodePDF', 'customer_qrcode_', $data);
        }
    }


     /**
     * Check if the Withdrwal amount does not exceeds the limit
     *
     * @param FeesLimit $currencyFee
     * @param double $amount
     * @throws AmountLimitException
     * @return bool
     *
     */
    function amountIsInLimit(FeesLimit $currencyFee, $amount)
    {
        $minError = (float) $amount < $currencyFee->min_limit;
        $maxError = $currencyFee->max_limit &&  $amount > $currencyFee->max_limit;

        if ($minError && $maxError) {
            throw new AmountLimitException(__("Maximum acceptable amount is :x and minimum acceptable amount is :y", [
                "x" => formatNumber($currencyFee->max_limit, optional($currencyFee->currency)->id),
                "y" => formatNumber($currencyFee->min_limit, optional($currencyFee->currency)->id),
            ]));
        } elseif ($maxError) {
            throw new AmountLimitException(__(
                "Maximum acceptable amount is :x",
                [
                    "x" => formatNumber($currencyFee->max_limit, optional($currencyFee->currency)->id)
                ]
            ));
        } elseif ($minError) {
            throw new AmountLimitException(__(
                "Minimum acceptable amount is :x",
                [
                    "x" => formatNumber($currencyFee->min_limit, optional($currencyFee->currency)->id)
                ]
            ));
        }
    }


    public function transactionFees($currencyId, $amount, $trasactionType, $paymentMethodId = null)
    {
         $fees = $this->getFeesLimitObject(
            ['currency:id,code,symbol,type'],
            $trasactionType,
            $currencyId,
            $paymentMethodId,
            'Yes',
            ['charge_percentage', 'charge_fixed', 'currency_id', 'min_limit', 'max_limit']
        );

        if (is_null($fees)) {
            throw new FeesException(__("Fees limit not set for this currency"));
        }
        $fees->amount = $amount;
        $fees->fees_percentage = $amount * ($fees->charge_percentage / 100);
        $fees->total_fees = $fees->fees_percentage + $fees->charge_fixed;
        $fees->total_amount = $fees->total_fees + $amount;

        return $fees;
    }

    /**
     * Finds corresponding Wallet
     *
     * @param int $userId
     * @param int $currencyId
     * @param int $totalAmount
     * @return Wallet
     * @throws SendMoneyException
     */
    public function getWallet($userId, $currencyId, $option = ['id','balance'])
    {
        $wallet = $this->getUserWallet([], ['user_id' => $userId, 'currency_id' => $currencyId], $option);

        if (is_null($wallet)) {
            throw new WalletException(__('The :x does not exist.', ['x' => __('wallet')]));
        }
        return $wallet;
    }


    public function checkAmount($userId, $currencyId, $amount, $transactionType)
    {
        $currencyFee = $this->transactionFees($currencyId, $amount, $transactionType);

        $this->amountIsInLimit($currencyFee, $amount);

        $this->checkWalletAmount($userId, $currencyId, $currencyFee->total_amount);

        return $currencyFee;
    }

    /**
     * Finds corresponding Wallet amounts or throws error
     *
     * @param int $userId
     * @param int $currencyId
     * @param int $totalAmount
     *
     * @throws Exception
     */
    public function checkWalletAmount($userId, $currencyId, $totalAmount)
    {
        $wallet = $this->getWallet($userId, $currencyId);

        // Checks if wallet has enough balance
        if ($wallet->balance < $totalAmount) {
            throw new WalletException(__("Sorry, not have enough funds to operate."));
        }
    }



}
