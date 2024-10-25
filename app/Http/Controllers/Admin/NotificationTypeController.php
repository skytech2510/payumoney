<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationType;
use Validator, Config, Common;
use Illuminate\Http\Request;

class NotificationTypeController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'notification-settings';
        
        $condition = !isActive('Investment') ? 'Investment' : null;

        $data['notificationTypes'] = NotificationType::select(['id', 'name', 'status'])->where('name', '!=' , $condition)->get();

        return view('admin.settings.notification_types.index', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  [int]  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['menu'] = 'settings';
        $data['settings_menu']             = 'notification-settings';
        $data['notificationType'] = $notificationType = NotificationType::find($id);

        if (empty($notificationType))
        {
            $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('notification type')]));
            return redirect(config('adminPrefix').'/settings/notification-types');
        }

        return view('admin.settings.notification_types.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  [int]                       $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $notificationType = NotificationType::find($id);

        if (empty($notificationType))
        {
            $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('notification type')]));
            return redirect(config('adminPrefix').'/settings/notification-types');
        }

        $rules = array(
            'notification_type_name'   => 'required|unique:notification_types,name,' . $id,
            'notification_type_status' => 'required',
        );

        $fieldNames = array(
            'notification_type_name'   => 'Notification type name',
            'notification_type_status' => 'Notification type status',

        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }

        $notificationType->name   = $request->notification_type_name;
        $notificationType->status = $request->notification_type_status;

        if ($notificationType->save())
        {
            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('notification type')]));
            return redirect(config('adminPrefix').'/settings/notification-types');
        }
        else
        {
            $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('notification type')]));
            return redirect(config('adminPrefix').'/settings/notification-types');
        }

    }


    /**
     * Check the specified unique name.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  [int]                       $id
     * @return \Illuminate\Http\Response
     */
    public function uniqueNotificationTypeNameCheck(Request $request)
    {
        $req_name = $request->notification_type_name;
        $req_id   = base64_decode($request->notification_type_id);

        $notificationTypeName = NotificationType::where(['name' => $req_name])->where(function ($query) use ($req_id)
        {
            $query->where('id', '!=', $req_id);
        })->exists();

        if ($notificationTypeName)
        {
            $data['status'] = false;
            $data['fail']   = __('The :x is already exist.', ['x' => __('notification type name')]);
        }
        else
        {
            $data['status'] = true;
            $data['fail']   = "Available.";
        }
        echo json_encode($data);
    }
}
