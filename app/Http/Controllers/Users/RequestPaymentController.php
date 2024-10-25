<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Users\EmailController;
use DB, Validator, Session, Exception;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use App\Models\{Currency,
    RequestPayment,
    Transaction,
    FeesLimit,
    Wallet,
    User
};

class RequestPaymentController extends Controller
{
    protected $helper;
    protected $email;
    protected $requestPayment;

    public function __construct()
    {
        $this->helper         = new Common();
        $this->email          = new EmailController();
        $this->requestPayment = new RequestPayment();
    }

    public function add()
    {
        //set the session for validating the action
        setActionSession();
        
        if (route('user.request_money.store') != url()->previous()) {
            if (!empty(session('transInfo'))) {
                session()->forget('transInfo');
            }
        }

        $data['menu']          = 'send_receive';
        $data['submenu']       = 'receive';
        $data['content_title'] = 'Request Payment';

        $activeCurrency       = Currency::where(['status' => 'Active'])->get(['id', 'status', 'code', 'type']);
        $feesLimitCurrency    = FeesLimit::where(['transaction_type_id' => Request_Received, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $data['currencyList'] = $this->currencyList($activeCurrency, $feesLimitCurrency);

        $data['defaultWallet'] = Wallet::with('currency:id,type')->where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);

        $data['amountPlaceHolder'] = $data['defaultWallet']->currency?->type == 'fiat' ? number_format(0, preference('decimal_format_amount', 2)) : number_format(0, preference('decimal_format_amount_crypto', 8));

        switch (preference('processed_by')) {
            case 'email':
                $placeHolder = __('Please enter valid :x', ['x' => __('email (ex: user@gmail.com)')]);
                $helpText = __('We will never share your :x with anyone else.', ['x' => __('email')]);
                break;
            case 'phone':
                $placeHolder = __('Please enter valid :x', ['x' => __('phone (ex: +12015550123)')]);
                $helpText = __('We will never share your :x with anyone else.', ['x' => __('phone')]);
                break;
            case 'email_or_phone':
                $placeHolder = __('Please enter valid :x', ['x' => __('email (ex: user@gmail.com) or phone (ex: +12015550123)')]);
                $helpText = __('We will never share your :x with anyone else.', ['x' => __('email or phone')]);
                break;
            default:
                $placeHolder = '';
                $helpText = '';
                break;
        }
        $data['placeHolder'] = $placeHolder;
        $data['helpText'] = $helpText;

        return view('user.request-money.create', $data);
    }

    public function requestUserEmailPhoneReceiverStatusValidate(Request $request)
    {
        $phoneRegex = $this->helper->validatePhoneInput($request->emailOrPhone);
        if ($phoneRegex) {
            $user = User::where(['id' => auth()->user()->id])->first(['formattedPhone']);
            if (empty($user->formattedPhone)) {
                return response()->json([
                    'status'  => 404,
                    'message' => __("Please set your phone number first!"),
                ]);
            }

            //Check own phone number
            if ($request->emailOrPhone == auth()->user()->formattedPhone) {
                return response()->json([
                    'status'  => true,
                    'message' => __("You Cannot Request Money To Yourself."),
                ]);
            }

            //Check Request Acceptor/Recipient is suspended/inactive - if entered phone number
            $requestAcceptor = User::where(['formattedPhone' => $request->emailOrPhone])->first(['status']);
            
            if (!empty($requestAcceptor)) {
                if ($requestAcceptor->status == 'Suspended') {
                    return response()->json([
                        'status'  => true,
                        'message' => __("The recipient is suspended!"),
                    ]);
                } elseif ($requestAcceptor->status == 'Inactive') {
                    return response()->json([
                        'status'  => true,
                        'message' => __("The recipient is inactive!"),
                    ]);
                }
            }
        } else {
            if ($request->emailOrPhone == auth()->user()->email) {
                return response()->json([
                    'status'  => true,
                    'message' => __("You Cannot Request Money To Yourself."),
                ]);
            }

            //Check Receiver/Recipient is suspended/inactive - if entered email
            $requestAcceptor = User::where(['email' => trim($request->emailOrPhone)])->first(['status']);
            if (!empty($requestAcceptor)) {
                if ($requestAcceptor->status == 'Suspended') {
                    return response()->json([
                        'status'  => true,
                        'message' => __("The recipient is suspended!"),
                    ]);
                } elseif ($requestAcceptor->status == 'Inactive') {
                    return response()->json([
                        'status'  => true,
                        'message' => __("The recipient is inactive!"),
                    ]);
                }
            }
        }
    }

    public function store(Request $request)
    {
        actionSessionCheck();
        $data['menu'] = 'send_receive';
        $data['submenu'] = 'receive';
        $data['content_title'] = 'Request Payment';

        $rules = array(
            'amount' => 'required|numeric',
            'email' => 'required',
            'currency_id' => 'required',
            'note' => 'required',
        );
        $fieldNames = array(
            'amount' => __("Amount"),
            'email' => __("Email"),
            'currency_id' => __('Currency'),
            'note' => __("Note"),
        );

        // backend Validation - ends

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        // backend Validation - starts
        $messages = [];

        if ($request->processed_by == 'email')
        {
            $rules['email'] = 'required|email';
        }
        elseif ($request->processed_by == 'phone')
        {
            $myStr = explode('+', $request->email);
            if ($request->email[0] != "+" || !is_numeric($myStr[1]))
            {
                return back()->withErrors(__("Please enter valid phone (ex: +12015550123)"))->withInput();
            }
        }
        elseif ($request->processed_by == 'email_or_phone')
        {
            $myStr = explode('+', $request->email);
            //valid number is not entered
            if ($request->email[0] != "+" || !is_numeric($myStr[1]))
            {
                //check if valid email
                $rules['email'] = 'required|email';

                $messages = [
                    'email' => __("Please enter valid email (ex: user@gmail.com) or phone (ex: +12015550123)"),
                ];
            }
        }

        //Own Email or phone validation + Request Acceptor/Recipient is suspended/Inactive validation
        $request['emailOrPhone']       = $request->email;
        $requestUserEmailPhoneReceiverStatusValidate = $this->requestUserEmailPhoneReceiverStatusValidate($request);
        if ($requestUserEmailPhoneReceiverStatusValidate)
        {
            if ($requestUserEmailPhoneReceiverStatusValidate->getData()->status == true || $requestUserEmailPhoneReceiverStatusValidate->getData()->status == 404)
            {
                return back()->withErrors(__($requestUserEmailPhoneReceiverStatusValidate->getData()->message))->withInput();
            }
        }

        $user = User::where('email', $request->email)->orWhere('formattedPhone', $request->email)->first(['first_name', 'last_name']);
        $currency              = Currency::find($request->currency_id, ['id', 'symbol', 'code']);
        $request['userName'] = !is_null($user) ? getColumnValue($user) : null;
        $request['currSymbol'] = $currency->symbol;
        $request['currencyCode'] = $currency->code;
        $data['transInfo']     = $request->all();
        session(['transInfo' => $request->all()]);
        return view('user.request-money.confirm', $data);
    }

    public function requestMoneyConfirm()
    {
        $data['menu']    = 'send_receive';
        $data['submenu'] = 'receive';

        $sessionValue = session('transInfo');
        if (empty($sessionValue))
        {
            return redirect('request_payment/add');
        }

        actionSessionCheck();

        $user_id             = auth()->user()->id;
        $processedBy         = $sessionValue['processed_by'];
        $uuid                = unique_code();
        $emailFilterValidate = $this->helper->validateEmailInput(trim($sessionValue['email']));
        $phoneRegex          = $this->helper->validatePhoneInput(trim($sessionValue['email']));
        $userInfo            = $this->helper->getEmailPhoneValidatedUserInfo($emailFilterValidate, $phoneRegex, trim($sessionValue['email']));
        $receiverName        = isset($userInfo) ? $userInfo->first_name . ' ' . $userInfo->last_name : '';
        $arr                 = [
            'unauthorisedStatus'  => null,
            'emailFilterValidate' => $emailFilterValidate,
            'phoneRegex'          => $phoneRegex,
            'processedBy'         => $processedBy,
            'user_id'             => $user_id,
            'userInfo'            => $userInfo,
            'currency_id'         => $sessionValue['currency_id'],
            'uuid'                => $uuid,
            'amount'              => $sessionValue['amount'],
            'receiver'            => $sessionValue['email'],
            'note'                => $sessionValue['note'],
            'receiverName'        => $receiverName,
            'senderEmail'         => auth()->user()->email,
            // 'status'              => 'Pending',
        ];
        $data['transInfo']['currSymbol']  = $sessionValue['currSymbol'];
        $data['transInfo']['currencyCode']  = $sessionValue['currencyCode'];
        $data['transInfo']['amount']      = $sessionValue['amount'];
        $data['transInfo']['currency_id'] = $sessionValue['currency_id'];
        $data['userPic']                  = isset($userInfo) ? $userInfo->picture : null;
        $data['receiverName']             = $receiverName;
        $data['transInfo']['email']       = $sessionValue['email'];

        //Get response
        $response = $this->requestPayment->processRequestCreateConfirmation($arr, 'web');
        if ($response['status'] != 200)
        {
            if (empty($response['transactionOrReqPaymentId']))
            {
                session()->forget('transInfo');
                $this->helper->one_time_message('error', $response['ex']['message']);
                return redirect('request_payment/add');
            }
            $data['errorMessage'] = $response['ex']['message'];
        }
        $data['transInfo']['trans_id'] = $response['transactionOrReqPaymentId'];

        //clearing session
        session()->forget('transInfo');
        clearActionSession();
        return view('user.request-money.success', $data);
    }

    //Cancel from request acceptor
    public function cancel(Request $request)
    {
        $id = $request->id;
        try
        {
            DB::beginTransaction();
            $TransactionA         = Transaction::find($id); //TODO: query optimization
            $TransactionA->status = "Blocked";
            $TransactionA->save();

            $transaction_type_id = $TransactionA->transaction_type_id == Request_Received ? Request_Sent : Request_Received;
            $TransactionB        = Transaction::where([
                'transaction_reference_id' => $TransactionA->transaction_reference_id,
                'transaction_type_id'      => $transaction_type_id])->first(); //TODO: query optimization
            $TransactionB->status = "Blocked";
            $TransactionB->save();

            $requestPayment         = RequestPayment::find($TransactionA->transaction_reference_id); //TODO: query optimization
            $requestPayment->status = "Blocked";
            $requestPayment->save();
            DB::commit();
            $data = $this->sendRequestCancelNotificationToAcceptorOrCreator($requestPayment, $request->notificationType); //TODO: query optimization
            return json_encode($data);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('dashboard');
        }
    }

    //Cancel from request creator
    public function cancelfrom(Request $request)
    {
        $id = $request->id;
        try
        {
            DB::beginTransaction();
            if ($request->type == Request_Sent)
            {
                $TransactionA         = Transaction::find($id); //TODO: query optimization
                $TransactionA->status = "Blocked";
                $TransactionA->save();

                $TransactionB         = Transaction::where(['transaction_reference_id' => $TransactionA->transaction_reference_id, 'transaction_type_id' => Request_Received])->first(); //TODO: query optimization
                $TransactionB->status = "Blocked";
                $TransactionB->save();

            }
            elseif ($request->type == Request_Received)
            {
                $TransactionA         = Transaction::find($id); //TODO: query optimization
                $TransactionA->status = "Blocked";
                $TransactionA->save();

                $TransactionB         = Transaction::where(['transaction_reference_id' => $TransactionA->transaction_reference_id, 'transaction_type_id' => Request_Sent])->first(); //TODO: query optimization
                $TransactionB->status = "Blocked";
                $TransactionB->save();
            }
            $requestPayment         = RequestPayment::find($TransactionA->transaction_reference_id); //TODO: query optimization
            $requestPayment->status = "Blocked";
            $requestPayment->save();
            DB::commit();

            $data = $this->sendRequestCancelNotificationToAcceptorOrCreator($requestPayment, $request->notificationType); //TODO: query optimization
            return json_encode($data);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('dashboard');
        }
    }

    public function sendRequestCancelNotificationToAcceptorOrCreator($requestPayment, $notificationType)
    {
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($notificationType);
        $phoneRegex          = $this->helper->validatePhoneInput($notificationType);
        $soft_name = session('name');
        $messageFromCreatorToAcceptor = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid, 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->user)]);

        if ($emailFilterValidate && $processedBy == "email")
        {
            if (auth()->user()->id == $requestPayment->user_id)
            {
                if (!empty($requestPayment->receiver_id))
                {
                    //ok
                    $data = $this->onlyEmailToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                        $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->email);
                    return $data;
                }
                else
                {
                    //ok
                    $data = $this->onlyEmailToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $requestPayment->email);
                    return $data;
                }
            }
            elseif (!empty($requestPayment->receiver_id) && auth()->user()->id == $requestPayment->receiver_id)
            {
                $messageFromAcceptorToCreator = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid, 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->receiver)]);

                $data = $this->onlyEmailToRequestCreator($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name, $requestPayment->user->email);
                return $data;
            }
        }
        elseif ($phoneRegex && $processedBy == "phone")
        {
            if (auth()->user()->id == $requestPayment->user_id)
            {
                if (!empty($requestPayment->receiver_id))
                {
                    $data = $this->onlySmsToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                        $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->carrierCode, $requestPayment->receiver->phone);
                    return $data;
                }
                else
                {
                    $data = $this->onlySmsToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $requestPayment->phone);
                    return $data;
                }
            }
            elseif (!empty($requestPayment->receiver_id) && auth()->user()->id == $requestPayment->receiver_id)
            {
                $messageFromAcceptorToCreator = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid, 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->receiver)]);
                $data = $this->onlySmsToRequestCreator($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name,
                    $requestPayment->user->carrierCode, $requestPayment->user->phone);
                return $data;
            }
        }
        elseif ($processedBy == "email_or_phone")
        {
            if ($emailFilterValidate)
            {
                if (auth()->user()->id == $requestPayment->user_id)
                {
                    if (!empty($requestPayment->receiver_id))
                    {
                        $data = $this->onlyEmailToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                            $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->email);
                        return $data;
                    }
                    else
                    {
                        $data = $this->onlyEmailToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $requestPayment->email);
                        return $data;
                    }
                }
                elseif (!empty($requestPayment->receiver_id) && auth()->user()->id == $requestPayment->receiver_id)
                {
                    $messageFromAcceptorToCreator = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid, 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->receiver)]);
                    $data = $this->onlyEmailToRequestCreator($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name, $requestPayment->user->email);
                    return $data;
                }
            }
            elseif ($phoneRegex)
            {
                if (auth()->user()->id == $requestPayment->user_id)
                {
                    if (!empty($requestPayment->receiver_id))
                    {
                        $data = $this->onlySmsToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                            $requestPayment->receiver->first_name, $requestPayment->receiver->last_name, $soft_name, $requestPayment->receiver->carrierCode, $requestPayment->receiver->phone);
                        return $data;
                    }
                    else
                    {
                        $data = $this->onlySmsToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $requestPayment->phone);
                        return $data;
                    }
                }
                elseif (!empty($requestPayment->receiver_id) && auth()->user()->id == $requestPayment->receiver_id)
                {
                    $messageFromAcceptorToCreator = __('Your request payment #:x of :y has been cancelled by :z.', ['x' => $requestPayment->uuid, 'y' => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)), 'z' => getColumnValue($requestPayment->receiver)]);
                    $data = $this->onlySmsToRequestCreator($messageFromAcceptorToCreator, $requestPayment->user->first_name, $requestPayment->user->last_name, $soft_name,
                        $requestPayment->user->carrierCode, $requestPayment->user->phone);
                    return $data;
                }
            }
        }
    }

    public function requestAccept($id)
    {
        //set the session for validating the action
        setActionSession();

        $data['requestPayment'] = $requestPayment = RequestPayment::with(['currency:id,symbol,code,type'])->where(['id' => $id])->first();
        $data['transfer_fee']   = FeesLimit::where(['transaction_type_id' => Request_Received, 'currency_id' => $requestPayment->currency_id])->first(['currency_id', 'charge_percentage', 'charge_fixed']);
        return view('user.request-money.accept', $data);
    }

    public function requestAccepted(Request $request)
    {
        if ($request->isMethod('post'))
        {
            $rules = array(
                'emailOrPhone' => 'required',
                'amount' => 'required|numeric',
                'currency' => 'required',
                'note' => 'required'
            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }

            // backend Validation - starts
            $request['amount']              = $request->amount;
            $request['currency_id']         = $request->currency_id;
            $request['transaction_type_id'] = Request_Received;
            $amountLimitCheck               = $this->amountLimitCheck($request);
            if ($amountLimitCheck->getData()->success->status == 404 || $amountLimitCheck->getData()->success->status == 401)
            {
                return back()->withErrors(__($amountLimitCheck->getData()->success->message))->withInput();
            }
            //backend validation - ends
            
            $data['requestPaymentId'] = $request->id;
            $request['currSymbol']    = $request->currencySymbol;
            $currency = Currency::find($request->currency_id)->first();
            $request['currencyCode'] = $currency->code;

            $user = User::where('email', $request->emailOrPhone)->orWhere('formattedPhone', $request->emailOrPhone)->first(['first_name', 'last_name']); 
            $request['userName'] = !is_null($user) ? getColumnValue($user) : null;

            $request['percentage_fee'] = $amountLimitCheck->getData()->success->feesPercentage;
            $request['fixed_fee'] = $amountLimitCheck->getData()->success->feesFixed;
            $request['fee'] = $amountLimitCheck->getData()->success->totalFees;

            $request['totalAmount']   = $request->amount + $request['fee'];
            session(['transInfo' => $request->all()]);
            $data['transInfo'] = $request->all();
            
            return view('user.request-money.accept-confirm', $data);
        }
    }

    //Amount Limit Check
    public function amountLimitCheck(Request $request)
    {
        $amount      = $request->amount;
        $currency_id = $request->currency_id;
        $user_id     = auth()->user()->id;

        $RequestAcceptorWallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['id']);
        if (empty($RequestAcceptorWallet))
        {
            $success['status']  = 404;
            $success['message'] = __("You don't have the requested currency!");
            return response()->json(['success' => $success]);
        }

        $wallet              = Wallet::where(['currency_id' => $currency_id, 'user_id' => $user_id])->first(['balance']);
        $feesDetails         = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $currency_id])->first(['charge_fixed', 'charge_percentage', 'min_limit', 'max_limit']);
        $feesPercentage      = $amount * ($feesDetails->charge_percentage / 100);
        $checkAmountWithFees = $amount + $feesDetails->charge_fixed + $feesPercentage;
        if (@$wallet)
        {
            if ((@$checkAmountWithFees) > (@$wallet->balance) || (@$wallet->balance < 0))
            {
                $success['message'] = __("Not have enough balance !");
                $success['status']  = '401';
                return response()->json(['success' => $success]);
            }
        }

        //Code for Amount Limit starts here
        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['message'] = __('Minimum amount ') . formatNumber($feesDetails->min_limit, $currency_id);
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
                $success['message'] = __('Minimum amount ') . formatNumber($feesDetails->min_limit, $currency_id) . __(' and Maximum amount ') . formatNumber($feesDetails->max_limit, $currency_id);
                $success['status']  = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }
        //Code for Amount Limit ends here

        //Code for Fees Limit Starts here
        if (empty($feesDetails))
        {
            $feesPercentage            = 0;
            $feesFixed                 = 0;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess, $currency_id);
            $success['totalAmount']    = $totalAmount;
            $success['pFeesHtml']      = formatNumber($feesPercentage, $currency_id);
            $success['fFeesHtml']      = formatNumber($feesFixed, $currency_id);
            $success['min']            = 0;
            $success['max']            = 0;
            $success['balance']        = 0;
        }
        else
        {
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess, $currency_id);
            $success['totalAmount']    = $totalAmount;
            $success['pFeesHtml']      = formatNumber($feesDetails->charge_percentage, $currency_id);
            $success['fFeesHtml']      = formatNumber($feesDetails->charge_fixed, $currency_id);
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $success['balance']        = isset($wallet) ? $wallet->balance : 0.00;
        }
        //Code for Fees Limit Ends here
        return response()->json(['success' => $success]);
    }

    public function requestAcceptedConfirm()
    {
        $sessionValue = session('transInfo');
        if (empty($sessionValue))
        {
            return redirect()->route('user.transactions.index');
        }
        actionSessionCheck();

        $requestPaymentId    = $sessionValue['id'];
        $user_id             = auth()->user()->id;
        $processedBy         = preference('processed_by');
        $emailFilterValidate = $this->helper->validateEmailInput($sessionValue['emailOrPhone']);
        $phoneRegex          = $this->helper->validatePhoneInput($sessionValue['emailOrPhone']);
        $feesLimit           = $this->helper->getFeesLimitObject([], Request_Received, $sessionValue['currency_id'], null, null, ['charge_percentage']);

        $arr = [
            'unauthorisedStatus'  => null,
            'emailFilterValidate' => $emailFilterValidate,
            'phoneRegex'          => $phoneRegex,
            'processedBy'         => $processedBy,
            'requestPaymentId'    => $requestPaymentId,
            'currency_id'         => $sessionValue['currency_id'],
            'user_id'             => $user_id,
            'accept_amount'       => $sessionValue['amount'],
            'charge_percentage'   => $feesLimit->charge_percentage,
            'percentage_fee'      => $sessionValue['percentage_fee'],
            'fixed_fee'           => $sessionValue['fixed_fee'],
            'fee'                 => $sessionValue['fee'],
            'total'               => $sessionValue['totalAmount'],
        ];
        $data['transInfo']['currSymbol']  = $sessionValue['currSymbol'];
        $data['transInfo']['currencyCode']  = $sessionValue['currencyCode'];
        $data['transInfo']['amount']      = $sessionValue['amount'];
        $data['transInfo']['currency_id'] = $sessionValue['currency_id'];

        //Get response
        $response = $this->requestPayment->processRequestAcceptConfirmation($arr, 'web');
        if ($response['status'] != 200)
        {
            if (empty($response['reqPayment']))
            {
                session()->forget('transInfo');
                $this->helper->one_time_message('error', $response['ex']['message']);
                return redirect("request_payment/accept/$requestPaymentId");
            }
            $data['errorMessage'] = $response['ex']['message'];
        }

        $data['requestCreator']['picture']    = $response['reqPayment']['requestPaymentObj']['user']->picture;
        $data['requestCreator']['first_name'] = $response['reqPayment']['requestPaymentObj']['user']->first_name;
        $data['requestCreator']['last_name']  = $response['reqPayment']['requestPaymentObj']['user']->last_name;
        $data['transInfo']['trans_id']        = $response['reqPayment']['transaction_id'];

        session()->forget('transInfo');
        clearActionSession();
        return view('user.request-money.accept-success', $data);
    }

    /**
     * Generate pdf for print
     */
    public function printPdf($trans_id)
    {
        $data['transactionDetails'] = Transaction::with(['end_user:id,first_name,last_name', 'currency:id,symbol,code'])
            ->where(['id' => $trans_id])
            ->first(['transaction_type_id', 'end_user_id', 'currency_id', 'uuid', 'created_at', 'status', 'subtotal', 'charge_percentage', 'charge_fixed', 'total', 'note', 'user_type', 'email']);

        generatePDF('user.request-money.request-money-pdf', 'request_', $data);
    }

    //Extended functions - starts
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

    // Email to registered receiver
    public function onlyEmailToRegisteredRequestReceiver($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = 'Cancellation of Request Payment';
        $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>'; //
        $message .= $messageFromAcceptorToCreator;
        $message .= '<br><br>';
        $message .= 'If you have any questions, please feel free to reply to this mail';
        $message .= '<br><br>';
        $message .= 'Regards,';
        $message .= '<br>';
        $message .= $softName;
        try {
            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (Exception $e)
        {
            DB::rollBack();
        }
    }

    // Email to unregistered receiver
    public function onlyEmailToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = 'Cancellation of Request Payment';
        $message = 'Hi ' . $requestPaymentEmail . ',<br><br>'; //
        $message .= $messageFromCreatorToAcceptor;
        $message .= '<br><br>';
        $message .= 'If you have any questions, please feel free to reply to this mail';
        $message .= '<br><br>';
        $message .= 'Regards,';
        $message .= '<br>';
        $message .= $softName;
        try {
            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (Exception $e)
        {
            DB::rollBack();
        }
    }

    // Email to registered creator
    public function onlyEmailToRequestCreator($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = 'Cancellation of Request Payment';
        $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>'; //
        $message .= $messageFromAcceptorToCreator;
        $message .= '<br><br>';
        $message .= 'If you have any questions, please feel free to reply to this mail';
        $message .= '<br><br>';
        $message .= 'Regards,';
        $message .= '<br>';
        $message .= $softName;
        try {

            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (Exception $e)
        {
            DB::rollBack();
        }
    }

    // Sms to registered receiver
    public function onlySmsToRegisteredRequestReceiver($messageFromCreatorToAcceptor, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
        $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserCarrierCode) && !empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                try {
                    // Mail to request creator when a request is cancelled (both sides)
                    $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>';
                    $message .= $messageFromCreatorToAcceptor;
                    sendSMS($RequestPaymentUserCarrierCode . $RequestPaymentUserPhone, $message);
                    $data['status'] = 'Cancelled';
                    return $data['status'];
                }
                catch (Exception $e)
                {
                    DB::rollBack();
                }
            }
        }
    }

    // Sms to unregistered receiver
    public function onlySmsToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $softName, $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                try {
                    // Mail to request creator when a request is cancelled (both sides)
                    $message = 'Hi ' . $RequestPaymentUserPhone . ',<br><br>';
                    $message .= $messageFromCreatorToAcceptor;
                    sendSMS($RequestPaymentUserPhone, $message);
                    $data['status'] = 'Cancelled';
                    return $data['status'];
                }
                catch (Exception $e)
                {
                    DB::rollBack();
                }
            }
        }
    }

    // Sms to registered creator
    public function onlySmsToRequestCreator($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
        $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserCarrierCode) && !empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                try {
                    // Mail to request creator when a request is cancelled (both sides)
                    $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>';
                    $message .= $messageFromAcceptorToCreator;
                    sendSMS($RequestPaymentUserCarrierCode . $RequestPaymentUserPhone, $message);
                    $data['status'] = 'Cancelled';
                    return $data['status'];
                }
                catch (Exception $e)
                {
                    DB::rollBack();
                }
            }
        }
    }

    //Check Request Creator Status (for dashboard and transactions list - user panel)
    public function checkReqCreatorStatus(Request $request)
    {
        try
        {
            $transaction = Transaction::with(['end_user:id,status'])->find($request->trans_id, ['id', 'end_user_id']);
            return response()->json([
                'status' => $transaction->end_user->status,
            ]);
        }
        catch (Exception $e)
        {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('dashboard');
        }
    }
}
