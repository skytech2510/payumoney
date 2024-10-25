<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Admin;
use Yajra\DataTables\Services\DataTable;
use Session, Config, Auth;

use Illuminate\Http\JsonResponse;
class AdminsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('first_name', function ($admin) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_admin')) ? '<a href="' . url(config('adminPrefix') . '/admin-user/edit/' . $admin->id) . '">' . $admin->first_name . '</a>' : $admin->first_name;
            })->editColumn('last_name', function ($admin) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_admin')) ? '<a href="' . url(config('adminPrefix') . '/admin-user/edit/' . $admin->id) . '">' . $admin->last_name . '</a>' : $admin->last_name;
            })->addColumn('role', function ($admin) {
                return (isset($admin->role->display_name) && !empty($admin->role->display_name)) ? $admin->role->display_name : '-';
            })->editColumn('status', function ($admin) {
                return getStatusLabel($admin->status);
            })->addColumn('action', function ($admin) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_admin')) ? '<a href="' . url(config('adminPrefix') . '/admin-user/edit/' . $admin->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
                $delete = ($admin->id != 1 && Common::has_permission(auth('admin')->user()->id, 'delete_admin')) ? '<a href="' . url(config('adminPrefix') . '/admin-user/delete/' . $admin->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';
                return $edit . $delete;
            })
            ->rawColumns(['first_name', 'last_name', 'status', 'action'])
            ->make(true);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Admin::with('role:id,display_name')->select('admins.id', 'admins.first_name', 'admins.last_name', 'admins.email', 'admins.role_id', 'admins.status');
        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'admins.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'first_name', 'name' => 'admins.first_name', 'title' => __('First Name')])
            ->addColumn(['data' => 'last_name', 'name' => 'admins.last_name', 'title' => __('Last Name')])
            ->addColumn(['data' => 'email', 'name' => 'admins.email', 'title' => __('Email')])
            ->addColumn(['data' => 'role', 'name' => 'role', 'title' => __('Group')])
            ->addColumn(['data' => 'status', 'name' => 'admins.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
