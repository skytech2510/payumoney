<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Http\Helpers\Common;
use App\Models\Dispute;
use Config, Auth;
use Illuminate\Http\JsonResponse;
class DisputesDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($dispute) {
                return dateFormat($dispute->created_at);
            })->addColumn('code', function ($dispute) {
                $code = getColumnValue($dispute, 'code');
                if ($code <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_dispute') && $dispute->status != 'Reject') {
                    return '<a href="' . url(config('adminPrefix') . '/dispute/discussion/' . $dispute->id) . '">' . $code . '</a>';
                }
                return $code;
            })->addColumn('title', function ($dispute) {
                $title = $dispute->title;

                return (Common::has_permission(auth('admin')->user()->id, 'edit_dispute') && $dispute->status != 'Reject') ? '<a href="' . url(config('adminPrefix') . '/dispute/discussion/' . $dispute->id) . '">' . $title . '</a>' : $title;
            })->editColumn('claimant_id', function ($dispute) {
                $claimant = getColumnValue($dispute->claimant);
                if ($claimant <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $dispute->claimant->id) . '">' . $claimant . '</a>';
                }
                return $claimant;
            })->editColumn('defendant_id', function ($dispute) {
                $defendant = getColumnValue($dispute->defendant);
                if ($defendant <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $dispute->defendant->id) . '">' . $defendant . '</a>';
                }
                return $defendant;
            })->editColumn('transaction_id', function ($dispute) {
                $transactionUuid = getColumnValue($dispute->transaction, 'uuid');
                if ($transactionUuid <> '-' &&  Common::has_permission(auth('admin')->user()->id, 'edit_dispute')) {
                    return '<a href="' . url(config('adminPrefix') . '/transactions/edit/' . $dispute->transaction->id) . '" target="_blank">' . $transactionUuid . '</a>';
                }
                return $transactionUuid;
            })->editColumn('status', function ($dispute) {
                return getStatusLabel($dispute->status);
            })
            ->rawColumns(['code', 'title', 'claimant_id', 'defendant_id', 'transaction_id', 'status'])
            ->make(true);
    }

    public function query()
    {
        $status = isset(request()->status) ? request()->status : 'all';
        $user   = isset(request()->user_id) ? request()->user_id : null;
        $from   = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to     = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query  = (new Dispute())->getDisputesList($from, $to, $status, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'disputes.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'disputes.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'code', 'name' => 'disputes.code', 'title' => __('Dispute ID')])
            ->addColumn(['data' => 'title', 'name' => 'disputes.title', 'title' => __('Title')])
            ->addColumn(['data' => 'claimant_id', 'name' => 'claimant.last_name', 'title' => __('Claimant'), 'visible' => false])
            ->addColumn(['data' => 'claimant_id', 'name' => 'claimant.first_name', 'title' => __('Claimant')])
            ->addColumn(['data' => 'defendant_id', 'name' => 'defendant.last_name', 'title' => 'Defendant', 'visible' => false])
            ->addColumn(['data' => 'defendant_id', 'name' => 'defendant.first_name', 'title' => __('Defendant')])
            ->addColumn(['data' => 'transaction_id', 'name' => 'disputes.transaction_id', 'title' => __('Transaction ID')])
            ->addColumn(['data' => 'status', 'name' => 'disputes.status', 'title' => __('Status')])
            ->parameters(dataTableOptions());
    }
}
