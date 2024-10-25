<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Config, Auth, Common;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
class MerchantsDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($merchant) {
                return dateFormat($merchant->created_at);
            })->editColumn('business_name', function ($merchant) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_merchant')) ? '<a href="' . url(config('adminPrefix') . '/merchant/edit/' . $merchant->id) . '">' . $merchant->business_name . '</a>' : $merchant->business_name;
            })->addColumn('user', function ($merchant) {
                $user = getColumnValue($merchant->user);
                if ($user <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $merchant->user->id) . '">' . $user . '</a>';
                }
                return $user;
            })->editColumn('merchant_group_id', function ($merchant) {
                return isset($merchant->merchant_group) ? $merchant->merchant_group->name : '';
            })->editColumn('logo', function ($merchant) {
                return '<img src="' . image($merchant->logo, 'merchant') . '" class="img-w80">';
            })
            ->editColumn('status', function ($merchant) {
                return getStatusLabel($merchant->status);
            })
            ->addColumn('action', function ($merchant) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_merchant')) ? '<a href="' . url(config('adminPrefix') . '/merchant/edit/' . $merchant->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })
            ->rawColumns(['business_name', 'user', 'logo', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $from     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new Merchant())->getMerchantsList($from, $to, $status, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'merchants.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'user', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'merchants.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'merchant_uuid', 'name' => 'merchants.merchant_uuid', 'title' => __('ID')])
            ->addColumn(['data' => 'type', 'name' => 'merchants.type', 'title' => __('Type')])
            ->addColumn(['data' => 'business_name', 'name' => 'merchants.business_name', 'title' => __('Name')])
            ->addColumn(['data' => 'user', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'site_url', 'name' => 'merchants.site_url', 'title' => __('Url')])
            ->addColumn(['data' => 'merchant_group_id', 'name' => 'merchant_group.name', 'title' => __('Group')])
            ->addColumn(['data' => 'logo', 'name' => 'merchants.logo', 'class' => 'text-center', 'title' => __('Logo')])
            ->addColumn(['data' => 'status', 'name' => 'merchants.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
