@php
$form_data = [
    'page_title'=> __('Edit Admin User Form'),
    'page_subtitle'=> __('Edit Admin'),
    'form_name' => __('Admin Edit Form'),
    'action' => URL::to('/').'/admin/edit_admin/'.$result->id,
    'fields' => [
      ['type' => 'text', 'class' => 'validate_field', 'label' => __('Username'), 'name' => 'username', 'value' => $result->username],
      ['type' => 'text', 'class' => 'validate_field', 'label' => __('Email'), 'name' => 'email', 'value' => $result->email],
      ['type' => 'password', 'class' => 'validate_field', 'label' => __('Password'), 'name' => 'password', 'value' => '', 'hint' => __('Enter new password only. Leave blank to use existing password.')],
      ['type' => 'select', 'options' =>$roles, 'class' => 'validate_field', 'label' => 'Role', 'name' => 'role', 'value' => $role_id],
      ['type' => 'select', 'options' => ['Active' => 'Active', 'Inactive' => 'Inactive'], 'class' => 'validate_field', 'label' => __('Status'), 'name' => 'status', 'value' => $result->status],
    ]
  ];
@endphp
@include("admin.common.form.primary", $form_data)
