<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
class RevenuesDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($revenue) {
                return dateFormat($revenue->created_at);
            })
            ->editColumn('transaction_type_id', function ($revenue) {
                return str_replace('_', ' ', $revenue->transaction_type?->name) ?: '';
            })
            ->editColumn('charge_percentage', function ($revenue) {
                return ($revenue->charge_percentage == 0) ?  '-' : formatNumber($revenue->charge_percentage, $revenue->currency_id);
            })
            ->editColumn('charge_fixed', function ($revenue) {
                return ($revenue->charge_fixed == 0) ?  '-' : formatNumber($revenue->charge_fixed, $revenue->currency_id);
            })
            ->addColumn('total', function ($revenue) {
                return '<td><span class="text-'. ((calculateFee($revenue) > 0) ? 'green">+' : 'red">')  . formatNumber(calculateFee($revenue), $revenue->currency_id) . '</span></td>';
            })
            ->editColumn('currency_id', function ($revenue) {
                return $revenue->currency?->code ?: '';
            })
            ->rawColumns(['total'])
            ->make(true);
    }

    public function query()
    {
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $type     = isset(request()->type) ? request()->type : 'all';
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new Transaction())->getRevenuesList($from, $to, $currency, $type);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'transactions.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'transactions.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'transaction_type_id', 'name' => 'transaction_type.name', 'title' => __('Transaction Type')])
            ->addColumn(['data' => 'charge_percentage', 'name' => 'transactions.charge_percentage', 'title' => __('Percentage Charge')])
            ->addColumn(['data' => 'charge_fixed', 'name' => 'transactions.charge_fixed', 'title' => __('Fixed Charge')])
            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => __('Total')])
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Currency')])
            ->parameters(dataTableOptions());
    }
}
