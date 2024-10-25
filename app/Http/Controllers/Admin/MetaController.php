<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\MetasDataTable;
use App\Http\Controllers\Controller;
use Validator, Config, Common;
use Illuminate\Http\Request;
use App\Models\Meta;

class MetaController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(MetasDataTable $dataTable)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'meta';
        return $dataTable->render('admin.metas.view', $data);
    }

    public function update(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu']   = 'meta';

        if (!$request->isMethod('post')) {
            $data['result'] = Meta::find($request->id);
            return view('admin.metas.edit', $data);
        } else if ($request->isMethod('post')) {
            $rules = array(
                'url'         => 'required|unique:metas,url,' . $request->id,
                'title'       => 'required',
                'description' => 'required',
            );

            $fieldNames = array(
                'url'         => 'Url',
                'title'       => 'Title',
                'description' => 'Description',
                'keywords'    => 'Keywords',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $metas              = Meta::find($request->id);
                $metas->url         = $request->url;
                $metas->title       = $request->title;
                $metas->description = $request->description;
                $metas->keywords    = $request->keywords;
                $metas->save();
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('meta')]));

                return redirect(config('adminPrefix').'/settings/metas');
            }
        }
    }

    public function delete(Request $request)
    {
        Meta::find($request->id)->delete();
        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('meta')]));

        return redirect(config('adminPrefix').'/settings/metas');
    }
}
