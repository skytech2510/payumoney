@extends('admin.layouts.master')
@section('title', __('Add New Asset') )

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumIo/Resources/assets/admin/css/tatumio_asset_setting.min.css') }}">
@endsection

@section('page_content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info" id="tatumio-asset-create">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Add New Asset') }}</h3>
                </div>

                <form action="{{ route('admin.tatumio_asset.store') }}" method="POST" class="row form-horizontal" enctype="multipart/form-data" id="add-tatumio-network-form">
                    @csrf
                    <div class="box-body">
                        <!-- Name -->
                        <div class="form-group align-items-center row" id="name-div">
                            <label for="name" class="col-sm-3 control-label f-14 fw-bold text-sm-end">{{ __('Name') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="name" class="form-control f-14" value="{{ old('name') }}" placeholder="{{ __('eg - Bitcoin or Litecoin') }}" id="name" aria-required="true" aria-invalid="false" required data-value-missing="{{ __("This field is required.") }}">
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>
                        </div>

                        <!-- Network / code -->
                        <div class="form-group align-items-center row" id="crypto-networks-div">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="network">{{ __('Coin/Network') }}</label>
                            <div class="col-sm-6">
                                <input type="text" value="{{ old('network') }}" name="network" class="form-control f-14" placeholder="{{ __('Enter network code (eg - BTC)') }}" id="network" required data-value-missing="{{ __("This field is required.") }}">
                                <span class="text-danger">{{ $errors->first('network') }}</span>
                                <span class="network-exist-error"></span>
                            </div>
                        </div>


                        <!-- Symbol -->
                        <div class="form-group align-items-center row" id="symbol-div">
                            <label for="symbol" class="col-sm-3 control-label f-14 fw-bold text-sm-end">{{ __('Symbol') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="symbol" class="form-control f-14" value="{{ old('symbol') }}" placeholder="Symbol (eg - ₿)" id="symbol" aria-required="true" aria-invalid="false" required data-value-missing="{{ __("This field is required.") }}">
                                <span class="text-danger">{{ $errors->first('symbol') }}</span>
                            </div>
                        </div>

                        <!-- Logo -->
                        <div class="form-group row" id="logo-div">
                            <label for="currency-logo" class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11">{{ __('Logo') }}</label>
                            <div class="col-sm-4">
                                <input type="file" name="logo" class="form-control f-14 input-file-field" id="currency-logo">
                                <span class="text-danger">{{ $errors->first('logo') }}</span>
                                <div class="clearfix"></div>
                              <small class="form-text text-muted f-12"><strong>{{ allowedImageDimension(64,64) }}</strong></small>
                            </div>
                            <div class="col-sm-2">
                                <div class="pull-right setting-img">
                                    <img src="{{ image(null, 'currency') }}" class="img-w64" id="currency-demo-logo-preview">
                                </div>
                            </div>
                        </div>

                        <!-- API Key -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="api_key">{{ __('API Key') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14 api_key" name="api_key" type="text" placeholder="{{ __('Please enter valid api key') }}" value="{{ old('api_key') }}" id="api_key" required data-value-missing="{{ __("This field is required.") }}">
                                <span class="text-danger">{{ $errors->first('api_key') }}</span>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*Network/Crypto Currency is generated according to api key.') }}</strong></small>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*Updating API key will update corresponding crypto currency.') }}</strong></small>
                            </div>
                        </div>

                        <!-- Address generate -->
                        <div class="form-group row" id="create-network-address-div">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="network-address">{{ __('Create Addresses') }}</label>
                            <div class="col-sm-6">
                                <input type="checkbox" data-toggle="toggle" name="create_address" id="network-address">
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*If On, ') }}<span class="network-name"></span> {{ __('wallet addresses will be created for all registered users.') }}</strong></small>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="status">{{ __('Status') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-14 status select2" name="status" id="status">
                                    <option value='Active'>{{ __('Active') }}</option>
                                    <option value='Inactive'>{{ __('Inactive') }}</option>
                                </select>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*Updating status will update corresponding crypto currency.') }}</strong></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 offset-md-3">
                                <a class="btn btn-theme-danger f-14 me-1" href="{{ route('admin.crypto_providers.list', 'TatumIo') }}" >{{ __('Cancel') }}</a>
                                @if (Common::has_permission(auth('admin')->user()->id, 'add_crypto_asset'))
                                    <button type="submit" class="btn btn-theme f-14" id="tatumio-settings-submit-btn">
                                        <i class="fa fa-spinner fa-spin display-spinner"></i> <span id="tatumio-settings-submit-btn-text">{{ __('Submit') }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/debounce-1.1/jquery.ba-throttle-debounce.min.js')}}" type="text/javascript"></script>
<script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js')}}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"  type="text/javascript" ></script>

<script>
    'use strict';
    var defaultImageSource = '{{ url("public/user_dashboard/images/favicon.png") }}';
    var pleaseWait = '{{ __("Please Wait") }}';
    var loading = '{{ __("Loading...") }}';
    var submit = '{{ __("Submit") }}';
    var submitting = '{{ __("Submitting...") }}';
</script>

<script src="{{ asset('Modules/TatumIo/Resources/assets/admin/js/tatumio_asset_setting.min.js') }}"  type="text/javascript"></script>
@include('common.read-file-on-change')
@endpush
