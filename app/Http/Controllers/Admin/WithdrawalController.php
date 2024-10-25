<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Users\EmailController;
use App\DataTables\Admin\WithdrawalsDataTable;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Exports\WithdrawalsExport;
use Illuminate\Http\Request;
use Session, Common;
use App\Models\{Wallet,
    Transaction,
    Withdrawal
};
use App\Services\Mail\TransactionUpdatedByAdminMailService;
use App\Services\Sms\TransactionUpdatedByAdminSmsService;

class WithdrawalController extends Controller
{
    protected $helper;
    protected $withdrawal;
    protected $email;

    public function __construct()
    {
        $this->helper     = new Common();
        $this->withdrawal = new Withdrawal();
        $this->email      = new EmailController();
    }

    public function index(WithdrawalsDataTable $dataTable)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'withdrawals';

        $data['w_status']     = $this->withdrawal->select('status')->groupBy('status')->get();
        $data['w_currencies'] = $this->withdrawal->select('currency_id')->groupBy('currency_id')->get();
        $data['w_pm']         = $this->withdrawal->select('payment_method_id')->whereNotNull('payment_method_id')->groupBy('payment_method_id')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['pm']       = isset(request()->payment_methods) ? request()->payment_methods : 'all';
        $data['user']     = $user    = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->withdrawal->getWithdrawalsUserName($user);

        return $dataTable->render('admin.withdrawals.list', $data);
    }

    public function withdrawalCsv()
    {
        return Excel::download(new WithdrawalsExport(), 'withdrawals_report_'. time() .'.xlsx');
    }

    public function withdrawalPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $pm = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;

        $data['withdrawals'] = $this->withdrawal->getWithdrawalsList($from, $to, $status, $currency, $pm, $user)->orderBy('withdrawals.id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.withdrawals.withdrawals_report_pdf', 'withdrawals_report_', $data);
    }

    public function withdrawalsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->withdrawal->getWithdrawalsUsersResponse($search);

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
        $data['sub_menu'] = 'withdrawals';
        $data['withdrawal'] = $withdrawal = Withdrawal::find($id);

        $data['transaction'] = Transaction::select('transaction_type_id', 'status', 'percentage', 'transaction_reference_id')
            ->where(['transaction_reference_id' => $withdrawal->id, 'status' => $withdrawal->status, 'transaction_type_id' => Withdrawal])
            ->first();

        return view('admin.withdrawals.edit', $data);
    }

    public function update(Request $request)
    {
        if ($request->transaction_type == 'Withdrawal')
        {
            if ($request->status == 'Pending')
            {
                if ($request->transaction_status == 'Pending')
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('withdrawal'), 'y' => __('pending')]));
                }
                elseif ($request->transaction_status == 'Success')
                {
                    $withdrawal         = Withdrawal::find($request->id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal')]));
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    $withdrawal         = Withdrawal::find($request->id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal')]));
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success')
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('withdrawal'), 'y' => __('successful')]));
                }
                elseif ($request->transaction_status == 'Pending')
                {
                    $withdrawal         = Withdrawal::find($request->id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal')]));
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    $withdrawal         = Withdrawal::find($request->id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal')]));
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($request->transaction_status == 'Blocked')
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('withdrawal'), 'y' => __('blocked')]));
                }
                elseif ($request->transaction_status == 'Pending')
                {
                    $withdrawal         = Withdrawal::find($request->id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal')]));
                }
                elseif ($request->transaction_status == 'Success')
                {
                    $withdrawal         = Withdrawal::find($request->id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + $request->amount + $request->feesTotal,
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('withdrawal')]));
                }
            }
        }
        return redirect(config('adminPrefix').'/withdrawals');
    }
}
