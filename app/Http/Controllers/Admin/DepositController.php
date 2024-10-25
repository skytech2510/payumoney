<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\DepositsDataTable;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DepositsExport;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Deposit,
    Transaction,
    Wallet
};

class DepositController extends Controller
{
    protected $helper;
    protected $deposit;

    public function __construct()
    {
        $this->helper  = new Common();
        $this->deposit = new Deposit();
    }

    public function index(DepositsDataTable $dataTable)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'deposits';

        $data['d_status']     = $this->deposit->select('status')->groupBy('status')->get();
        $data['d_currencies'] = $this->deposit->with('currency:id,code')->select('currency_id')->groupBy('currency_id')->get();
        $data['d_pm']         = $this->deposit->with('payment_method:id,name')->select('payment_method_id')->whereNotNull('payment_method_id')->groupBy('payment_method_id')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['pm']       = isset(request()->payment_methods) ? request()->payment_methods : 'all';
        $data['user']     = $user = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->deposit->getDepositsUsersName($user);

        return $dataTable->render('admin.deposits.list', $data);
    }

    public function depositsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->deposit->getDepositsUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0)
        {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function depositCsv()
    {
        return Excel::download(new DepositsExport(), 'deposit_list_'. time() .'.xls');
    }

    public function depositPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $pm = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;
        $data['deposits'] = $this->deposit->getDepositsList($from, $to, $status, $currency, $pm, $user)->orderBy('id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.deposits.deposits_report_pdf', 'deposits_report_', $data);
    }

    public function edit($id)
    {
        $data['menu'] = 'transaction';
        $data['sub_menu'] = 'deposits';
        $data['deposit'] = $deposit = Deposit::find($id);

        $data['transaction'] = Transaction::select('transaction_type_id', 'status', 'transaction_reference_id', 'percentage')
            ->where(['transaction_reference_id' => $deposit->id, 'status' => $deposit->status, 'transaction_type_id' => Deposit])
            ->first();
        return view('admin.deposits.edit', $data);
    }

    public function update(Request $request)
    {
        if ($request->transaction_type == 'Deposit')
        {
            if ($request->status == 'Pending')
            {
                if ($request->transaction_status == 'Pending')
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('deposit'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
                elseif ($request->transaction_status == 'Success')
                {
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

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
                        'balance' => $current_balance->balance - $request->amount,
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('deposit')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('deposit')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success')
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('deposit'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

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
                        'balance' => $current_balance->balance + $request->amount,
                    ]);

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('deposit')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
                elseif ($request->transaction_status == 'Pending')
                {
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

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
                        'balance' => $current_balance->balance + $request->amount,
                    ]);

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('deposit')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($request->transaction_status == 'Blocked')
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('deposit'), 'y' => __('blocked')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
                elseif ($request->transaction_status == 'Pending')
                {
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('deposit')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
                elseif ($request->transaction_status == 'Success')
                {
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

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
                        'balance' => $current_balance->balance - $request->amount,
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('deposit')]));
                    return redirect(config('adminPrefix').'/deposits');
                }
            }
        }
    }
}
