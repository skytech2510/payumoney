

@extends('admin.layouts.master')
@section('title', __('System Update'))

@section('head_style')
    <link rel="stylesheet" href="{{ asset('Modules/Upgrader/Resources/assets/css/style.min.css') }}">
@endsection

@section('page_content')
    <!-- Main content -->
    <div>
        <div class="card min-h-100">
            <div class="custom-header">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <h5>{{ __('Update your system') }}</h5>
                    <p class="f-14 mb-0">{{ __('Current verion') }} : <b>{{ $applicationVersion }}</b></p>
                </div>
            </div>
            <div class="card-body row">
                <div class="col-sm-2"></div>
                <div class="col-sm-8">
                    <div class="d-flex justify-content-start alert alert-secondary mt-3 f-14 update-system">
                        <ul>
                            <li>{{ __('Make sure your server has matched with all requirements.') }} <a href="{{ route('systemInfo.index') }}">{{ __('Check Here') }}</a>
                            </li>
                            <li>{{ __('Download latest version Paymoney from codecanyon.') }}  <a href="https://codecanyon.net/downloads" target="_blank"><i class="feather icon-external-link"></i> {{ __('Click here') }}</a></li>
                            <li>{{ __('Extract downloaded zip. You will find updates.zip file in those extraced files.') }}
                            </li>
                            <li>{{ __('Upload that zip file here and click update now.') }}</li>
                            <li>{{ __('If you are using any addon make sure to update those addons after system updated.') }}</li>
                            <li>{{ __('A successful update will lose custom works.') }}</li>
                        </ul>
                    </div>
                    <div class="d-flex justify-content-start alert alert-warning-deep">
                        <b>{{ __('Before performing an update, it is strongly recommended to create a full backup of your current installation (files and database) and review the changelog') }}
                           <a href="https://docs.paymoney.techvill.net/backup-paymoney-files-and-database/" target="_blank"> <i class="fa fa-external-link ms-1"></i> {{ __('See backup documentation') }}</a>
                        </b>
                    </div>

                    <div class="col-sm-12 px-0 m-l-10 mt-3 pr-0 d-flex justify-content-end">
                        <a href="{{ route('version.check')}}">
                            <button class="btn btn-success f-14" >{{ __('Auto update') }}</button>
                        </a>
                    </div>


                    <div class="mt-5">
                        <form action="{{ route('systemUpdate.upgrade') }}" class="form-horizontal from-class-id" id="password-form" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Envato Username -->
                            <div class="form-group row">
                                <label for="envatoUsername"
                                    class="col-sm-4 text-center col-form-label require f-14 text-gray-200">{{ __('Envato Username') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control inputFieldDesign f-14 text-gray-200" id="envatoUsername"
                                        name="envatoUsername" placeholder="{{ __('Envato Username') }}" required
                                        oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                </div>
                            </div>

                            <!-- Purchase code -->
                            <div class="form-group row">
                                <label for="purchaseCode"
                                    class="col-sm-4 text-center col-form-label require f-14 text-gray-200">{{ __('Purchase code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control inputFieldDesign f-14 text-gray-200" id="purchaseCode"
                                        name="purchaseCode" placeholder="{{ __('Purchase code') }}" required
                                        oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                </div>
                            </div>

                            <!-- Zip File -->
                            <div class="form-group row">
                                <label
                                    class="col-sm-4 text-center col-form-label require f-14 text-gray-200">{{ __('Upload Zip File') }}</label>
                                <div class="col-sm-8">
                                    <div class="custom-file position-relative">
                                        <input type="file" class="form-control attachment f-14 text-gray-200"
                                            name="attachment" id="validatedCustomFile" value="" required
                                            oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 px-0 m-l-10 mt-3 pr-0 d-flex justify-content-end">
                                <a href="" class="btn btn-theme-danger me-2 f-14" type="submit">{{ __('Cancel') }}</a>
                                <button class="btn custom-btn-submit f-14" type="submit">{{ __('Upload Now') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-sm-2"></div>
            </div>
        </div>
    </div>
@endsection
@push('extra_body_scripts')
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
    <script src="{{ asset('Modules/Upgrader/Resources/assets/js/update.min.js') }}"></script>
@endpush
