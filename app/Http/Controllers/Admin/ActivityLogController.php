<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\ActivityLogsDataTable;
use App\Http\Controllers\Controller;

class ActivityLogController extends Controller
{
    public function activities_list(ActivityLogsDataTable $dataTable)
    {
        $data['menu'] = 'activity_logs';
        return $dataTable->render('admin.activity_logs.list', $data);
    }
}
