<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Common, Config, Auth;
use App\Models\Pages;

use Illuminate\Http\JsonResponse;
class PagesDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        $page = $this->query();

        return datatables()
            ->of($page)
            ->addColumn('action', function ($page) {
                $edit = $delete = '';
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_page')) ? '<a href="' . url(config('adminPrefix') . '/settings/page/edit/' . $page->id) . '"  class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_page')) ? '<a href="' . url(config('adminPrefix') . '/settings/page/delete/' . $page->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';
                return $edit . $delete;
            })
            ->addColumn('name', function ($page) {
                return '<a href="' . url(config('adminPrefix') . '/settings/page/edit/' . $page->id) . '">' . $page->name . '</a>';
            })
            ->addColumn('url', function ($page) {
                return '<a target="_blank" href="' . url($page->url) . '">' . $page->url . '</a>';
            })
            ->addColumn('position', function ($page) {
                return $page->position;
            })
            ->editColumn('status', function ($page) {
                return ucfirst($page->status);
            })
            ->rawColumns(['url', 'name', 'action'])
            ->make(true);
    }

    public function query()
    {
        $page = Pages::select();
        return $this->applyScopes($page);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'pages.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'name', 'name' => 'pages.name', 'title' => __('Name')])
            ->addColumn(['data' => 'url', 'name' => 'pages.url', 'title' => __('Url')])
            ->addColumn(['data' => 'position', 'name' => 'pages.position', 'title' => __('Position')])
            ->addColumn(['data' => 'status', 'name' => 'pages.status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
