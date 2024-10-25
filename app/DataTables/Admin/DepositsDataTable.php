<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Config, Auth, Common;
use App\Models\Deposit;
use Illuminate\Http\JsonResponse;
class DepositsDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($deposit) {
                return dateFormat($deposit->created_at);
            })->addColumn('user_id', function ($deposit) {
                $sender = getColumnValue($deposit->user);
                if ($sender <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $deposit->user->id) . '">' . $sender . '</a>' ;
                }
                return $sender;
            })->editColumn('amount', function ($deposit) {
                return formatNumber($deposit->amount, $deposit->currency_id);
            })->addColumn('fees', function ($deposit) {
                return ($deposit->charge_percentage == 0) && ($deposit->charge_fixed == 0) ? '-' : formatNumber($deposit->charge_percentage + $deposit->charge_fixed, $deposit->currency_id);
            })->addColumn('total', function ($deposit) {
                return '<td><span class="text-' . ((($deposit->charge_percentage + $deposit->charge_fixed + $deposit->amount) > 0) ? 'green' : 'red') . '">+' . formatNumber($deposit->charge_percentage + $deposit->charge_fixed + $deposit->amount, $deposit->currency_id) . '</span></td>';
            })->editColumn('currency_id', function ($deposit) {
                return (isset($deposit->currency->code) && !empty($deposit->currency->code)) ? $deposit->currency->code : '-';
            })->editColumn('payment_method_id', function ($deposit) {
                if (isset($deposit->payment_method)) {
                    return ($deposit->payment_method->id == Mts) ? settings('name') : $deposit->payment_method?->name;
                }
            })->editColumn('status', function ($deposit) {
                return getStatusLabel($deposit->status);
            })->addColumn('action', function ($deposit) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_deposit')) ? '<a href="' . url(config('adminPrefix') . '/deposits/edit/' . $deposit->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
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
        $query    = (new Deposit())->getDepositsList($from, $to, $status, $currency, $pm, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'deposits.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'deposits.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'uuid', 'name' => 'deposits.uuid', 'title' => __('UUID'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'amount', 'name' => 'deposits.amount', 'title' => __('Amount')])
            ->addColumn(['data' => 'fees', 'name' => 'fees', 'title' => __('Fees')])
            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => __('Total')])
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Currency')])
            ->addColumn(['data' => 'payment_method_id', 'name' => 'payment_method.name', 'title' => __('Payment Method')])
            ->addColumn(['data' => 'status', 'name' => 'deposits.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
