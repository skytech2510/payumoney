@extends('admin.layouts.master')
@section('title', __('Update Crypto Asset') )

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/bootstrap-toggle-2.2.0/css/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/BlockIo/Resources/assets/admin/css/blockio_asset_setting.min.css') }}">
@endsection

@section('page_content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info" id="blockio-asset-edit">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Update Crypto Asset') }}</h3>
                </div>

                <form action="{{ route('admin.blockio_asset.update', encrypt($cryptoAssetSetting->network)) }}" method="POST" class="form-horizontal" enctype="multipart/form-data" id="edit-blockio-network-form">
                    @csrf

                    <input type="hidden" name="id" value="{{ encrypt($cryptoAssetSetting->id) }}">

                    <div class="box-body">
                        <!-- Name -->
                        <div class="form-group row align-items-center" id="name-div">
                            <label for="name" class="col-sm-3 control-label f-14 fw-bold text-sm-end">{{ __('Name') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="name" class="form-control f-14" value="{{ $cryptoAssetSetting->currency?->name }}" placeholder="{{ __('eg - Bitcoin or Litecoin') }}" id="name" aria-required="true" aria-invalid="false">
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>
                        </div>

                        <!-- Network / code -->
                        <div class="form-group row align-items-center" id="crypto-networks-div">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="network">{{ __('Coin/Network') }}</label>
                            <div class="col-sm-6">
                                <input type="text" value="{{ $cryptoAssetSetting->network }}" name="network" class="form-control f-14" placeholder="{{ __('Enter network code (eg - BTC)') }}" id="network">
                                <span class="text-danger">{{ $errors->first('network') }}</span>
                                <span class="network-exist-error"></span>
                            </div>
                        </div>

                        <!-- Symbol -->
                        <div class="form-group row align-items-center" id="symbol-div">
                            <label for="symbol" class="col-sm-3 control-label f-14 fw-bold text-sm-end">{{ __('Symbol') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="symbol" class="form-control f-14" value="{{ $cryptoAssetSetting->currency?->symbol }}" placeholder="{{ __('Symbol (ex - ₿)') }}" id="symbol" aria-required="true" aria-invalid="false">
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
                                    @if (fileExistCheck($cryptoAssetSetting->currency?->logo, 'currency'))
                                        <img src="{{ image($cryptoAssetSetting->currency?->logo, 'currency') }}" alt="{{ __('Currency Logo') }}" class="img-w64" id="currency-demo-logo-preview">
                                    @else
                                        <img src='{{ image(null, 'currency') }}' class="img-w64" id="currency-demo-logo-preview">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- API Key -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="api_key">{{ __('API Key') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14 api_key" name="api_key" type="text" placeholder="{{ __('Please enter valid api key') }}" value="{{ json_decode($cryptoAssetSetting->network_credentials)->api_key }}" id="api_key">
                                <span class="text-danger">{{ $errors->first('api_key') }}</span>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*Network/Crypto Currency is generated according to api key.') }}</strong></small>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*Updating API key will update corresponding crypto currency.') }}</strong></small>
                            </div>
                        </div>

                        <!-- PIN -->
                        <div class="form-group row align-items-center">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="pin">{{ __('PIN') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14 pin" name="pin" type="text" placeholder="{{ __('Please enter valid pin') }}" value="{{ json_decode($cryptoAssetSetting->network_credentials)->pin }}" id="pin">
                                <span class="text-danger">{{ $errors->first('pin') }}</span>
                            </div>
                        </div>

                        <!-- Merchant Address -->
                        <div class="form-group row align-items-center">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="address">{{ __('Merchant Address') }}</label>
                            <div class="col-sm-6">
                                <input class="form-control f-14 address" name="address" type="text" placeholder="{{ __('Please enter valid merchant address') }}" value="{{ json_decode($cryptoAssetSetting->network_credentials)->address }}" id="address">
                                <span class="text-danger">{{ $errors->first('address') }}</span>
                                <span class="address-validation-error"></span>
                            </div>
                        </div>

                        <!-- Address generate -->
                        <div class="form-group row" id="create-network-address-div">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="inputEmail3">{{ __('Create Addresses') }}</label>
                            <div class="col-sm-6">
                                <input type="checkbox" data-toggle="toggle" name="network_address" id="network-address">
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*If On, ') }}<span class="network-name"></span> {{ __('wallet addresses will be created for all registered users.') }}</strong></small>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group row">
                            <label class="col-sm-3 control-label f-14 fw-bold text-sm-end mt-11" for="status">{{ __('Status') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control f-14 status select2" name="status" id="status">
                                    <option value='Active' {{ $cryptoAssetSetting->status == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value='Inactive' {{ $cryptoAssetSetting->status == 'Inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                                <div class="clearfix"></div>
                                <small class="form-text text-muted f-12"><strong>{{ __('*Updating status will update corresponding crypto currency.') }}</strong></small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 offset-md-3">
                                <a class="btn btn-theme-danger f-14 me-1" href="{{ route('admin.crypto_providers.list', 'BlockIo') }}" >{{ __('Cancel') }}</a>
                                @if (Common::has_permission(auth('admin')->user()->id, 'edit_crypto_asset'))
                                    <button type="submit" class="btn btn-theme f-14" id="blockio-settings-edit-btn">
                                        <i class="fa fa-spinner fa-spin display-spinner"></i> <span id="blockio-settings-edit-btn-text">{{ __('Update') }}</span>
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
    'use script';
    var checkMerchantAddressUrl = '{{ route("admin.blockio_asset.check_merchant_address") }}';
    var defaultImageSource = "{{ image(null, 'currency') }}";
    var pleaseWait = '{{ __("Please Wait") }}';
    var loading = '{{ __("Loading...") }}';
    var merchantAddress = '{{ __("Merchant address does not belong to this network.") }}';
    var update = '{{ __("Update") }}';
    var updating = '{{ __("Updating...") }}';
</script>

<script src="{{ asset('Modules/BlockIo/Resources/assets/admin/js/blockio_asset_setting.min.js') }}"  type="text/javascript" ></script>
@include('common.read-file-on-change')

@endpush
