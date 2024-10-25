<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\RequestPaymentsDataTable;
use App\Http\Controllers\Users\EmailController;
use App\Exports\RequestPaymentsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Session, Config, Common;
use Illuminate\Http\Request;
use App\Models\{Transaction,
    RequestPayment,
    EmailTemplate,
    Wallet,
    User
};
use App\Services\Mail\RequestPaymentStatusChangeMailService;
use App\Services\Sms\RequestPaymentStatusChangeSmsService;

class RequestPaymentController extends Controller
{
    protected $helper;
    protected $email;
    protected $requestpayment;

    public function __construct()
    {
        $this->helper         = new Common();
        $this->email          = new EmailController();
        $this->requestpayment = new RequestPayment();
    }

    public function index(RequestPaymentsDataTable $dataTable)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'request_payments';

        $data['requestpayments_status']     = $this->requestpayment->select('status')->groupBy('status')->get();
        $data['requestpayments_currencies'] = $this->requestpayment->select('currency_id')->groupBy('currency_id')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['user']     = $user    = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->requestpayment->getRequestPaymentsUserName($user);

        return $dataTable->render('admin.RequestPayment.list', $data);
    }

    public function requestpaymentCsv()
    {
        return Excel::download(new RequestPaymentsExport(), 'requestpayments_list_' . time() . '.xlsx');
    }

    public function requestpaymentPdf()
    {
        $from     = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to       = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;

        $data['requestpayments'] = $this->requestpayment->getRequestPaymentsList($from, $to, $status, $currency, $user)->orderBy('request_payments.id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.RequestPayment.requestpayments_report_pdf', 'requestpayments_report_', $data);
    }

    public function requestpaymentsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->requestpayment->getRequestPaymentsUsersResponse($search);

        $res = ['status' => 'fail'];
        if (count($user) > 0) {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function edit($id)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'request_payments';

        $data['request_payments'] = $request_payments = RequestPayment::find($id);

        $data['transactionOfRefunded'] = $transactionOfRefunded = Transaction::select('refund_reference')->where(['uuid' => $request_payments->uuid])->first();

        $data['requestPaymentsOfRefunded'] = RequestPayment::where(['uuid' => $transactionOfRefunded->refund_reference])->first(['id']);

        $data['transaction'] = Transaction::select('transaction_type_id', 'status', 'percentage', 'charge_percentage', 'charge_fixed', 'transaction_reference_id', 'user_type')
            ->where([
                'transaction_reference_id' => $request_payments->id,
                'status'                   => $request_payments->status,
                'transaction_type_id'      => Request_Received,
            ])
            ->first();

        return view('admin.RequestPayment.edit', $data);
    }

    public function update(Request $request)
    {
        $userInfo = User::where(['email' => trim($request->request_payments_email)])->first();

        //Updating both Request_Sent and Request_Received entries by using one type
        if ($request->transaction_type == 'Request_Received') {
            if ($request->status == 'Success') {
                if ($request->transaction_status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('request payment'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/request_payments');
                }
            } elseif ($request->status == 'Refund') {
                if ($request->transaction_status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('request payment'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/request_payments');
                } elseif ($request->transaction_status == 'Success') {
                    $unique_code = unique_code();

                    $requestpayment = new RequestPayment();

                    $requestpayment->user_id = $request->user_id;

                    $requestpayment->receiver_id = isset($userInfo) ? $userInfo->id : null;

                    $requestpayment->currency_id = $request->currency_id;

                    $requestpayment->uuid = $unique_code;

                    $requestpayment->amount = $request->amount;

                    $requestpayment->accept_amount = $request->accept_amount;

                    $requestpayment->email = $request->request_payments_email;

                    $requestpayment->note = $request->note;

                    $requestpayment->status = $request->status;

                    $requestpayment->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_Sent,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Request_Sent entry
                    $refund_t_A = new Transaction();

                    $refund_t_A->user_id     = $request->user_id;
                    $refund_t_A->end_user_id = isset($userInfo) ? $userInfo->id : null;
                    $refund_t_A->currency_id = $request->currency_id;
                    $refund_t_A->uuid = $unique_code;
                    $refund_t_A->refund_reference = $request->uuid;
                    $refund_t_A->transaction_reference_id = $requestpayment->id;
                    $refund_t_A->transaction_type_id      = Request_Sent; //Request_Sent
                    $refund_t_A->user_type = $request->user_type;
                    $refund_t_A->subtotal = $request->accept_amount;
                    $refund_t_A->percentage = 0;
                    $refund_t_A->charge_percentage = 0;
                    $refund_t_A->charge_fixed = 0;
                    $refund_t_A->total = '-' . $refund_t_A->subtotal;
                    $refund_t_A->note   = $request->note;
                    $refund_t_A->status = $request->status;
                    $refund_t_A->save();

                    //New Request_Received entry
                    $refund_t_B = new Transaction();
                    $refund_t_B->user_id     = isset($userInfo) ? $userInfo->id : null;
                    $refund_t_B->end_user_id = $request->user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $requestpayment->id;
                    $refund_t_B->transaction_type_id = $request->transaction_type_id;
                    $refund_t_B->user_type = $request->user_type;
                    $refund_t_B->subtotal = $request->accept_amount;
                    $refund_t_B->percentage        = $request->percentage;
                    $refund_t_B->charge_percentage = $request->charge_percentage;
                    $refund_t_B->charge_fixed      = $request->charge_fixed;
                    $refund_t_B->total = ($request->charge_percentage + $request->charge_fixed + $refund_t_B->subtotal);
                    $refund_t_B->note = $request->note;
                    $refund_t_B->status = $request->status;
                    $refund_t_B->save();

                    //sender wallet entry update
                    $request_created_wallet = Wallet::where([
                        'user_id'     => $requestpayment->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $requestpayment->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $request_created_wallet->balance - $request->accept_amount,
                    ]);

                    if (isset($userInfo)) {
                        //receiver wallet entry update
                        $request_accepted_wallet = Wallet::where([
                            'user_id'     => isset($userInfo) ? $userInfo->id : null,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => isset($userInfo) ? $userInfo->id : null,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $request_accepted_wallet->balance + ($request->accept_amount + $request->charge_percentage + $request->charge_fixed),
                        ]);
                    }

                    $data['amount'] = moneyFormat(optional($requestpayment->currency)->symbol, formatNumber($request->accept_amount));
                    $data['user'] = $requestpayment->user;
                    $data['status'] = 'subtracted';
                    $data['fromTo'] = 'from';

                    (new RequestPaymentStatusChangeMailService)->send($requestpayment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new RequestPaymentStatusChangeSmsService)->send($requestpayment, $data);
                    }

                    if (isset($userInfo)) {
                        $data['amount'] = moneyFormat(optional($requestpayment->currency)->symbol, formatNumber($request->accept_amount + $request->charge_percentage + $request->charge_fixed));
                        $data['user'] = $requestpayment->receiver;
                        $data['status'] = 'added';
                        $data['fromTo'] = 'to';

                        (new RequestPaymentStatusChangeMailService)->send($requestpayment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new RequestPaymentStatusChangeSmsService)->send($requestpayment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('request payment')]));
                    return redirect(config('adminPrefix') . '/request_payments');
                }
            } elseif ($request->status == 'Blocked') {

                $request_created         = RequestPayment::find($request->id);
                $request_created->status = $request->status;
                $request_created->save();

                //Request From entry update
                Transaction::where([
                    'user_id'                  => $request->user_id,
                    'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_Sent,
                ])->update([
                    'status' => $request->status,
                ]);

                //Request To entry update
                Transaction::where([
                    'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                    'end_user_id'              => $request->user_id,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_Received,
                ])->update([
                    'status' => $request->status,
                ]);

                $data['user'] = $request_created->user;
                $data['amount'] = 'No Amount';
                $data['status'] = 'added/subtracted';
                $data['fromTo'] = 'from/to';

                (new RequestPaymentStatusChangeMailService)->send($request_created, $data);

                if (!empty($data['user']->formattedPhone)) {
                    (new RequestPaymentStatusChangeSmsService)->send($request_created, $data);
                }

                if (isset($userInfo)) {
                    $data['user'] = $request_created->receiver;
                    $data['amount'] = 'No Amount';
                    $data['status'] = 'added/subtracted';
                    $data['fromTo'] = 'from/to';

                    (new RequestPaymentStatusChangeMailService)->send($request_created, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new RequestPaymentStatusChangeSmsService)->send($request_created, $data);
                    }
                }

                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('request payment')]));
                return redirect(config('adminPrefix') . '/request_payments');
            } elseif ($request->status == 'Pending') {
                $request_created         = RequestPayment::find($request->id);
                $request_created->status = $request->status;
                $request_created->save();

                //Request From entry update
                Transaction::where([
                    'user_id'                  => $request->user_id,
                    'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_Sent,
                ])->update([
                    'status' => $request->status,
                ]);

                //Request To entry update
                Transaction::where([
                    'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                    'end_user_id'              => $request->user_id,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_Received,
                ])->update([
                    'status' => $request->status,
                ]);

                $data['user'] = $request_created->user;
                $data['amount'] = 'No Amount';
                $data['status'] = 'added/subtracted';
                $data['fromTo'] = 'from/to';

                (new RequestPaymentStatusChangeMailService)->send($request_created, $data);

                if (!empty($data['user']->formattedPhone)) {
                    (new RequestPaymentStatusChangeSmsService)->send($request_created, $data);
                }

                if (isset($userInfo)) {
                    $data['user'] = $request_created->receiver;
                    $data['amount'] = 'No Amount';
                    $data['status'] = 'added/subtracted';
                    $data['fromTo'] = 'from/to';

                    (new RequestPaymentStatusChangeMailService)->send($request_created, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new RequestPaymentStatusChangeSmsService)->send($request_created, $data);
                    }
                }

                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('request payment')]));
                return redirect(config('adminPrefix') . '/request_payments');
            }
        }
    }
}
