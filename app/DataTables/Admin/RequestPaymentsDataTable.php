<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Models\RequestPayment;
use Auth, Config, Common;
use Illuminate\Http\JsonResponse;
class RequestPaymentsDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($request_payment) {
                return dateFormat($request_payment->created_at);
            })->addColumn('sender', function ($request_payment) {
                $sender = getColumnValue($request_payment->user);
                if ($sender <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $request_payment->user->id) . '">' . $sender . '</a>';
                }
                return $sender;
            })->editColumn('amount', function ($request_payment) {
                return '<td><span class="text-' . (($request_payment->amount > 0) ? 'green">+' : 'red">') . formatNumber($request_payment->amount, $request_payment->currency_id) . '</span></td>';
            })->editColumn('accept_amount', function ($request_payment) {
                return '<td><span class="text-' . (($request_payment->accept_amount > 0) ? 'green">+' : 'red">') . formatNumber($request_payment->accept_amount, $request_payment->currency_id) . '</span></td>';
            })->editColumn('currency_id', function ($request_payment) {
                return isset($request_payment->currency->code) && !empty($request_payment->currency->code) ? $request_payment->currency->code : '';
            })->addColumn('receiver', function ($request_payment) {
                if (isset($request_payment->receiver->first_name) && !empty($request_payment->receiver->first_name)) {
                    $receiver = $request_payment->receiver->first_name . ' ' . $request_payment->receiver->last_name;
                    $receiverWithLink = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $request_payment->receiver->id) . '">' . $receiver . '</a>' : $receiver;
                } else {
                    if (!empty($request_payment->email)) {
                        $receiver = $request_payment->email;
                        $receiverWithLink = $receiver;
                    } elseif (!empty($request_payment->phone)) {
                        $receiver         = $request_payment->phone;
                        $receiverWithLink = $receiver;
                    } else {
                        $receiver         = '-';
                        $receiverWithLink = $receiver;
                    }
                }
                return $receiverWithLink;
            })->editColumn('status', function ($request_payment) {
                return getStatusLabel($request_payment->status);
            })->addColumn('action', function ($request_payment) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_request_payment')) ?
                    '<a href="' . url(config('adminPrefix') . '/request_payments/edit/' . $request_payment->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })
            ->rawColumns(['sender', 'amount', 'accept_amount', 'receiver', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new RequestPayment())->getRequestPaymentsList($from, $to, $status, $currency, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'request_payments.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'request_payments.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'uuid', 'name' => 'request_payments.uuid', 'title' => __('UUID'), 'visible' => false])
            ->addColumn(['data' => 'sender', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'sender', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'amount', 'name' => 'request_payments.amount', 'title' => __('Requested Amount')])
            ->addColumn(['data' => 'accept_amount', 'name' => 'request_payments.accept_amount', 'title' => __('Accepted Amount')])
            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => __('Currency')])
            ->addColumn(['data' => 'receiver', 'name' => 'receiver.last_name', 'title' => __('Receiver'), 'visible' => false])
            ->addColumn(['data' => 'receiver', 'name' => 'receiver.first_name', 'title' => __('Receiver')])
            ->addColumn(['data' => 'status', 'name' => 'request_payments.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])

            ->parameters(dataTableOptions());
    }
}
