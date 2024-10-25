@extends('admin.layouts.master')
@section('title', __('Admin Profile'))
@section('page_content')
    <div class="row">   
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="p-1 d-flex mt-3 flex-column justify-content-center">

                        <img src='{{ image($admin->picture, 'profile') }}' class="profile-user-img img-responsive img-circle h-100p" id="admin-picture-preview" alt="{{ __('Admin profile picture') }}">

                        <h3 class="profile-username text-center">{{ getColumnValue($admin) }}</h3>
                        <div class="px-1 col-sm-6 offset-3">
                            <a class="btn btn-primary d-block f-14" href="{{ url(config('adminPrefix') . '/change-password') }}""><b>{{ __('Change Password') }}</b></a>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form action="{{ url(config('adminPrefix') . '/update-admin', $admin->id) }}" method="POST" class="form-horizontal" enctype="multipart/form-data" id="profile_form">
                            @csrf
                            <input type="hidden" value="{{ json_encode(getFileExtensions(3)) }}" id="extensions">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-2 offset-1 control-label f-14 fw-bold text-sm-end" for="first_name">{{ __('First Name') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" value="{{ $admin->first_name }}" class="form-control f-14" id="first_name" name="first_name">
                                    <span class="text-danger" id="val_fname"></span>
                                    <span class="text-danger f-14">{{ $errors->first('first_name') }}</span>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-2 offset-1 control-label f-14 fw-bold text-sm-end" for="last_name">{{ __('Last name') }}</label>
                                <div class="col-sm-6">
                                    <input type="text" value="{{ $admin->last_name}}" class="form-control f-14" id="last_name" name="last_name">
                                    <span class="text-danger" id="val_lname"></span>
                                    <span class="text-danger f-14">{{ $errors->first('last_name') }}</span>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-2 offset-1 control-label f-14 fw-bold text-sm-end" for="email">{{ __('Email') }}</label>
                                <div class="col-sm-6">
                                    <input type="email" value="{{ $admin->email}}" class="form-control f-14" id="email" name="email" readonly>
                                    <span class="text-danger" id="val_em"></span>
                                    <span class="text-danger f-14">{{ $errors->first('email') }}</span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="admin-picture" class="col-sm-2 offset-1 control-label f-14 fw-bold text-sm-end">{{ __('Picture') }}</label>
                                <div class="col-sm-6">
                                    <input type="file" name="picture" class="form-control f-14 input-file-field" id="admin-picture">
                                    <small class="form-text text-muted f-12"><strong>{{ allowedImageDimension(100,100) }}</strong></small>
                                    <div class="clear-fix"></div>
                                    <span class="text-danger f-14">{{ $errors->first('picture') }}</span>
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <div class="offset-3 col-sm-10">
                                    <button type="submit" class="btn btn-primary btn-flat f-14 rounded" id="update">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
@include('common.read-file-on-change')

<script type="text/javascript">
    'use strict';
    let extensions = JSON.parse($('#extensions').val());
	let extensionsValidationRule = extensions.join('|');
	let extensionsValidation = extensions.join(', ');
	let errorMessage = '{{ __("Please select (:x) file.") }}';
	let extensionsValidationMessage = errorMessage.replace(':x', extensionsValidation);
    let defaultImageSource = '{{ url("public/uploads/userPic/default-image.png") }}';
</script>
<script src="{{ asset('public/admin/customs/js/admin_profile/admin_profile.min.js') }}" type="text/javascript"></script>
@endpush

