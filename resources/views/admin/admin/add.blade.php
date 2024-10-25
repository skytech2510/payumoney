@php
$form_data = [
    'page_title'=> __('Add Admin User Form'),
    'page_subtitle'=> __('Add Admin'),
    'form_name' => __('Admin Add Form'),
    'action' => URL::to('/').'/admin/add_admin',
    'fields' => [
      ['type' => 'text', 'class' => 'validate_field', 'label' => __('Username'), 'name' => 'username', 'value' => ''],
      ['type' => 'text', 'class' => 'validate_field', 'label' => __('Email'), 'name' => 'email', 'value' => ''],
      ['type' => 'password', 'class' => 'validate_field', 'label' => __('Password'), 'name' => 'password', 'value' => ''],
      ['type' => 'select', 'options' =>$roles, 'class' => 'validate_field', 'label' => __('Role'), 'name' => 'role', 'value' => ''],
      ['type' => 'select', 'options' => ['Active' => 'Active', 'Inactive' => 'Inactive'], 'class' => 'validate_field', 'label' => __('Status'), 'name' => 'status', 'value' => ''],
    ]
  ];
@endphp
@include("admin.common.form.primary", $form_data)
