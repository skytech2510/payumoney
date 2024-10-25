<?php

namespace App\Http\Controllers\Admin;

use App\Services\Mail\Withdrawal\WithdrawalViaAdminMailService;
use App\Services\Mail\Deposit\DepositViaAdminMailService;
use App\Http\Controllers\Users\EmailController;
use Hash, Validator, Session, DB, Exception;
use App\DataTables\Admin\{AdminsDataTable, 
    EachUserTransactionsDataTable,
    UsersDataTable
};
use App\Services\Mail\{UserStatusChangeMailService,
    UserVerificationMailService
};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{ActivityLog,
    CryptoProvider,
    VerifyUser,
    PaymentMethod,
    Transaction,
    Withdrawal,
    FeesLimit,
    Currency,
    RoleUser,
    Dispute,
    Deposit,
    Wallet,
    Ticket,
    QrCode,
    Admin,
    User,
    Role
};

class UserController extends Controller
{
    protected $helper;
    protected $email;
    protected $currency;
    protected $user;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email = new EmailController();
        $this->currency = new Currency();
        $this->user = new User();
    }

    public function index(UsersDataTable $dataTable)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_list';
        return $dataTable->render('admin.users.index', $data);
    }

    public function create()
    {
        $data['menu'] = 'users';
        $data['sub_menu'] = 'users_list';
        $data['roles'] = Role::select('id', 'display_name')->where('user_type', "User")->get();

        return view('admin.users.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = array(
                'first_name'            => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'last_name'             => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'email'                 => 'required|unique:users,email',
                'password'              => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6',
                'status'                => 'required',
            );

            $fieldNames = array(
                'first_name'            => 'First Name',
                'last_name'             => 'Last Name',
                'email'                 => 'Email',
                'password'              => 'Password',
                'password_confirmation' => 'Confirm Password',
                'status'                => 'Status',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {

                try {
                    DB::beginTransaction();

                    // Create user
                    $user = $this->user->createNewUser($request, 'admin');

                    // Assigning user_type and role id to new user
                    RoleUser::insert(['user_id' => $user->id, 'role_id' => $user->role_id, 'user_type' => 'User']);

                    // Create user detail
                    $this->user->createUserDetail($user->id);

                    // Create user's default wallet
                    $this->user->createUserDefaultWallet($user->id, settings('default_currency'));

                    // Create wallets that are allowed by admin
                    if (settings('allowed_wallets') != 'none') {
                        $this->user->createUserAllowedWallets($user->id, settings('allowed_wallets'));
                    }

                    if (isActive('BlockIo') && CryptoProvider::getStatus('BlockIo') == 'Active' && $user->status == 'Active') {
                        $generateUserCryptoWalletAddress = $this->user->generateUserBlockIoWalletAddress($user);
                        if ($generateUserCryptoWalletAddress['status'] == 401) {
                            DB::rollBack();
                            $this->helper->one_time_message('error', $generateUserCryptoWalletAddress['message']);
                            return redirect(config('adminPrefix').'/users');
                        }
                    }

                    if (isActive('TatumIo') && CryptoProvider::getStatus('TatumIo') == 'Active' && $user->status == 'Active') {
                        $generateUserCryptoWalletAddress = $this->user->generateUserTatumIoWalletAddress($user);
                        if ($generateUserCryptoWalletAddress['status'] == 401) {
                            DB::rollBack();
                            $this->helper->one_time_message('error', $generateUserCryptoWalletAddress['message']);
                            return redirect(config('adminPrefix').'/users');
                        }
                    }



                    //Entry for User's QrCode Generation - starts
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
                                $this->helper->one_time_message('success', __('An email has been sent to :x with verification code.', ['x' =>  $user->email]));
                                return redirect(config('adminPrefix').'/users');
                            } catch (Exception $e) {
                                DB::rollBack();
                                $this->helper->one_time_message('error', $e->getMessage());
                                return redirect(config('adminPrefix').'/users');
                            }
                        }
                    }
                    DB::commit();
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('user')]));
                    return redirect(config('adminPrefix').'/users');
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect(config('adminPrefix').'/users');
                }
            }
        }
    }

    public function edit($id)
    {
        $data['menu'] = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_profile';

        $data['users'] = User::find($id);

        $data['roles'] = Role::select('id', 'display_name')->where('user_type', "User")->get();

        return view('admin.users.edit', $data);
    }

    public function update(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = array(
                'first_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'last_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
                'email' => 'required|email|unique:users,email,' . $request->id,
                'password' => 'nullable|min:6|confirmed',
                'password_confirmation' => 'nullable|min:6',
                'status' => 'required',
            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {

                try {
                    DB::beginTransaction();
                    $user = User::find($request->id);
                    $user->first_name = $request->first_name;
                    $user->last_name  = $request->last_name;
                    $user->email      = $request->email;
                    $user->role_id    = $request->role;
                    $user->status     = $request->status;

                    $formattedPhone = ltrim($request->phone, '0');
                    if (!empty($request->phone)) {
                        $user->phone          = preg_replace("/[\s-]+/", "", $formattedPhone);
                        $user->defaultCountry = $request->user_defaultCountry;
                        $user->carrierCode    = $request->user_carrierCode;
                        $user->formattedPhone = $request->formattedPhone;
                    } else {
                        $user->phone          = null;
                        $user->defaultCountry = null;
                        $user->carrierCode    = null;
                        $user->formattedPhone = null;
                    }

                    if (!is_null($request->password) && !is_null($request->password_confirmation)) {
                        $user->password = \Hash::make($request->password);
                    }
                    $user->save();

                    RoleUser::where(['user_id' => $request->id, 'user_type' => 'User'])->update(['role_id' => $request->role]);

                    DB::commit();

                    if ($request->status != $user->status) {
                        (new UserStatusChangeMailService)->send($user);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('user')]));
                    return redirect(config('adminPrefix').'/users');
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect(config('adminPrefix').'/users');
                }
            }
        }
    }

    /* Start of Admin Depsosit */
    public function eachUserDeposit($id, Request $request)
    {
        setActionSession();

        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_profile';

        $data['users'] = User::find($id, ['id', 'first_name', 'last_name']);
        $data['payment_met']     = $payment_met     = PaymentMethod::where(['name' => 'Mts', 'status' => 'Active'])->first(['id', 'name']);
        $data['active_currency'] = $activeCurrency = Currency::where(['status' => 'Active'])->get(['id', 'status', 'code', 'type']);
        $feesLimitCurrency       = FeesLimit::where(['transaction_type_id' => Deposit, 'payment_method_id' => $payment_met->id, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $data['activeCurrencyList'] = $this->currencyList($activeCurrency, $feesLimitCurrency);

        if ($request->isMethod('post')) {

            $userStatus = User::where('id', $id)->value('status');
            if ($userStatus == 'Inactive' || $userStatus == 'Suspended') {
                $this->helper->one_time_message('error', __('The user is :x.', ['x' => $userStatus]));
                return redirect(config('adminPrefix') . '/users/deposit/create/' . $id);
            }
            
            $currency = Currency::where(['id' => $request->currency_id, 'status' => 'Active'])->first(['symbol']);
            $request['currSymbol'] = $currency->symbol;
            $amount = $request->amount;
            $request['totalAmount'] = $amount + $request->fee;
            session(['transInfo' => $request->all()]);
            $data['transInfo'] = $transInfo = $request->all();

            //check amount and limit
            $feesDetails = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $request->currency_id, 'payment_method_id' => $transInfo['payment_method'], 'has_transaction' => 'Yes'])
                ->first(['min_limit', 'max_limit']);

            if ($feesDetails->max_limit == null) {
                if (($amount < $feesDetails->min_limit)) {
                    $data['error'] = 'Minimum amount ' . formatNumber($feesDetails->min_limit);
                    $this->helper->one_time_message('error', $data['error']);
                    return view('admin.users.deposit.create', $data);
                }
            } else {
                if (($amount < $feesDetails->min_limit) || ($amount > $feesDetails->max_limit)) {
                    $data['error'] = __('Minimum amount :x and Maximum amount :y', ['x' => formatNumber($feesDetails->min_limit), 'y' => formatNumber($feesDetails->max_limit)]);
                    $this->helper->one_time_message('error', $data['error']);
                    return view('admin.users.deposit.create', $data);
                }
            }
            return view('admin.users.deposit.confirmation', $data);
        }
        return view('admin.users.deposit.create', $data);
    }

    //Extended function below - deposit
    public function currencyList($activeCurrency, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        foreach ($activeCurrency as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $aCurrency->status == 'Active' && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$aCurrency->id]['id']   = $aCurrency->id;
                    $selectedCurrency[$aCurrency->id]['code'] = $aCurrency->code;
                    $selectedCurrency[$aCurrency->id]['type'] = $aCurrency->type;
                }
            }
        }
        return $selectedCurrency;
    }
    /* End of Admin Depsosit */

    public function eachUserDepositSuccess(Request $request)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_profile';

        $user_id = $request->user_id;

        //Check Session - starts
        $sessionValue = session('transInfo');
        if (empty($sessionValue))
        {
            return redirect(config('adminPrefix')."/users/deposit/create/" . $user_id);
        }
        //Check Session - ends

        actionSessionCheck();

        $amount  = $sessionValue['amount'];
        $uuid    = unique_code();
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $sessionValue['currency_id'], 'payment_method_id' => $sessionValue['payment_method']])
            ->first(['charge_percentage', 'charge_fixed']);
        //charge percentage calculation
        $p_calc = (($amount) * (@$feeInfo->charge_percentage) / 100);

        try
        {
            DB::beginTransaction();
            //Deposit
            $deposit                    = new Deposit();
            $deposit->user_id           = $user_id;
            $deposit->currency_id       = $sessionValue['currency_id'];
            $deposit->payment_method_id = $sessionValue['payment_method'];
            $deposit->uuid              = $uuid;
            $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
            $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $deposit->amount            = $amount;
            $deposit->status            = 'Success';
            $deposit->save();

            //Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $user_id;
            $transaction->currency_id              = $sessionValue['currency_id'];
            $transaction->payment_method_id        = $sessionValue['payment_method'];
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id      = Deposit;
            $transaction->uuid                     = $uuid;
            $transaction->subtotal                 = $amount;
            $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
            $transaction->charge_percentage        = $deposit->charge_percentage;
            $transaction->charge_fixed             = $deposit->charge_fixed;
            $transaction->total                    = $amount + $deposit->charge_percentage + $deposit->charge_fixed;
            $transaction->status                   = 'Success';
            $transaction->save();

            //Wallet
            $wallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $sessionValue['currency_id']])->first(['id', 'balance']);
            if (empty($wallet))
            {
                $createWallet              = new Wallet();
                $createWallet->user_id     = $user_id;
                $createWallet->currency_id = $sessionValue['currency_id'];
                $createWallet->balance     = $amount;
                $createWallet->is_default  = 'No';
                $createWallet->save();
            }
            else
            {
                $wallet->balance = ($wallet->balance + $amount);
                $wallet->save();
            }

            if (module('Referral') && settings('referral_enabled') == 'Yes') {
            
                $refAwardData = [
                    'userId'          => $deposit->user_id,
                    'currencyId'      => $deposit->currency_id,
                    'currencyCode'    => $deposit?->currency?->code,
                    'presentAmount'   => $deposit->amount,
                    'paymentMethodId' => $deposit->payment_method_id,
                    'transactionType' => 'Deposit',
                ];

                $awardResponse = (new \Modules\Referral\Entities\ReferralAward)->checkReferralAward($refAwardData);
            }

            DB::commit();

            // Notification Email/SMS
            (new DepositViaAdminMailService)->send($deposit);
            if (module('Referral') && settings('referral_enabled') == 'Yes' && !empty($awardResponse)) {
                if (isset($awardResponse['email_status']) && $awardResponse['email_status'] === 200 && !empty($awardResponse['email_details'])) {
                    $awardInfo = (new \Modules\Referral\Services\Email\ReferralAwardMailService)->send($awardResponse['email_details']);
                    \Modules\Referral\Jobs\ProcessRewardEmail::dispatch($awardInfo);
                }
            }

            // Send deposit sms to admin
            if (checkAppSmsEnvironment()) {
                $payoutMessage = 'Amount ' . moneyFormat(optional($deposit->currency)->symbol, formatNumber($deposit->amount)) . ' was deposited by System Administrator.';
                if (!empty($deposit->user->formattedPhone)) {
                    sendSMS($deposit->user->formattedPhone, $payoutMessage);
                }
            }

            $data['transInfo']['currency_id'] = $sessionValue['currency_id'];
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['users'] = User::find($user_id, ['id']);
            $data['name'] = $sessionValue['fullname'];

            Session::forget('transInfo');
            clearActionSession();
            return view('admin.users.deposit.success', $data);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            Session::forget('transInfo');
            clearActionSession();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect(config('adminPrefix')."/users/deposit/create/" . $user_id);
        }
    }

    public function eachUserdepositPrintPdf($transaction_id)
    {
        $data['transactionDetails'] = Transaction::with(['payment_method:id,name', 'currency:id,symbol,code'])
            ->where(['id' => $transaction_id])
            ->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total', 'user_id']);

        generatePDF('admin.users.deposit.depositPrintPdf', 'deposit_report_', $data);
    }

    /* Start of Admin Withdraw */
    public function eachUserWithdraw($id, Request $request)
    {
        setActionSession();

        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_profile';

        $data['users'] = $users = User::find($id, ['id', 'first_name', 'last_name']);

        if ($request->isMethod('post')) {

            $userStatus = User::where('id', $id)->value('status');
            if ($userStatus == 'Inactive' || $userStatus == 'Suspended') {
                $this->helper->one_time_message('error', __('The user is :x.', ['x' => $userStatus]));
                return redirect(config('adminPrefix') . '/users/withdraw/create/' . $id);
            }

            $amount = $request->amount;
            $currency = Currency::where(['id' => $request->currency_id])->first(['symbol']);
            $request['currSymbol'] = $currency->symbol;
            $request['totalAmount'] = $request->amount + $request->fee;
            session(['transInfo' => $request->all()]);
            $data['transInfo'] = $request->all();

            //backend validation starts
            $request['transaction_type_id'] = Withdrawal;
            $request['currency_id']         = $request->currency_id;
            $request['payment_method_id']   = $request->payment_method;
            $amountFeesLimitCheck           = $this->amountFeesLimitCheck($request);

            if ($amountFeesLimitCheck) {
                if ($amountFeesLimitCheck->getData()->success->status == 200) {
                    if ($amountFeesLimitCheck->getData()->success->totalAmount > $amountFeesLimitCheck->getData()->success->balance) {
                        $data['error'] = "Insufficient Balance!";
                        $this->helper->one_time_message('error', $data['error']);
                        return view('admin.users.withdraw.create', $data);
                    }
                } elseif ($amountFeesLimitCheck->getData()->success->status == 401) {
                    $data['error'] = $amountFeesLimitCheck->getData()->success->message;
                    $this->helper->one_time_message('error', $data['error']);
                    return view('admin.users.withdraw.create', $data);
                }
            }
            //backend valdation ends
            return view('admin.users.withdraw.confirmation', $data);
        }

        $data['payment_met'] = $paymentMethod = PaymentMethod::where(['name' => 'Mts'])->first(['id', 'name']);
        $paymentMethodId = $paymentMethod->id;
        $data['wallets'] = $users->wallets()->whereHas('active_currency', function ($q) use ($paymentMethodId)
        {
            $q->whereHas('fees_limit', function ($query) use ($paymentMethodId)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', Withdrawal)->where('payment_method_id', $paymentMethodId);
            });
        })
        ->with(['active_currency:id,code,type', 'active_currency.fees_limit:id,currency_id'])
        ->get(['id', 'currency_id']);

        return view('admin.users.withdraw.create', $data);
    }

    public function amountFeesLimitCheck(Request $request)
    {
        $amount = $request->amount;
        $feesDetails = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $request->currency_id, 'payment_method_id' => $request->payment_method_id])
            ->first(['min_limit', 'max_limit', 'charge_percentage', 'charge_fixed']);
        $wallet = Wallet::where(['currency_id' => $request->currency_id, 'user_id' => $request->user_id])->first(['balance']);

        if ($request->transaction_type_id == Withdrawal) {
            //Wallet Balance Limit Check Starts here
            $checkAmount = $amount + $feesDetails->charge_fixed + (($amount * $feesDetails->charge_percentage) / 100);
            if (@$wallet)
            {
                if ((@$checkAmount) > (@$wallet->balance) || (@$wallet->balance < 0))
                {
                    $success['message'] = "Insufficient Balance!";
                    $success['status']  = '401';
                    return response()->json(['success' => $success]);
                }
            }
            //Wallet Balance Limit Check Ends here
        }

        //Amount Limit Check Starts here
        if (empty($feesDetails))
        {
            $feesPercentage            = 0;
            $feesFixed                 = 0;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess, $request->currency_id);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesPercentage;
            $success['pFeesHtml']      = formatNumber($feesPercentage, $request->currency_id);
            $success['fFees']          = $feesFixed;
            $success['fFeesHtml']      = formatNumber($feesFixed, $request->currency_id);
            $success['min']            = 0;
            $success['max']            = 0;
            $success['balance']        = 0;
        }
        else
        {
            if (@$feesDetails->max_limit == null)
            {
                if ((@$amount < @$feesDetails->min_limit))
                {
                    $success['message'] = __('Minimum amount :x', ['x' => formatNumber($feesDetails->min_limit, $request->currency_id)]);
                    $success['status']  = '401';
                }
                else
                {
                    $success['status'] = 200;
                }
            }
            else
            {
                if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
                {
                    $success['message'] = 'Minimum amount ' . formatNumber($feesDetails->min_limit, $request->currency_id) . ' and Maximum amount ' . formatNumber($feesDetails->max_limit, $request->currency_id);
                    $success['status']  = '401';
                }
                else
                {
                    $success['status'] = 200;
                }
            }
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess, $request->currency_id);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesDetails->charge_percentage;
            $success['pFeesHtml']      = formatNumber($feesDetails->charge_percentage, $request->currency_id);
            $success['fFees']          = $feesDetails->charge_fixed;
            $success['fFeesHtml']      = formatNumber($feesDetails->charge_fixed, $request->currency_id);
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $success['balance']        = @$wallet->balance ? @$wallet->balance : 0;
        }
        //Amount Limit Check Ends here
        return response()->json(['success' => $success]);
    }

    public function eachUserWithdrawSuccess(Request $request)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_profile';
        $user_id = $request->user_id;

        //Check Session - starts
        $sessionValue = session('transInfo');
        if (empty($sessionValue)) {
            return redirect(config('adminPrefix')."/users/withdraw/create/" . $user_id);
        }
        //Check Session - ends

        actionSessionCheck();

        $uuid = unique_code();
        $feeInfo = FeesLimit::where(['transaction_type_id' => Withdrawal, 'currency_id' => $sessionValue['currency_id'], 'payment_method_id' => $sessionValue['payment_method']])
            ->first(['charge_percentage', 'charge_fixed']);
        $p_calc = (($sessionValue['amount']) * (@$feeInfo->charge_percentage) / 100); //charge percentage calculation

        try
        {
            DB::beginTransaction();
            //Withdrawal
            $withdrawal                    = new Withdrawal();
            $withdrawal->user_id           = $user_id;
            $withdrawal->currency_id       = $sessionValue['currency_id'];
            $withdrawal->payment_method_id = $sessionValue['payment_method'];
            $withdrawal->uuid              = $uuid;
            $withdrawal->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
            $withdrawal->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $withdrawal->subtotal          = ($sessionValue['amount'] - ($p_calc + (@$feeInfo->charge_fixed)));
            $withdrawal->amount            = $sessionValue['amount'];
            $withdrawal->status            = 'Success';
            $withdrawal->save();

            //Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $user_id;
            $transaction->currency_id              = $sessionValue['currency_id'];
            $transaction->payment_method_id        = $sessionValue['payment_method'];
            $transaction->transaction_reference_id = $withdrawal->id;
            $transaction->transaction_type_id      = Withdrawal;
            $transaction->uuid                     = $uuid;
            $transaction->subtotal                 = $withdrawal->amount;
            $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
            $transaction->charge_percentage        = $withdrawal->charge_percentage;
            $transaction->charge_fixed             = $withdrawal->charge_fixed;
            $transaction->total                    = '-' . ($withdrawal->amount + $withdrawal->charge_percentage + $withdrawal->charge_fixed);
            $transaction->status                   = 'Success';
            $transaction->save();

            //Wallet
            $wallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $sessionValue['currency_id']])->first();
            if (!empty($wallet)) {
                $wallet->balance = ($wallet->balance - ($withdrawal->amount + $withdrawal->charge_percentage + $withdrawal->charge_fixed));
                $wallet->save();
            }

            DB::commit();

            // Notification email/SMS
            (new WithdrawalViaAdminMailService)->send($withdrawal);

            if (checkAppSmsEnvironment()) {
                $payoutMessage = 'Amount ' . moneyFormat(optional($withdrawal->currency)->symbol, formatNumber($withdrawal->amount)) . ' was withdrawn by System Administrator.';
                if (!empty($withdrawal->user->formattedPhone)) {
                    sendSMS($withdrawal->user->formattedPhone, $payoutMessage);
                }
            }

            $data['transInfo']['currency_id'] = $transaction->currency->id;
            $data['transInfo']['currSymbol'] = $transaction->currency->symbol;
            $data['transInfo']['subtotal'] = $transaction->subtotal;
            $data['transInfo']['id'] = $transaction->id;
            $data['users'] = User::find($user_id, ['id']);
            $data['name'] = $sessionValue['fullname'];

            Session::forget('transInfo');
            clearActionSession();
            return view('admin.users.withdraw.success', $data);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            Session::forget('transInfo');
            clearActionSession();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect("users/withdraw/create/" . $user_id);
        }
    }

    public function eachUserWithdrawPrintPdf($trans_id)
    {
        $data['transactionDetails'] = Transaction::with(['payment_method:id,name', 'currency:id,symbol,code'])
            ->where(['id' => $trans_id])->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total']);

        generatePDF('admin.users.withdraw.withdrawalPrintPdf', 'withdrawal_report_', $data);
    }
    /* End of Admin Withdraw */

    public function eachUserTransaction($id, EachUserTransactionsDataTable $dataTable)
    {
        $data['menu'] = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_transactions';

        $data['users'] = User::find($id, ['id', 'first_name', 'last_name']);
        $data['transactionStatus'] = (new Transaction)->eachUserTransactionGroupBy('status', $id);
        $data['transactionCurrency'] = (new Transaction)->eachUserTransactionGroupBy('currency_id', $id);
        $data['transactionType'] = (new Transaction)->eachUserTransactionGroupBy('transaction_type_id', $id);

        $data['from'] = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to'] = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status'] = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['type'] = isset(request()->type) ? request()->type : 'all';

        return $dataTable->with('user_id', $id)->render('admin.users.eachusertransaction', $data); //passing $id to dataTable ass user_id
    }

    public function eachUserWallet($id)
    {
        $data['menu'] = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_wallets';

        $data['wallets'] = Wallet::with('currency:id,type,code')->where(['user_id' => $id])->orderBy('id', 'desc')->get();
        $data['users']   = User::find($id, ['id', 'first_name', 'last_name']);
        return view('admin.users.eachuserwallet', $data);
    }

    public function eachUserTicket($id)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_tickets';

        $data['tickets'] = Ticket::where(['user_id' => $id])->orderBy('id', 'desc')->get();
        $data['users']   = User::find($id, ['id', 'first_name', 'last_name']);
        return view('admin.users.eachuserticket', $data);
    }

    public function eachUserDispute($id)
    {
        $data['menu'] = 'users';
        $data['sub_menu'] = 'users_list';
        $data['user_tab_menu'] = 'user_disputes';

        $data['disputes'] = Dispute::where(['claimant_id' => $id])->orWhere(['defendant_id' => $id])->orderBy('id', 'desc')->get();
        $data['users'] = User::find($id, ['id', 'first_name', 'last_name']);

        return view('admin.users.eachuserdispute', $data);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            try {
                DB::beginTransaction();
                // Deleting Non-Relational Table Entries

                ActivityLog::where(['user_id' => $id])->delete();
                RoleUser::where(['user_id' => $id, 'user_type' => 'User'])->delete();

                $user->delete();

                DB::commit();

                $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('user')]));
                return redirect(config('adminPrefix').'/users');
            } catch (Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect(config('adminPrefix').'/users');
            }
        }
    }

    public function postEmailCheck(Request $request)
    {

        if (isset($request->admin_id) || isset($request->user_id))
        {
            if (isset($request->type) && $request->type == "admin-email")
            {
                $req_id = $request->admin_id;
                $email  = Admin::where(['email' => $request->email])->where(function ($query) use ($req_id)
                {
                    $query->where('id', '!=', $req_id);
                })->exists();
            }
            else
            {
                $req_id = $request->user_id;
                $email  = User::where(['email' => $request->email])->where(function ($query) use ($req_id)
                {
                    $query->where('id', '!=', $req_id);
                })->exists();
            }
        }
        else
        {
            if (isset($request->type) && $request->type == "admin-email")
            {
                $email = Admin::where(['email' => $request->email])->exists();
            }
            else
            {
                $email = User::where(['email' => $request->email])->exists();
            }
        }

        if ($email)
        {
            $data['status'] = true;
            $data['fail']   = __('The :x is already exist.', ['x' => __('email')]);
        }
        else
        {
            $data['status']  = false;
            $data['success'] = __('The :x is available.', ['x' => __('email')]);
        }
        return json_encode($data);
    }

    public function duplicatePhoneNumberCheck(Request $request)
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
            $data['fail']   = __('The :x is already exist.', ['x' => __('phone number')]);
        }
        else
        {
            $data['status']  = false;
            $data['success'] = __('The :x is available.', ['x' => __('phone number')]);
        }
        return json_encode($data);
    }

    public function adminList(AdminsDataTable $dataTable)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'admin_users_list';

        return $dataTable->render('admin.users.adminList', $data);
    }

    public function adminCreate()
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'admin_users_list';

        $data['roles'] = Role::select('id', 'display_name')->where('user_type', 'Admin')->get();

        return view('admin.users.adminCreate', $data);
    }

    public function adminStore(Request $request)
    {

        $rules = array(
            'first_name'            => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'last_name'             => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'email'                 => 'required|unique:admins,email',
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
        );

        $fieldNames = array(
            'first_name'            => 'First Name',
            'last_name'             => 'Last Name',
            'email'                 => 'Email',
            'password'              => 'Password',
            'password_confirmation' => 'Confirm Password',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $admin             = new Admin();
            $admin->first_name = $request->first_name;
            $admin->last_name  = $request->last_name;
            $admin->email      = $request->email;
            $admin->password   = Hash::make($request->password);
            $admin->role_id    = $request->role;
            $admin->save();
            RoleUser::insert(['user_id' => $admin->id, 'role_id' => $request->role, 'user_type' => 'Admin']);
        }

        //condition because same function used in installer for create admin
        if (!isset($request->from_installer))
        {
            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('admin')]));
            return redirect()->intended(config('adminPrefix')."/admin_users");
        }
    }

    public function adminEdit($id)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'admin_users_list';

        $data['admin'] = Admin::find($id);
        $data['roles'] = Role::select('id', 'display_name')->where('user_type', "Admin")->get();
        return view('admin.users.adminEdit', $data);
    }

    public function adminUpdate(Request $request)
    {

        $rules = array(
            'first_name' => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'last_name'  => 'required|max:30|regex:/^[a-zA-Z\s]*$/',
            'email'      => 'required|email|unique:admins,email,' . $request->admin_id,
        );

        $fieldNames = array(
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
            'email'      => 'Email',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);
        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $admin             = Admin::find($request->admin_id);
            $admin->first_name = $request->first_name;
            $admin->last_name  = $request->last_name;
            $admin->email      = $request->email;
            $admin->role_id    = $request->role;
            $admin->save();
            RoleUser::where(['user_id' => $admin->id, 'user_type' => 'Admin'])->update(['role_id' => $request->role]);
            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('admin')]));
            return redirect()->intended(config('adminPrefix')."/admin_users");
        }
    }

    public function adminDestroy($id)
    {
        $admin = Admin::find($id);
        if ($admin)
        {
            $admin->delete();

            // Deleting Non-Relational Table Entries
            ActivityLog::where(['user_id' => $id])->delete();
            RoleUser::where(['user_id' => $id, 'user_type' => 'Admin'])->delete();

            $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('admin')]));
            return redirect()->intended(config('adminPrefix')."/admin_users");
        }
    }
}