<?php

namespace Modules\BlockIo\DataTables;

use Yajra\DataTables\Services\DataTable;
use Config;

use Illuminate\Http\JsonResponse;
class CryptoSentTransactionsDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        $columns = request()->columns;
        return datatables()
            ->eloquent($this->query())
            ->editColumn('txid', function ($transaction) {
                $payloadJson = json_decode($transaction->cryptoAssetApiLog->payload, true);
                return $payloadJson['txid'] ?? $payloadJson['txId'];
            })->editColumn('created_at', function ($transaction) {
                return dateFormat($transaction->created_at);
            })->addColumn('sender', function ($transaction) {
                $sender = getColumnValue($transaction->user);
                if ($sender <> '-' && \App\Http\Helpers\Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix').'/users/edit/' . $transaction->user_id) . '">'.$sender.'</a>';
                } 
                return $sender;
            })->editColumn('subtotal', function ($transaction) {
                return formatNumber($transaction->subtotal, $transaction->currency_id);
            })->addColumn('fees', function ($transaction) {
                return formatNumber($transaction->charge_fixed, $transaction->currency_id);
            })->editColumn('total', function ($transaction) {
                return '<td><span class="text-'. (($transaction->total > 0) ? 'green">+' : 'red">')  . formatNumber($transaction->total - json_decode(optional($transaction->cryptoAssetApiLog)->payload)->network_fee, $transaction->currency_id) . '</span></td>';
            })->editColumn('currency_id', function ($transaction) {
                return optional($transaction->currency)->code;
            })->addColumn('receiver', function ($transaction) {
                $receiver = getColumnValue($transaction->end_user);
                if ($receiver <> '-' && \App\Http\Helpers\Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                   return '<a href="' . url(config('adminPrefix').'/users/edit/' . $transaction->end_user_id) . '">'.$receiver.'</a>';
                }
                return $receiver;
            })->editColumn('status', function ($transaction) {
                return getStatusLabel($transaction->status);
            })->addColumn('action', function ($transaction) {
                return '<a href="' . route('admin.crypto_sent_transaction.view', $transaction->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye" title="View"></i></a>&nbsp;';
            })
            ->rawColumns(['sender','receiver','total', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $query    = (new \App\Models\Transaction())->getCryptoSentTransactions($from, $to, $status, $currency, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'transactions.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'txid', 'name' => 'cryptoAssetApiLog.payload', 'title' => __('Txid'), 'visible' => false])
            ->addColumn(['data' => 'uuid', 'name' => 'transactions.uuid', 'title' => __('UUID'), 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'transactions.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'sender', 'name' => 'user.last_name', 'title' => __('Sender'), 'visible' => false])
            ->addColumn(['data' => 'sender', 'name' => 'user.first_name', 'title' => __('Sender')])
            ->addColumn(['data' => 'subtotal', 'name' => 'transactions.subtotal', 'title' => __('Amount')])
            ->addColumn(['data' => 'fees', 'name' => 'fees', 'title' => __('Fees')])
            ->addColumn(['data' => 'total', 'name' => 'transactions.total', 'title' => __('Total')])
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Crypto Currency')])
            ->addColumn(['data' => 'receiver', 'name' => 'end_user.last_name', 'title' => __('Receiver'), 'visible' => false])
            ->addColumn(['data' => 'receiver', 'name' => 'end_user.first_name', 'title' => __('Receiver')])
            ->addColumn(['data' => 'status', 'name' => 'transactions.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
