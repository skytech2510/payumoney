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
                    <p class="f-14 mb-0">{{ __('Current verion') }} : <b>{{ $currentVersion }}</b></p>
                </div>
            </div>
            <div class="card-body row">
                <div class="col-sm-2"></div>
                <div class="col-sm-8">

                    <div class="text-center mt-3 font-bold">{!! $message !!}</div>

                    <div class="text-center alert alert-primary mt-3 font-bold">
                        <p class="mb-1">{{ __('Latest Version') }}</p>
                        <p class="mb-0">{{ $latestVersion }}</p>
                    </div>
                    
                    @if ($status == 'success')
                        <div class="d-flex justify-content-start alert alert-warning-deep">
                            <b>{{ __('Before performing an update, it is strongly recommended to create a full backup of your current installation (files and database) and review the changelog') }}
                            <a href="https://docs.paymoney.techvill.net/backup-paymoney-files-and-database/" target="_blank"> <i class="fa fa-external-link ms-1"></i> {{ __('See backup documentation') }}</a>
                            </b>
                        </div>

                        <div class="mt-5">
                            <form action="{{ route('version.download') }}" class="form-horizontal from-class-id" id="password-form" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="version" value="{{ $latestVersion }}">

                                <!-- Purchase code -->
                                <div class="form-group row">
                                    <label for="purchaseCode"
                                        class="col-sm-4 text-center col-form-label require f-14 text-gray-200">{{ __('Purchase code') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control inputFieldDesign f-14 text-gray-200" id="purchaseCode"
                                            name="purchase_code" placeholder="{{ __('Purchase code') }}" required
                                            oninvalid="this.setCustomValidity('{{ __('This field is required.') }}')">
                                    </div>
                                </div>

                                <div class="col-sm-12 px-0 m-l-10 mt-3 pr-0 d-flex justify-content-end">
                                    <button class="btn custom-btn-submit f-14" type="submit">{{ __('Download Now') }}</button>
                                </div>
                            </form>
                        </div>
                    @endif
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


