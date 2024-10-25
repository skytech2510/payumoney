<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Models\CurrencyExchange;
use Common;

use Illuminate\Http\JsonResponse;
class CurrencyExchangesDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($exchange) {
                return dateFormat($exchange->created_at);
            })
            ->editColumn('user_id', function ($exchange) {
                $sender = getColumnValue($exchange->user);
                if ($sender <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $exchange->user_id) . '">' . $sender . '</a>';
                }
                return $sender;
            })
            ->editColumn('amount', function ($exchange) {
                return formatNumber($exchange->amount, $exchange->currency_id);
            })
            ->editColumn('fee', function ($exchange) {
                return ($exchange->fee == 0) ? '-' : formatNumber($exchange->fee , $exchange->currency_id);
            })
            ->addColumn('total', function ($exchange) {
                if ($exchange->type == 'Out') {
                    if (($exchange->fee + $exchange->amount) > 0) {
                        $total = '<span class="text-red">-' . formatNumber($exchange->fee + $exchange->amount, $exchange->currency_id) . '</span>';
                    } else {
                        $total = '<span class="text-green">+' . formatNumber($exchange->fee + $exchange->amount, $exchange->currency_id) . '</span>';
                    }
                } elseif ($exchange->type == 'In') {
                    if (($exchange->fee + $exchange->amount) > 0) {
                        $total = '<span class="text-green">+' .  formatNumber($exchange->fee + $exchange->amount, $exchange->currency_id) . '</span>';
                    } else {
                        $total = '<span class="text-red">' . formatNumber($exchange->fee + $exchange->amount, $exchange->currency_id) . '</span>';
                    }
                }
                return $total;
            })
            ->editColumn('exchange_rate', function ($exchange) {
                return formatNumber($exchange->exchange_rate, $exchange->currency_id);
            })
            ->addColumn('from_wallet', function ($exchange) {
               
                return optional(optional($exchange->fromWallet)->currency)->code;
            })
            ->addColumn('to_wallet', function ($exchange) {
                return optional(optional($exchange->toWallet)->currency)->code;
            })
            ->editColumn('status', function ($exchange) {
                return getStatusLabel($exchange->status);
            })
            ->addColumn('action', function ($exchange) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_exchange')) ? '<a href="' . url(config('adminPrefix') . '/exchange/edit/' . $exchange->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })
            ->rawColumns(['user_id', 'total', 'status', 'action', 'amount'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new CurrencyExchange())->getExchangesListForCsvExport($from, $to, $status, $currency, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'uuid', 'name' => 'uuid', 'title' => __('UUID'), 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'amount', 'name' => 'amount', 'title' => __('Amount')])
            ->addColumn(['data' => 'fee', 'name' => 'fee', 'title' => __('Fees')])
            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => __('Total')])
            ->addColumn(['data' => 'exchange_rate', 'name' => 'exchange_rate', 'title' => __('Rate')])
            ->addColumn(['data' => 'from_wallet', 'name' => 'fromWallet.currency.code', 'title' => __('From')])
            ->addColumn(['data' => 'to_wallet', 'name' => 'toWallet.currency.code', 'title' => __('To')])
            ->addColumn(['data' => 'status', 'name' => 'status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
