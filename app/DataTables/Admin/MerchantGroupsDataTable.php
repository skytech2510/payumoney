<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Models\MerchantGroup;
use Config, Auth, Common;
use Illuminate\Http\JsonResponse;
class MerchantGroupsDataTable extends DataTable
{

    public function ajax(): JsonResponse
    {
        $role = $this->query();

        return datatables()
            ->of($role)
            ->addColumn('name', function ($merchantGroup) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_merchant_group')) ? '<a href="' . url(config('adminPrefix') . '/settings/edit-merchant-group/' . $merchantGroup->id) . '">' . ucfirst($merchantGroup->name) . '</a>' : ucfirst($merchantGroup->name);
            })
            ->editColumn('description', function ($merchantGroup) {
                return ucfirst($merchantGroup->description);
            })
            ->editColumn('fee', function ($merchantGroup) {
                return formatNumber($merchantGroup->fee);
            })
            ->editColumn('fee_bearer', function ($merchantGroup) {
                return $merchantGroup->fee_bearer;
            })
            ->editColumn('is_default', function ($merchantGroup) {
                return isDefault($merchantGroup->is_default);
            })
            ->addColumn('action', function ($merchantGroup) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_merchant_group')) ? '<a href="' . url(config('adminPrefix') . '/settings/edit-merchant-group/' . $merchantGroup->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_merchant_group')) ? '<a href="' . url(config('adminPrefix') . '/settings/delete-merchant-group/' . $merchantGroup->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';

                return $edit . $delete;
            })
            ->rawColumns(['name', 'is_default', 'action'])
            ->make(true);
    }

    public function query()
    {
        $merchantGroup = MerchantGroup::select();
        return $this->applyScopes($merchantGroup);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn([
                'data' => 'id', 
                'name' => 'merchant_groups.id', 
                'title' => __('ID'), 
                'searchable' => false, 
                'visible' => false
            ])
            ->addColumn([
                'data' => 'name', 
                'name' => 'merchant_groups.name', 
                'title' => __('Name')
            ])
            ->addColumn([
                'data' => 'description', 
                'name' => 'merchant_groups.description', 
                'title' => __('Description')
            ])
            ->addColumn([
                'data' => 'fee', 
                'name' => 'merchant_groups.fee', 
                'title' => __('Fee (%)')
            ])
            ->addColumn([
                'data' => 'fee_bearer',
                'name' => 'merchant_groups.fee_bearer',
                'title' => __('Fee Bearer')
            ])
            ->addColumn([
                'data' => 'is_default', 
                'name' => 'merchant_groups.is_default', 
                'title' => __('Default')
            ])
            ->addColumn([
                'data'  => 'action', 
                'name'  => 'action', 
                'title' => __('Action'), 
                'orderable' => false, 
                'searchable' => false
            ])
            ->parameters(dataTableOptions());
    }
}
