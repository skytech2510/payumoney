<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\MerchantPaymentsDataTable;
use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{MerchantPayment,
    Transaction,
    Wallet,
    User
};
use App\Exports\MerchantPaymentsExport;
use Illuminate\Support\Facades\Config;
use App\Services\Mail\MerchantPaymentStatusChangeMailService;
use App\Services\Sms\MerchantPaymentStatusChangeSmsService;

class MerchantPaymentController extends Controller
{
    protected $helper;
    protected $email;
    protected $merchant_payment;

    public function __construct()
    {
        $this->helper           = new Common();
        $this->email            = new EmailController();
        $this->merchant_payment = new MerchantPayment();
    }

    public function index(MerchantPaymentsDataTable $dataTable)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'merchant_payments';

        $data['merchant_payments_currencies'] = $this->merchant_payment->select('currency_id')->groupBy('currency_id')->get();

        $data['merchant_payments_status'] = $this->merchant_payment->select('status')->groupBy('status')->get();

        $data['merchant_payments_pm'] = $this->merchant_payment->select('payment_method_id')->whereNotNull('payment_method_id')->groupBy('payment_method_id')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['pm']       = isset(request()->payment_methods) ? request()->payment_methods : 'all';

        return $dataTable->render('admin.merchant_payments.list', $data);
    }

    public function merchantPaymentCsv()
    {
        return Excel::download(new MerchantPaymentsExport(), 'merchant_payments_list_' . time() . '.xlsx');
    }

    public function merchantPaymentPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $pm = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;

        $data['merchant_payments'] = $this->merchant_payment->getMerchantPaymentsList($from, $to, $status, $currency, $pm)->orderBy('merchant_payments.id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.merchant_payments.merchant_payments_report_pdf', 'merchant_payments_report_', $data);
    }

    public function edit($id)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'merchant_payments';

        $data['merchant_payment'] = $merchant_payment = MerchantPayment::find($id);
        $data['transactionOfRefunded'] = $transactionOfRefunded = Transaction::select('refund_reference')
            ->where(['uuid' => $merchant_payment->uuid])->first();

        if (!empty($transactionOfRefunded)) {
            $data['merchantPaymentOfRefunded'] = MerchantPayment::where(['uuid' => $transactionOfRefunded->refund_reference])->first(['id']);
        }

        $data['transaction'] = Transaction::select('transaction_type_id', 'status', 'transaction_reference_id', 'percentage', 'user_type')
            ->where(['uuid' => $merchant_payment->uuid, 'status' => $merchant_payment->status])
            ->whereIn('transaction_type_id', [Payment_Sent, Payment_Received])
            ->first();

        return view('admin.merchant_payments.edit', $data);
    }

    public function update(Request $request)
    {
        $userInfo = User::where(['id' => trim($request->paid_by_user_id)])->first();

        if ($request->transaction_type == 'Payment_Sent') {
            if ($request->status == 'Pending') {
                if ($request->transaction_status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                } elseif ($request->transaction_status == 'Success') {
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->paid_by_user_id,
                        'end_user_id'              => $merchant_payment->merchant->user->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => $request->paid_by_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //deduct amount from receiver wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->amount,
                    ]);

                    if (isset($merchant_payment->merchant)) {

                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->amount));
                        $data['status'] = 'subtracted';
                        $data['fromTo'] = 'from';
                        $data['user'] = $merchant_payment?->merchant?->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merhcant payment')]));

                    return redirect(config('adminPrefix') . '/merchant_payments');
                }
            } elseif ($request->status == 'Success') {
                if ($request->transaction_status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                } elseif ($request->transaction_status == 'Pending') {
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->paid_by_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // add amount to merchant_user_wallet wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance + $request->amount,
                    ]);

                    if (isset($merchant_payment->merchant)) {
                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->amount));
                        $data['status'] = 'added';
                        $data['fromTo'] = 'to';
                        $data['user'] = $merchant_payment?->merchant?->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                }
            } elseif ($request->status == 'Refund') {
                if ($request->transaction_status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                } elseif ($request->transaction_status == 'Success') {
                    $unique_code = unique_code();

                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = $request->paid_by_user_id;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $request->charge_percentage;
                    $merchant_payment->charge_fixed      = $request->charge_fixed;
                    $merchant_payment->amount            = $request->amount;
                    $merchant_payment->total             = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $merchant_payment->status            = $request->status;
                    $merchant_payment->save();

                    //Payment_Sent old entry update
                    Transaction::where([
                        'user_id'                  => $request->paid_by_user_id,
                        'end_user_id'              => $merchant_payment->merchant->user->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => $request->paid_by_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Payment_Sent entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $request->paid_by_user_id;
                    $refund_t_A->end_user_id              = $merchant_payment->merchant->user->id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_A->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->mp_uuid;
                    $refund_t_A->transaction_reference_id = $merchant_payment->id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id;
                    $refund_t_A->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_A->percentage               = $request->percentage;
                    $refund_t_A->subtotal                 = $request->charge_percentage + $request->charge_fixed + $request->amount;
                    $refund_t_A->charge_percentage        = 0;
                    $refund_t_A->charge_fixed             = 0;
                    $refund_t_A->total                    = $request->charge_percentage + $request->charge_fixed + $request->amount;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Payment_Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $merchant_payment->merchant->user->id;
                    $refund_t_B->end_user_id              = $request->paid_by_user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->mp_uuid;
                    $refund_t_B->transaction_reference_id = $merchant_payment->id;
                    $refund_t_B->transaction_type_id      = Payment_Received;
                    $refund_t_B->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_B->percentage               = $request->percentage;
                    $refund_t_B->subtotal                 = $request->amount;
                    $refund_t_B->charge_percentage        = $request->charge_percentage;
                    $refund_t_B->charge_fixed             = $request->charge_fixed;
                    $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //add amount from paid_by_user wallet, if user exists
                    if (isset($merchant_payment->user_id)) {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance', 'user_id')->first();

                        Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $paid_by_user->balance + ($request->charge_percentage + $request->charge_fixed + $request->amount),
                        ]);
                    }

                    if (isset($merchant_payment->user_id)) {
                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->charge_percentage + $request->charge_fixed + $request->amount));
                        $data['status'] = 'added';
                        $data['fromTo'] = 'to';
                        $data['user'] = $merchant_payment->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    //deduct amount to merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->amount,
                    ]);

                    if (isset($merchant_payment->merchant)) {
                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->amount));
                        $data['status'] = 'subtracted';
                        $data['fromTo'] = 'from';
                        $data['user'] = $merchant_payment->merchant->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                }
            }
        } elseif ($request->transaction_type == 'Payment_Received') {
            if ($request->status == 'Pending') {
                if ($request->transaction_status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                } elseif ($request->transaction_status == 'Success') {
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //deduct amount from receiver wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - ($request->amount),
                    ]);

                    if (isset($merchant_payment->merchant)) {
                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->amount));
                        $data['status'] = 'subtracted';
                        $data['fromTo'] = 'from';
                        $data['user'] = $merchant_payment->merchant->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                }
            } elseif ($request->status == 'Success') {
                if ($request->transaction_status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                } elseif ($request->transaction_status == 'Pending') {
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // add amount to merchant_user_wallet wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance + $request->amount,
                    ]);

                    if (isset($merchant_payment->merchant)) {
                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->amount));
                        $data['status'] = 'subtracted';
                        $data['fromTo'] = 'from';
                        $data['user'] = $merchant_payment->merchant->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                }
            } elseif ($request->status == 'Refund') {
                if ($request->transaction_status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/merchant_payments');
                } elseif ($request->transaction_status == 'Success') {
                    $unique_code                         = unique_code();
                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = isset($userInfo) ? $userInfo->id : null;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $request->charge_percentage;
                    $merchant_payment->charge_fixed      = $request->charge_fixed;
                    $merchant_payment->amount            = $request->amount;
                    $merchant_payment->total             = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $merchant_payment->status            = $request->status;
                    $merchant_payment->save();

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Payment_Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $merchant_payment->merchant->user->id;
                    $refund_t_B->end_user_id              = isset($userInfo) ? $userInfo->id : null;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->mp_uuid;
                    $refund_t_B->transaction_reference_id = $merchant_payment->id;
                    $refund_t_B->transaction_type_id      = $request->transaction_type_id;
                    $refund_t_B->user_type                = $request->user_type;
                    $refund_t_B->subtotal                 = $request->amount;
                    $refund_t_B->percentage               = $request->percentage;
                    $refund_t_B->charge_percentage        = $request->charge_percentage;
                    $refund_t_B->charge_fixed             = $request->charge_fixed;
                    $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //add amount from paid_by_user wallet
                    if (isset($merchant_payment->user_id)) {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance', 'user_id')->first();

                        Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $paid_by_user->balance + ($request->amount + $request->charge_percentage + $request->charge_fixed),
                        ]);
                    }

                    if (isset($merchant_payment->user_id)) {
                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->amount + $request->charge_percentage + $request->charge_fixed));
                        $data['status'] = 'added';
                        $data['fromTo'] = 'to';
                        $data['user'] = $merchant_payment->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    //deduct amount to merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->amount,
                    ]);

                    if (isset($merchant_payment->merchant)) {
                        $data['amount'] = moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->amount));
                        $data['status'] = 'subtracted';
                        $data['fromTo'] = 'from';
                        $data['user'] = $merchant_payment->merchant->user;

                        (new MerchantPaymentStatusChangeMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new MerchantPaymentStatusChangeSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));

                    return redirect(config('adminPrefix') . '/merchant_payments');
                }
            }
        }
    }
}
