<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Illuminate\Http\JsonResponse;
use App\Models\Withdrawal;
use Common, Auth;
class WithdrawalsDataTable extends DataTable
{

    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($withdrawal) {
                return dateFormat($withdrawal->created_at);
            })
            ->addColumn('user_id', function ($withdrawal) {
                $sender = getColumnValue($withdrawal->user);
                if ($sender <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $withdrawal->user->id) . '">' . $sender . '</a>';
                }
                return $sender;
            })
            ->editColumn('amount', function ($withdrawal) {
                return formatNumber($withdrawal->amount, $withdrawal->currency_id);
            })
            ->addColumn('fees', function ($withdrawal) {
                return ($withdrawal->charge_percentage == 0) && ($withdrawal->charge_fixed == 0) ? '-' : formatNumber($withdrawal->charge_percentage + $withdrawal->charge_fixed, $withdrawal->currency_id);
            })
            ->addColumn('total', function ($withdrawal) {
                return '<td><span class="text-' . ((($withdrawal->charge_percentage + $withdrawal->charge_fixed + $withdrawal->amount) > 0) ? 'green' : 'red') . '">' . formatNumber($withdrawal->charge_percentage + $withdrawal->charge_fixed + $withdrawal->amount, $withdrawal->currency_id) . '</span></td>';
            })
            ->editColumn('currency_id', function ($withdrawal) {
                return isset($withdrawal->currency->code) ? $withdrawal->currency->code : '';
            })
            ->editColumn('payment_method_id', function ($withdrawal) {
                return ($withdrawal->payment_method->id == Mts) ? settings('name') : $withdrawal->payment_method->name;
            })
            ->editColumn('payment_method_info', function ($withdrawal) {
                if ($withdrawal->payment_method->id == Paypal || $withdrawal->payment_method->id == Mts) {
                    $data = !empty($withdrawal->payment_method_info) ? $withdrawal->payment_method_info : '-';
                } elseif ($withdrawal->payment_method->id == Bank) {
                    $data = (isset($withdrawal->withdrawal_detail->account_name) && !empty($withdrawal->withdrawal_detail->account_name)) ?
                        $withdrawal->withdrawal_detail->account_name . ' ' . '(' . ('*****' . substr($withdrawal->withdrawal_detail->account_number, -4)) . ')' . ' ' . $withdrawal->withdrawal_detail->bank_name : '-';
                } elseif ($withdrawal->payment_method->id == Crypto) {
                    $data = !empty($withdrawal->payment_method_info) ? '*******' . substr($withdrawal->payment_method_info, -15) : '-';
                } elseif (config('mobilemoney.is_active') && $withdrawal->payment_method->id == (defined('MobileMoney') ? MobileMoney : '')) {
                    $data = isset($withdrawal->withdrawal_detail->mobilemoney->mobilemoney_name) ? $withdrawal->withdrawal_detail->mobilemoney->mobilemoney_name . ' (' . $withdrawal->payment_method_info . ')' : '-';
                }
                return $data;
            })
            ->editColumn('status', function ($withdrawal) {
                return getStatusLabel($withdrawal->status);
            })
            ->addColumn('action', function ($withdrawal) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_withdrawal')) ?
                    '<a href="' . url(config('adminPrefix') . '/withdrawals/edit/' . $withdrawal->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })
            ->rawColumns(['user_id', 'total', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $pm       = isset(request()->payment_methods) ? request()->payment_methods : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new Withdrawal())->getWithdrawalsList($from, $to, $status, $currency, $pm, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'withdrawals.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'withdrawals.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'uuid', 'name' => 'withdrawals.uuid', 'title' => __('UUID'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'amount', 'name' => 'withdrawals.amount', 'title' => __('Amount')])
            ->addColumn(['data' => 'fees', 'name' => 'fees', 'title' => __('Fees')])
            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => __('Total')])
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Currency')])
            ->addColumn(['data' => 'payment_method_id', 'name' => 'payment_method.name', 'title' => __('Payment Method')])
            ->addColumn(['data' => 'payment_method_info', 'name' => 'withdrawals.payment_method_info', 'title' => __('Method Info')])
            ->addColumn(['data' => 'status', 'name' => 'withdrawals.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
