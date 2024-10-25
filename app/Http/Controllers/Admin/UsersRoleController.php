<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\UserRolesDataTable;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Permission,
    PermissionRole,
    RoleUser,
    Role
};
use Illuminate\Support\Facades\Config;

class UsersRoleController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(UserRolesDataTable $dataTable)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'user_role';
        return $dataTable->render('admin.user_roles.view', $data);
    }

    public function add(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'user_role';

        if (!$request->isMethod('post')) {

            $data['permissions'] = Permission::where(['user_type' => 'User'])->select('id', 'group','user_type')->get();

            return view('admin.user_roles.add', $data);
        }
        else if ($request->isMethod('post')) {
            $rules = array(
                'name'         => 'required|max:30|unique:roles,name',
                'display_name' => 'required',
                'description'  => 'required',
            );

            $fieldNames = array(
                'name'         => 'Name',
                'display_name' => 'Display Name',
                'description'  => 'Description',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $role                = new Role();
                $role->name          = $request->name;
                $role->display_name  = $request->display_name;
                $role->description   = $request->description;
                $role->is_default    = $request->default;
                $role->user_type     = 'User';
                $role->customer_type = $request->customer_type;
                $role->save();

                //Only one role can have default - 'Yes'
                if ($role->is_default == 'Yes')  {
                    Role::where(['is_default' => 'Yes','customer_type' => $request->customer_type])->where('id', '!=', $role->id)->update(['is_default' => 'No']);
                }

                foreach ($request->permission as $key => $value) {
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $role->id]);
                }
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('user group')]));
                return redirect(config('adminPrefix').'/settings/user_role');
            }
        } else {
            return redirect(config('adminPrefix').'/settings/user_role');
        }
    }

    public function update(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu']   = 'user_role';
        
        if ($request->isMethod('post')) {

            $rules = array(
                'name'         => 'required|max:30|unique:roles,name,'.$request->id,
                'display_name' => 'required',
                'description'  => 'required',
            );

            $fieldNames = array(
                'name'         => 'Name',
                'display_name' => 'Display Name',
                'description'  => 'Description',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {

                return back()->withErrors($validator)->withInput();
            } else {
                $role                = Role::find($request->id);
                $role->name          = $request->name;
                $role->display_name  = $request->display_name;
                $role->description   = $request->description;
                $role->is_default    = $request->default;
                $role->user_type     = 'User';
                $role->customer_type = $request->customer_type;
                $role->save();

                //Only one role can have default - 'Yes'
                if ($role->is_default == 'Yes') {
                    Role::where(['is_default' => 'Yes','customer_type' => $request->customer_type])->where('id', '!=', $role->id)->update(['is_default' => 'No']);
                }

                $stored_permissions = Role::permission_role($request->id);
                foreach ($stored_permissions as $value) {
                    if (!in_array($value, $request->permission)) {
                        PermissionRole::where(['permission_id' => $value, 'role_id' => $request->id])->delete();
                    }
                }
                foreach ($request->permission as $value) {
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $request->id]);
                }

                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('user group')]));
                return redirect(config('adminPrefix').'/settings/user_role');
            }
        }

        $data['result'] = Role::find($request->id);
        $data['stored_permissions'] = $stored_permissions = Role::permission_role($request->id)->toArray();
        $data['permissions'] = Permission::where(['user_type' => 'User'])->select('id', 'group','user_type')->get();
        return view('admin.user_roles.edit', $data);
    }

    public function delete(Request $request)
    {
        Role::where('id', $request->id)->delete();
        PermissionRole::where('role_id', $request->id)->delete();

        $role_user = RoleUser::where(['role_id' => $request->id, 'user_type' => 'User'])->first();

        if (isset($role_user)) {
            $role_user->delete();
        }
        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('user group')]));
        return redirect(config('adminPrefix').'/settings/user_role');
    }

    public function checkUserPermissions(Request $request)
    {
        if ($request->customer_type == 'user')
        {
            $permissions = Permission::where(['user_type' => 'User'])
            ->where('group', '!=', 'Voucher')
            ->where('group', '!=', 'Merchant')
            ->where('group', '!=', 'Merchant Payment')
            ->select('id', 'group', 'user_type')->get();
        }
        else
        {
            $permissions = Permission::where(['user_type' => 'User'])->where('group', '!=', 'Voucher')->select('id', 'group', 'user_type')->get();
        }

        if (isset($request->role_id))
        {
            $stored_permissions = Role::permission_role($request->role_id)->toArray();
            return response()->json([
                'status'      => true,
                'permissions' => $permissions,
                'stored_permissions' => $stored_permissions,
            ]);
        }
        return response()->json([
            'status'      => true,
            'permissions' => $permissions,
        ]);
    }
}
