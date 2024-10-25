<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\LanguagesDataTable;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use Common, File, Config;
use App\Models\Language;

class LanguageController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(LanguagesDataTable $dataTable)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'language';

        return $dataTable->render('admin.languages.view', $data);
    }

    public function add(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'language';

        if ($request->isMethod('post')) {
            $this->validate($request, [
                'name' => 'required|unique:languages,name',
                'short_name' => 'required',
                'flag' => ['nullable', new CheckValidFile(getFileExtensions(3))]
            ]);

            $language = new Language();
            $language->name = $request->name;
            $language->short_name = $request->short_name;
            $language->status = $request->status;

            if ($request->hasFile('flag')) {
                $flag = $request->file('flag');
                if (isset($flag)) {
                    $response = uploadImage($flag, getDirectory('language_flag'), '80*60');
                    if ($response['status']) {
                        $language->flag = $response['file_name'];
                    }
                }
            }
            $language->save();

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('language')]));
            return redirect(config('adminPrefix').'/settings/language');
        }

        return view('admin.languages.add', $data);
    }

    public function update(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'language';
            
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'name' => 'required|unique:languages,name,' . $request->id,
                'short_name' => 'required',
                'flag' => ['nullable', new CheckValidFile(getFileExtensions(3))]
            ]);
            $language = Language::find($request->id);
            $language->name = $request->name;
            $language->short_name = $request->short_name;

            if ($request->hasFile('flag')) {
                $flag = $request->file('flag');
                if (isset($flag)) {
                    $response = uploadImage($flag, getDirectory('language_flag'), '80*60', $language->flag);
                    if ($response['status']) {
                        $language->flag = $response['file_name'];
                    }
                }
            }
            $language->status = $request->status;
            $language->save();

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('backup')]));
            return redirect(config('adminPrefix').'/settings/language');
        } 

        $data['result'] = Language::find($request->id);
        return view('admin.languages.edit', $data);
    }

    public function deleteFlag(Request $request)
    {
        $data = [
            'success' => false,
            'message' => __("No Record Found!"),
        ];

        $flag = $request->flag;

        if (isset($flag)) {
            $language = Language::where(['id' => $request->language_id, 'flag' => $request->flag])->first();
            if (!is_null($language)) {
                $isUpdated = $language->update(['flag' => null]);
                if ($isUpdated) {
                    if ($isUpdated && $file = fileExistCheck($flag, 'language_flag')) {
                        File::delete($file);
                    }
                }
                $data = [
                    'success' => true,
                    'message' => __('The :x has been successfully deleted.', ['x' => __('flag')])
                ];
            }
        }
        echo json_encode($data);
        exit();
    }

    public function delete(Request $request)
    {
        $language = Language::where('id', $request->id)->first();

        if (!is_null($language)) {
            $isDeleted = $language->delete();

            if ($isDeleted && $file = fileExistCheck($language->flag, 'language_flag')) {
                File::delete($file);
            }
        }

        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('language')]));
        return redirect(config('adminPrefix').'/settings/language');
    }
}