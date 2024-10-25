@extends('admin.layouts.master')
@section('title', __('Addon Manager'))

@section('head_style')
<!-- bootstrap-toggle -->
<link rel="stylesheet" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
@endsection

@section('page_content')
<div class="box box-default">
    <div class="box-body">
        <div class="d-flex justify-content-between">
            <div>
                <div class="top-bar-title padding-bottom pull-left">{{ __('Addons') }}</div>
            </div>
            <div>
                <a class="btn btn-theme" data-toggle="collapse" href="#addonUpload" role="button" aria-expanded="false" aria-controls="addonUpload">{{ __('Upload Addon') }}</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="collapse box box-info" id="addonUpload">
            <div class="box-body">
                <div class="row mt-30">
                    <form action="{{ url(config('adminPrefix').'/custom/addons') }}" class="form-horizontal" method="POST" id="AddonUploadForm" enctype="multipart/form-data">
                        @csrf

                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="purchase_code">{{ __('Purchase Code') }}</label>
                                <div class="col-sm-6">
                                    <input class="form-control" placeholder="{{ __('Enter addon purchase code') }}" name="purchase_code" value="{{ old('purchase_code') }}" type="text" id="purchase_code" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="envato_user_name">{{ __('Envato Username') }}</label>
                                <div class="col-sm-6">
                                    <input class="form-control" placeholder="{{ __('Enter your envato username') }}" name="envato_user_name" value="{{ old('envato_user_name') }}" type="text" id="envato_user_name" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="addon_zip">{{ __('Addon (compressed in zip format)') }}</label>
                                <div class="col-sm-6">
                                    <input type="file" name="addon_zip" id="addon_zip" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="addon-upload-submit-btn"></label>
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-theme pull-right" id="addon-upload-submit-btn"><i class="fa fa-spinner fa-spin d-none"></i> <span id="addon-upload-submit-btn-text">{{ __('Upload') }}</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach($addons as $addon)
    <div class="col-md-3">
        <div class="box box-info">
            <div class="row">
                <div class="col-md-12">
                    <img src="{{ url('public/images/addon/' . $addon->image) }}" class="setting-img addon-radius" alt="{{ __('Addon Image') }}">
                </div>
            </div>
            <div class="box-footer">
                <div class="row">
                    <div class="col-md-10">
                        <strong class="text-primary">{{ $addon->name }}</strong><br>
                        {{ __('Version') }}: {{ $addon->version }}
                    </div>
                    <div class="col-md-2">
                        <a href="{{ url(config('adminPrefix').'/custom/addon/activation', [($addon->activated == '0' ? '1' : '0'), $addon->id]) }}" class="btn btn-{{($addon->activated == '1' ? 'primary' : 'danger')}}  pull-right">{{($addon->activated == '1' ? __('Deactivate') : __('Activate'))}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<!-- bootstrap-toggle -->
<script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script>

<script>
    $('#AddonUploadForm').validate({
        rules: {
            purchase_code: {
                required: true,
            },
            envato_user_name: {
                required: true,
            },
            addon_zip: {
                required: true,
            },
        },
        submitHandler: function(form) {
            $("#addon-upload-submit-btn").attr("disabled", true);
            $(".fa-spin").removeClass("d-none");
            $("#addon-upload-submit-btn-text").text('Uploading...');
            $('#addon-upload-submit-btn').click(function(e) {
                e.preventDefault();
            });
            form.submit();
        }
    });
</script>

@endpush
