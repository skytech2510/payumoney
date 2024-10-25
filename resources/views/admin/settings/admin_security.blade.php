@extends('admin.layouts.master')
@section('title', __('Security Settings'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/tagify/tagify.min.css') }}">
@endsection

@section('page_content')

    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Admin Security Form') }}</h3>
                </div>

                <form action="{{ url(config('adminPrefix').'/settings/admin-security-settings') }}" method="post" class="form-horizontal" id="SecuritySettingsForm">
                    {!! csrf_field() !!}
                    <div class="box-body">

                        <div class="form-group row">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end mt-3" for="admin-url-prefix">{{ __('Admin URL Prefix') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="admin_url_prefix" class="form-control f-14" id="admin-url-prefix" value="{{ !empty($prefData['preference']['admin_url_prefix']) ? $prefData['preference']['admin_url_prefix'] : '' }}" placeholder="{{ __('Admin URL Prefix') }}">
                                <small class="form-text text-muted f-12 url"><strong>{{ url('/') . '/' . preference('admin_url_prefix') }}</strong></small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end mt-3" for="admin_access_ip_setting">{{ __('Admin Panel IPaccess') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-14 admin_access_ip_setting select2" name="admin_access_ip_setting"
                                    id="admin_access_ip_setting">
                                    <option value='Enabled'
                                        {{ $result['admin_access_ip_setting'] == 'Enabled' ? 'selected' : '' }}>{{ __('Enabled') }}
                                    </option>
                                    <option value='Disabled'
                                        {{ $result['admin_access_ip_setting'] == 'Disabled' ? 'selected' : '' }}>{{ __('Disabled') }}
                                    </option>
                                </select>
                                <small class="form-text text-muted f-12"><strong>*{{ __('If enable, do not forget to put the IPs') }}</strong></small>
                            </div>
                        </div>

						<div class="form-group row">
                            <label class="col-sm-4 control-label f-14 fw-bold text-sm-end mt-2" for="admin_access_ips">{{ __('Admin Access IPs') }}</label>
                            <div class="col-sm-6">
                                <input name='admin_access_ips' id="admin_access_ips" value="{{ $adminAccessIPs }}" placeholder="{{ __('Enter your site url IP address') }}">
                                <small class="form-text text-muted f-12"><strong>*{{ __('Before enable, you must need to put your IPs') }}</strong></small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-theme pull-right f-14"
                            id="admin-security-settings-submit">
                            <i class="fa fa-spinner fa-spin d-none"></i> <span
                                id="admin-security-settings-submit-text">{{ __('Submit') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('public/dist/plugins/tagify/tagify.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('public/dist/plugins/tagify/jQuery.tagify.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
    'use strict';
    var site_url = '{{ url('/') }}';
    var submitText = '{{ __('Submitting..') }}'
    </script>
    <script src="{{ asset('public/admin/customs/js/settings/admin_security/admin_security.min.js') }}" type="text/javascript"></script>
@endpush
