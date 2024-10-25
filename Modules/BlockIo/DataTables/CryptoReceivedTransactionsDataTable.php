<?php

namespace Modules\BlockIo\DataTables;

use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Config;

use Illuminate\Http\JsonResponse;
class CryptoReceivedTransactionsDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        $columns = request()->columns;
        return datatables()
            ->eloquent($this->query())
            ->editColumn('txid', function ($transaction) {
                $payloadJson = json_decode(optional($transaction->cryptoAssetApiLog)->payload, true);
                return $payloadJson['txid'] ?? $payloadJson['txId'];
            })->editColumn('created_at', function ($transaction) {
                return dateFormat($transaction->created_at);
            })->addColumn('sender', function ($transaction) {
                $sender = getColumnValue($transaction->end_user);
                if ($sender <> '-' && \App\Http\Helpers\Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix').'/users/edit/' . $transaction->end_user_id) . '">'.$sender.'</a>';
                }
                return $sender;
            })->editColumn('subtotal', function ($transaction) {
                $subtotal = '<td><span class="text-green">+' . formatNumber($transaction->subtotal, $transaction->currency->id) . '</span></td>';
                return $subtotal;
            })->editColumn('currency_id', function ($transaction) {
                return optional($transaction->currency)->code;
            })->addColumn('receiver', function ($transaction) {
                $receiver = getColumnValue($transaction->user);
                if ($receiver <> '-' && \App\Http\Helpers\Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix').'/users/edit/' . $transaction->user_id) . '">'.$receiver.'</a>';
                }
                return $receiver;
            })->addColumn('action', function ($transaction) {
                return '<a href="' . route('admin.crypto_received_transaction.view', $transaction->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye" title="View"></i></a>&nbsp;';
            })
            ->rawColumns(['sender','receiver','subtotal','action'])
            ->make(true);
    }

    public function query()
    {
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $query    = (new \App\Models\Transaction())->getCryptoReceivedTransactions($from, $to, $currency, $user);
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
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Crypto Currency')])
            ->addColumn(['data' => 'receiver', 'name' => 'end_user.last_name', 'title' => __('Receiver'), 'visible' => false])
            ->addColumn(['data' => 'receiver', 'name' => 'end_user.first_name', 'title' => __('Receiver')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }

}
