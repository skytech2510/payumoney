<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Common, Config, Auth;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
class UserRolesDataTable extends DataTable
{

    public function ajax(): JsonResponse
    {
        $role = $this->query();

        return datatables()
            ->of($role)
            ->addColumn('name', function ($role) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_group')) ?
                    '<a href="' . url(config('adminPrefix') . '/settings/edit_user_role/' . $role->id) . '">' . ucfirst($role->name) . '</a>' : ucfirst($role->name);
            })
            ->editColumn('display_name', function ($role) {
                return ucfirst($role->display_name);
            })
            ->editColumn('description', function ($role) {
                return ucfirst($role->description);
            })
            ->editColumn('customer_type', function ($role) {
                return ucfirst($role->customer_type);
            })
            ->editColumn('is_default', function ($role) {
                return isDefault($role->is_default);
            })
            ->addColumn('action', function ($role) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_group')) ? '<a href="' . url(config('adminPrefix') . '/settings/edit_user_role/' . $role->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';

                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_group')) ? '<a href="' . url(config('adminPrefix') . '/settings/delete_user_role/' . $role->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';

                return $edit . $delete;
            })
            ->rawColumns(['name', 'is_default', 'action'])
            ->make(true);
    }

    public function query()
    {
        $role = Role::where(['user_type' => 'User'])->select();
        return $this->applyScopes($role);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'roles.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'name', 'name' => 'roles.name', 'title' => __('Name')])
            ->addColumn(['data' => 'display_name', 'name' => 'roles.display_name', 'title' => __('Display Name')])
            ->addColumn(['data' => 'description', 'name' => 'roles.description', 'title' => __('Description')])
            ->addColumn(['data' => 'customer_type', 'name' => 'roles.customer_type', 'title' => __('User Type')]) //new
            ->addColumn(['data' => 'is_default', 'name' => 'roles.is_default', 'title' => __('Default')])
            ->addColumn(['data'  => 'action', 'name'  => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
