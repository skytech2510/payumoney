<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Models\Transaction;
use Config, Auth, Common;
use Illuminate\Http\JsonResponse;
class EachUserTransactionsDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($transaction) {
                return dateFormat($transaction->created_at);
            })->addColumn('sender', function ($transaction) {

                $senderWithLink = getTransactionListUser($transaction);
                if (!is_null($senderWithLink)) {
                    return $senderWithLink;
                }

                $senderWithLink = '-';
                switch ($transaction->transaction_type_id) {
                    case Deposit:
                    case Transferred:
                    case Exchange_From:
                    case Exchange_To:
                    case Request_Sent:
                    case Withdrawal:
                    case Payment_Sent:
                        if (isset($transaction->user->first_name) && !empty($transaction->user->first_name)) {
                            $sender = $transaction->user->first_name . ' ' . $transaction->user->last_name;
                            $senderWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->user_id) . '">' . $sender . '</a>' : $sender;
                        }
                        break;
                    case (module('CryptoExchange') ? Crypto_Buy : false):
                    case (module('CryptoExchange') ? Crypto_Sell : false):
                    case (module('CryptoExchange') ? Crypto_Swap : false):
                        if (isset($transaction->user->first_name) && !empty($transaction->user->first_name)) {
                            $sender = $transaction->user->first_name . ' ' . $transaction->user->last_name;
                            $senderWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->user_id) . '">' . $sender . '</a>' : $sender;
                        } elseif (isset($transaction->crypto_exchange->email_phone) && !empty($transaction->crypto_exchange->email_phone)) {
                           $senderWithLink = isset($transaction->crypto_exchange->email_phone) ?  $transaction->crypto_exchange->email_phone : '' ;
                        }
                        break;
                    case Received:
                    case Request_Received:
                    case Payment_Received:
                        if (isset($transaction->end_user->first_name) && !empty($transaction->end_user->first_name)) {
                            $sender = $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name;
                            $senderWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->end_user_id) . '">' . $sender . '</a>' : $sender;
                        }
                        break;
                }
                return $senderWithLink;
            })->addColumn('receiver', function ($transaction) {
                
                $receiverWithLink = getTransactionListUser($transaction, 'receiver');
                if (!is_null($receiverWithLink)) {
                    return $receiverWithLink;
                }

                $receiverWithLink = '-';
                switch ($transaction->transaction_type_id) {
                    case Deposit:
                    case Exchange_From:
                    case Exchange_To:
                    case Withdrawal:
                    case Payment_Sent:
                        if (isset($transaction->end_user->first_name) && !empty($transaction->end_user->first_name)) {
                            $receiver = $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name;
                            $receiverWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->end_user_id) . '">' . $receiver . '</a>' : $receiver;
                        }
                        break;
                    case Transferred:
                        if (isset($transaction->end_user->first_name) && !empty($transaction->end_user->first_name)) {
                            $receiver = $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name;
                            $receiverWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->end_user_id) . '">' . $receiver . '</a>' : $receiver;
                        } else {
                            if (isset($transaction->transfer->email) && !empty($transaction->transfer->email)) {
                                $receiverWithLink = $transaction->transfer->email;
                            } elseif (isset($transaction->transfer->phone) && !empty($transaction->transfer->phone)) {
                                $receiverWithLink = $transaction->transfer->phone;
                            }
                        }
                        break;
                    case Received:
                        if (isset($transaction->user->first_name) && !empty($transaction->user->first_name)) {
                            $receiver = $transaction->user->first_name . ' ' . $transaction->user->last_name;
                            $receiverWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->user_id) . '">' . $receiver . '</a>' : $receiver;
                        } else {
                            if (isset($transaction->transfer->email) && !empty($transaction->transfer->email)) {
                                $receiverWithLink = $transaction->transfer->email;
                            } elseif (isset($transaction->transfer->phone) && !empty($transaction->transfer->phone)) {
                                $receiverWithLink = $transaction->transfer->phone;
                            }
                        }
                        break;
                    case Request_Sent:
                        if (isset($transaction->end_user->first_name) && !empty($transaction->end_user->first_name)) {
                            $receiver = $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name;
                            $receiverWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->end_user_id) . '">' . $receiver . '</a>' : $receiver;
                        } else {
                            if (isset($transaction->request_payment->email) && !empty($transaction->request_payment->email)) {
                                $receiverWithLink = $transaction->request_payment->email;
                            } elseif (isset($transaction->request_payment->phone) && !empty($transaction->request_payment->phone)) {
                                $receiverWithLink = $transaction->request_payment->phone;
                            }
                        }
                        break;
                    case Request_Received:
                        if (isset($transaction->user->first_name) && !empty($transaction->user->first_name)) {
                            $receiver = $transaction->user->first_name . ' ' . $transaction->user->last_name;
                            $receiverWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->user_id) . '">' . $receiver . '</a>' : $receiver;
                        } else {
                            if (isset($transaction->request_payment->email) && !empty($transaction->request_payment->email)) {
                                $receiverWithLink = $transaction->request_payment->email;
                            } elseif (isset($transaction->request_payment->phone) && !empty($transaction->request_payment->phone)) {
                                $receiverWithLink = $transaction->request_payment->phone;
                            }
                        }
                        break;
                    case Payment_Received:
                        if (isset($transaction->user->first_name) && !empty($transaction->user->first_name)) {
                            $receiver = $transaction->user->first_name . ' ' . $transaction->user->last_name;
                            $receiverWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $transaction->user_id) . '">' . $receiver . '</a>' : $receiver;
                        }
                        break;
                }
                return $receiverWithLink;
            })->editColumn('transaction_type_id', function ($transaction) {
                return isset($transaction->transaction_type->name) ? str_replace('_', ' ', $transaction->transaction_type->name) : '';
            })->editColumn('subtotal', function ($transaction) {
                return isset($transaction->currency->type) && ($transaction->currency->type != 'fiat') ? $transaction->subtotal : formatNumber($transaction->subtotal);
            })->addColumn('fees', function ($transaction) {
                return (($transaction->charge_percentage == 0) && ($transaction->charge_fixed == 0) ? '-' : ($transaction->currency->type != 'fiat' ? $transaction->charge_fixed : formatNumber($transaction->charge_percentage + $transaction->charge_fixed)));
            })->editColumn('total', function ($transaction) {
                $total = isset($transaction->currency->type) && $transaction->currency->type != 'fiat' ? $transaction->total : formatNumber($transaction->total);
                return '<td><span class="text-' . (($transaction->total > 0) ? 'green' : 'red') . '">' . $total . '</span></td>';
            })->editColumn('currency_id', function ($transaction) {
                return isset($transaction->currency->code) ? $transaction->currency->code : '';
            })->editColumn('status', function ($transaction) {
                return getStatusLabel($transaction->status);
            })->addColumn('action', function ($transaction) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_transaction')) ? '<a href="' . url(config('adminPrefix') . '/transactions/edit/' . $transaction->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })->rawColumns(['sender', 'receiver', 'total', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $type     = isset(request()->type) ? request()->type : 'all';
        $user     = $this->user_id;
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new Transaction())->getEachUserTransactionsList($from, $to, $status, $currency, $type, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'transactions.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'uuid', 'name' => 'transactions.uuid', 'title' => __('UUID'), 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'transactions.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'sender', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'sender', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'transaction_type_id', 'name' => 'transaction_type.name', 'title' => __('Type')])
            ->addColumn(['data' => 'subtotal', 'name' => 'transactions.subtotal', 'title' => __('Amount')])
            ->addColumn(['data' => 'fees', 'name' => 'fees', 'title' => 'Fees'])
            ->addColumn(['data' => 'total', 'name' => 'transactions.total', 'title' => __('Total')])
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Currency')])
            ->addColumn(['data' => 'receiver', 'name' => 'end_user.last_name', 'title' => __('Receiver'), 'visible' => false])
            ->addColumn(['data' => 'receiver', 'name' => 'end_user.first_name', 'title' => __('Receiver')])
            ->addColumn(['data' => 'status', 'name' => 'transactions.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
