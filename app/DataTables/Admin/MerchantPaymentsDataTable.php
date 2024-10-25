<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Models\MerchantPayment;
use Config, Auth, Common;

use Illuminate\Http\JsonResponse;
class MerchantPaymentsDataTable extends DataTable
{

    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($merchant_payment) {
                return dateFormat($merchant_payment->created_at);
            })->editColumn('merchant_id', function ($merchant_payment) {
                $merchant = getColumnValue($merchant_payment->merchant->user);
                if ($merchant <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $merchant_payment->merchant->user->id) . '">' . $merchant . '</a>';
                }
                return $merchant;
            })->editColumn('user_id', function ($merchant_payment) {
                $user = getColumnValue($merchant_payment->user);
                if ($user <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $merchant_payment->user->id) . '">' . $user . '</a>' ;
                }
                return $user;
            })->editColumn('amount', function ($merchant_payment) {
                return formatNumber($merchant_payment->amount, $merchant_payment->currency_id, $merchant_payment->currency_id);
            })->addColumn('fees', function ($merchant_payment) {
                return ($merchant_payment->charge_percentage == 0) && ($merchant_payment->charge_fixed == 0) ? "-" : formatNumber($merchant_payment->charge_percentage + $merchant_payment->charge_fixed, $merchant_payment->currency_id);
            })->editColumn('total', function ($merchant_payment) {
                $total = $merchant_payment->charge_percentage + $merchant_payment->charge_fixed + $merchant_payment->amount;
                if ($total > 0) {
                    if ($merchant_payment->status == 'Refund') {
                        $total = '<td><span class="text-red">-' . formatNumber($total, $merchant_payment->currency_id) . '</span></td>';
                    } else {
                        $total = '<td><span class="text-green">+' . formatNumber($total, $merchant_payment->currency_id) . '</span></td>';
                    }
                } else {
                    $total = '<td><span class="text-red">' . formatNumber($total, $merchant_payment->currency_id) . '</span></td>';
                }
                return $total;
            })->editColumn('currency_id', function ($merchant_payment) {
                return isset($merchant_payment->currency->code) && !empty($merchant_payment->currency->code) ? $merchant_payment->currency->code : '-';
            })->editColumn('payment_method_id', function ($merchant_payment) {
                return ($merchant_payment->payment_method->name == "Mts") ? settings('name') : $merchant_payment->payment_method->name;
            })->editColumn('status', function ($merchant_payment) {
                return getStatusLabel($merchant_payment->status);
            })->addColumn('action', function ($merchant_payment) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_merchant_payment')) ? '<a href="' . url(config('adminPrefix') . '/merchant_payments/edit/' . $merchant_payment->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })
            ->rawColumns(['total', 'merchant_id', 'user_id', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $pm       = isset(request()->payment_methods) ? request()->payment_methods : 'all';
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new MerchantPayment())->getMerchantPaymentsList($from, $to, $status, $currency, $pm);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'merchant_payments.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'merchant_payments.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'uuid', 'name' => 'merchant_payments.uuid', 'title' => __('UUID'), 'visible' => false])
            ->addColumn(['data' => 'merchant_id', 'name' => 'merchant.user.last_name', 'title' => __('Merchant U Last Name'), 'visible' => false])
            ->addColumn(['data' => 'merchant_id', 'name' => 'merchant.user.first_name', 'title' => __('Merchant')])
            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => __('User Last Name'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'amount', 'name' => 'merchant_payments.amount', 'title' => __('Amount')])
            ->addColumn(['data' => 'fees', 'name' => 'fees', 'title' => 'Fees'])
            ->addColumn(['data' => 'total', 'name' => 'merchant_payments.total', 'title' => __('Total')])
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Currency')])
            ->addColumn(['data' => 'payment_method_id', 'name' => 'payment_method.name', 'title' => __('Payment Method')])
            ->addColumn(['data' => 'status', 'name' => 'merchant_payments.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
