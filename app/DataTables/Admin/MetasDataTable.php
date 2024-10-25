<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Common, Config, Auth;
use App\Models\Meta;
use Illuminate\Http\JsonResponse;
class MetasDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     */
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('keywords', function ($seo_metas) {
                return isset($seo_metas->keywords) ? $seo_metas->keywords : '-';
            })
            ->addColumn('action', function ($seo_metas) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_meta')) ? '<a href="' . url(config('adminPrefix') . '/settings/edit_meta/' . $seo_metas->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })
            ->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     */
    public function query()
    {
        $query = Meta::select();
        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'metas.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'url', 'name' => 'metas.url', 'title' => __('Url')])
            ->addColumn(['data' => 'title', 'name' => 'metas.title', 'title' => __('Title')])
            ->addColumn(['data' => 'description', 'name' => 'metas.description', 'title' => __('Description')])
            ->addColumn(['data' => 'keywords', 'name' => 'metas.keywords', 'title' => __('Keywords')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
