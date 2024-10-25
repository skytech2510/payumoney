<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Config, Auth, Common;
use App\Models\User;
use Illuminate\Http\JsonResponse;
class UsersDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('first_name', function ($user) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ?
                    '<a href="' . url(config('adminPrefix') . '/users/edit/' . $user->id) . '">' . $user->first_name . '</a>' : $user->first_name;
            })
            ->editColumn('last_name', function ($user) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ?
                    '<a href="' . url(config('adminPrefix') . '/users/edit/' . $user->id) . '">' . $user->last_name . '</a>' : $user->last_name;
            })
            ->editColumn('phone', function ($user) {
                return !empty($user->formattedPhone) ? $user->formattedPhone : '-';
            })
            ->addColumn('role', function ($user) {
                return isset($user->role->display_name) ? $user->role->display_name : '-';
            })
            ->addColumn('last_login_at', function ($user) {
                return isset($user->user_detail->last_login_at) && !empty($user->user_detail->last_login_at) ? \Carbon\Carbon::createFromTimeStamp(strtotime($user->user_detail->last_login_at))->diffForHumans() : '-';
            })
            ->addColumn('last_login_ip', function ($user) {
                return !empty($user->user_detail->last_login_ip) ? $user->user_detail->last_login_ip : '-';
            })
            ->addColumn('status', function ($user) {
                $status = '';

                if ($user->document_verification->count() > 0) {
                    foreach ($user->document_verification as $document) {
                        if (isset($document->user->address_verified)) {
                            if ($document->user->address_verified && $document->user->identity_verified && $document->status == 'approved') {
                                $status = getStatusLabel($document->user->status) . '<br>' . '<span class="label label-primary">Identity Verified</span>' .
                                        '<br>' . '<span class="label label-info">Address Verified</span>';
                            } elseif ($document->user->address_verified && !$document->user->identity_verified && $document->status == 'approved') {
                                $status = getStatusLabel($document->user->status) . '<br>' . '<span class="label label-info">Address Verified</span>';
                            } elseif (!$document->user->address_verified && $document->user->identity_verified && $document->status == 'approved') {
                                $status = getStatusLabel($document->user->status) . '<br>' . '<span class="label label-primary">Identity Verified</span>';
                            } elseif (!$document->user->address_verified && !$document->user->identity_verified && $document->status != 'approved') {
                                $status = getStatusLabel($document->user->status);
                            }
                        }
                    }
                } else {
                    $status = getStatusLabel($user->status);
                }
                return $status;
            })
            ->addColumn('action', function ($user) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/users/edit/' . $user->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit f-14"></i></a>&nbsp;' : '';
                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_user')) ? '<a href="' . url(config('adminPrefix') . '/users/delete/' . $user->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';
                return $edit . $delete;
            })
            ->rawColumns(['first_name', 'last_name', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $query = User::with(['document_verification:id,user_id,status', 'role:id,display_name', 'user_detail:id,user_id,last_login_at,last_login_ip'])->select('users.*');
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'users.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'status', 'name' => 'document_verification.status', 'title' => __('Document Verification Status'), 'visible' => false])
            ->addColumn(['data' => 'first_name', 'name' => 'users.first_name', 'title' => __('First Name')])
            ->addColumn(['data' => 'last_name', 'name' => 'users.last_name', 'title' => __('Last Name')])
            ->addColumn(['data' => 'phone', 'name' => 'users.phone', 'title' => __('Phone')])
            ->addColumn(['data' => 'email', 'name' => 'users.email', 'title' => __('Email')])
            ->addColumn(['data' => 'role', 'name' => 'role', 'title' => __('Group')])
            ->addColumn(['data' => 'last_login_at', 'name' => 'user_detail.last_login_at', 'title' => __('Last Login')])
            ->addColumn(['data' => 'last_login_ip', 'name' => 'user_detail.last_login_ip', 'title' => __('IP')])
            ->addColumn(['data' => 'status', 'name' => 'users.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
