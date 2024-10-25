<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\MerchantGroupsDataTable;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\MerchantGroup;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use Illuminate\Support\Facades\Config;

class MerchantGroupController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(MerchantGroupsDataTable $dataTable)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'merchant_group';
        return $dataTable->render('admin.merchant_group.list', $data);
    }

    public function add(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'merchant_group';

        if ($request->isMethod('post')) {
            $rules = array(
                'name'        => 'required|unique:merchant_groups,name',
                'description' => 'required',
                'fee'         => 'required|numeric',
                'fee_bearer'   => [
                    'required', 'string', \Illuminate\Validation\Rule::in(['Merchant', 'User'])
                ]
            );

            $fieldNames = array(
                'name'        => 'Name',
                'description' => 'Description',
                'fee'         => 'Fee',
                'fee_bearer'   => 'Fee Bearer'
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $merchantGroup              = new MerchantGroup();
                $merchantGroup->name        = $request->name;
                $merchantGroup->description = $request->description;
                $merchantGroup->fee         = $request->fee;
                $merchantGroup->fee_bearer   = $request->fee_bearer;
                $merchantGroup->is_default  = $request->default;
                $merchantGroup->save();

                if ($merchantGroup->is_default == 'Yes') {
                    MerchantGroup::where(['is_default' => 'Yes'])->where('id', '!=', $merchantGroup->id)->update(['is_default' => 'No']);
                }
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant group')]));
                return redirect(config('adminPrefix').'/settings/merchant-group');
            }
        }

        $data['merchantGroups'] = MerchantGroup::get();
        return view('admin.merchant_group.add', $data);
    }

    public function update(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'merchant_group';

        if ($request->isMethod('post')) {
            $rules = array(
                'name'        => 'required|unique:merchant_groups,name,' . $request->id,
                'description' => 'required',
                'fee'         => 'required|numeric',
                'fee_bearer'   => [
                    'required', 'string', \Illuminate\Validation\Rule::in(['Merchant', 'User'])
                ]
            );

            $fieldNames = array(
                'name'        => 'Name',
                'description' => 'Description',
                'fee'         => 'Fee',
                'fee_bearer'   => 'Fee Bearer'
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $merchantGroup              = MerchantGroup::find($request->id,['id','name','description','fee','is_default']);
                $merchantGroup->name        = $request->name;
                $merchantGroup->description = $request->description;
                $merchantGroup->fee         = $request->fee;
                $merchantGroup->fee_bearer   = $request->fee_bearer;
                $merchantGroup->is_default  = $request->default;
                $merchantGroup->save();

                if ($merchantGroup->is_default == 'Yes') {
                    MerchantGroup::where(['is_default' => 'Yes'])->where('id', '!=', $merchantGroup->id)->update(['is_default' => 'No']);
                }
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant group')]));
                return redirect(config('adminPrefix').'/settings/merchant-group');
            }
        }

        $data['merchantGroup'] = $merchantGroup = MerchantGroup::find($request->id);
        return view('admin.merchant_group.edit', $data);
    }

    public function delete(Request $request)
    {
        $merchantGroup = MerchantGroup::find($request->id);
        if (isset($merchantGroup) && $merchantGroup->is_default == 'Yes') {
            $this->helper->one_time_message('error', __('The default :x cannot be deleted.', ['x' => __('merchant group')]));
        } else {
            if (isset($merchantGroup)) {
                $merchantGroup->delete();
                $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('merchant group')]));
            }
        }
        return redirect(config('adminPrefix').'/settings/merchant-group');
    }
}
