<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CountriesDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use Common, Config;

class CountryController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(CountriesDataTable $dataTable)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'country';
        return $dataTable->render('admin.countries.view', $data);
    }

    public function add(Request $request)
    {
        if (!$request->isMethod('post'))
        {
            $data['menu'] = 'settings';
            $data['settings_menu'] = 'country';
            return view('admin.countries.add', $data);
        }
        else if ($request->isMethod('post'))
        {
            $this->validate($request, [
                'short_name'  => 'required|unique:countries,short_name',
                'name'        => 'required',
                'iso3'        => 'required|max:3',
                'number_code' => 'required|numeric',
                'phone_code'  => 'required|numeric',
                'is_default'  => 'required',
            ]);

            $country              = new Country();
            $country->short_name  = $request->short_name;
            $country->name        = $request->name;
            $country->iso3        = $request->iso3;
            $country->number_code = $request->number_code;
            $country->phone_code  = $request->phone_code;
            if ($request->is_default == 'yes') {
                if (Country::where(['is_default' => 'yes'])->update(['is_default' => 'no'])) {
                    $country->is_default = 'yes';
                }
            } else {
                $country->is_default = 'no';
            }
            $country->save();
            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('country')]));
            return redirect(config('adminPrefix').'/settings/country');
        }

    }

    public function update(Request $request)
    {
        if (!$request->isMethod('post'))
        {
            $data['menu'] = 'settings';
            $data['settings_menu']   = 'country';
            $data['result'] = Country::find($request->id);
            return view('admin.countries.edit', $data);
        }
        else if ($request->isMethod('post'))
        {
            $this->validate($request, [
                'short_name'  => 'required|unique:countries,short_name,' . $request->id,
                'name'        => 'required',
                'iso3'        => 'required|max:3',
                'number_code' => 'required|numeric',
                'phone_code'  => 'required|numeric',
            ]);

            $country              = Country::find($request->id);
            $country->short_name  = $request->short_name;
            $country->name        = $request->name;
            $country->iso3        = $request->iso3;
            $country->number_code = $request->number_code;
            $country->phone_code  = $request->phone_code;
            if ($request->is_default == 'yes') {
                if (Country::where(['is_default' => 'yes'])->update(['is_default' => 'no'])) {
                    $country->is_default = 'yes';
                }
            } else if ($request->is_default == 'no') {
                $country->is_default = 'no';
            }
            $country->save();
            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('country')]));
            return redirect(config('adminPrefix').'/settings/country');
        }
    }

    public function delete(Request $request)
    {
        $country = Country::find($request->id);
        if ($country->is_default == 'yes') {
            $this->helper->one_time_message('error', __('The default :x cannot be deleted.', ['x' => __('country')]));
            return redirect(config('adminPrefix').'/settings/country');
        }
        Country::find($request->id)->delete();
        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('country')]));
        return redirect(config('adminPrefix').'/settings/country');
    }
}
