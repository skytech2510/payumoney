<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Config, Auth, Common;
use App\Models\Country;

use Illuminate\Http\JsonResponse;
class CountriesDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @return \Yajra\Datatables\Engines\BaseEngine
     */
    public function ajax(): JsonResponse //don't use default dataTable() method
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('is_default', function ($country) {
                return isDefault($country->is_default);
            })
            ->addColumn('action', function ($country) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_country')) ? '<a href="' . url(config('adminPrefix') . '/settings/edit_country/' . $country->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_country')) ? '<a href="' . url(config('adminPrefix') . '/settings/delete_country/' . $country->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';

                return $edit . $delete;
            })
            ->rawColumns(['is_default', 'action'])
            ->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        $query = Country::select();
        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'countries.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'short_name', 'name' => 'countries.short_name', 'title' => __('Short Name')])
            ->addColumn(['data' => 'name', 'name' => 'countries.name', 'title' => __('Long Name')])
            ->addColumn(['data' => 'iso3', 'name' => 'countries.iso3', 'title' => __('Iso3')])
            ->addColumn(['data' => 'number_code', 'name' => 'countries.number_code', 'title' => __('Num Code')])
            ->addColumn(['data' => 'phone_code', 'name' => 'countries.phone_code', 'title' => __('Phone Code')])
            ->addColumn(['data' => 'is_default', 'name' => 'countries.is_default', 'title' => __('Default')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
