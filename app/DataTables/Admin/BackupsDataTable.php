<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Backup;
use Yajra\DataTables\Services\DataTable;
use Session, Config, Auth;

use Illuminate\Http\JsonResponse;
class BackupsDataTable extends DataTable
{
    public function ajax(): JsonResponse //don't use default dataTable() method
    {
        return datatables()
            ->eloquent($this->query())
            ->addColumn('action', function ($backup) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_database_backup')) ? '<a href="' . url(config('adminPrefix') . '/backup/download/' . $backup->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a>' : '';
            })
            ->make(true);
    }

    public function query()
    {
        $backup = Backup::select('backups.*');
        return $this->applyScopes($backup);
    }
    
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'backups.id', 'title' => __('Id'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'name', 'name' => 'backups.name', 'title' => __('Name')])
            ->addColumn(['data' => 'created_at', 'name' => 'backups.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
