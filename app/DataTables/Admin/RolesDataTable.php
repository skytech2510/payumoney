<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Common, Config, Auth;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
class RolesDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        $role = $this->query();

        return datatables()
            ->of($role)
            ->addColumn('action', function ($role) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_role')) ? '<a href="' . url(config('adminPrefix') . '/settings/edit_role/' . $role->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';

                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_role')) ? '<a href="' . url(config('adminPrefix') . '/settings/delete_role/' . $role->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';

                return $edit . $delete;
            })
            ->addColumn('name', function ($role) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_role')) ? '<a href="' . url(config('adminPrefix') . '/settings/edit_role/' . $role->id) . '">' . ucfirst($role->name) . '</a>' : ucfirst($role->name);
            })
            ->editColumn('display_name', function ($role) {
                return ucfirst($role->display_name);
            })
            ->editColumn('description', function ($role) {
                return ucfirst($role->description);
            })
            ->rawColumns(['name', 'action'])
            ->make(true);
    }

    public function query()
    {
        $role = Role::where(['user_type' => 'Admin'])->select();
        return $this->applyScopes($role);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'roles.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'name', 'name' => 'roles.name', 'title' => __('Name')])
            ->addColumn(['data' => 'display_name', 'name' => 'roles.display_name', 'title' => __('Display Name')])
            ->addColumn(['data' => 'description', 'name' => 'roles.description', 'title' => __('Description')])
            ->addColumn(['data' => 'action', 'name'  => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
