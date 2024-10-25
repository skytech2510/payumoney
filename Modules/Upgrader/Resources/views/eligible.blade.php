@extends('admin.layouts.master')
@section('title', __('System Information'))

@section('page_content')
    <!-- Main content -->
    <div>
        <div class="card min-h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <h5 class="f-18 mb-0">{{ __('Update your system') }}</h5>
                </div>
            </div>
            <div class="card-body row">
                <div class="col-sm-2"></div>
                <div class="col-sm-8">
                    <div class="row align-items-center justify-content-center alert alert-{{ $upgrader['status'] ? 'secondary' : 'danger' }} mt-3">
                        <div class="col">
                            <h5 class="f-18 mb-0">{{ $upgrader['message'] }}</h5>
                        </div>
                        @if ($upgrader['status'])
                            <div class="col-auto">
                                <a href="{{ route('systemUpdate.upgrade', ['waiting' => true]) }}" class="btn custom-btn-update" id="update_now">{{ __('Update Now') }}</a>
                            </div>
                        @endif
                    </div>
                    @if ($upgrader['status'])
                    <div class="row alert alert-warning-deep">
                        {!! $upgrader['json']['description'] !!}
                    </div>
                    @elseif (isset($upgrader['needPermission']) && $upgrader['needPermission'])
                        <table>
                            <tr>
                                <th>{{ __('Directory Name') }}</th>
                                <th>{{ __('Need Permission') }}</th>
                            </tr>
                            @foreach ($upgrader['permissionRequire'] as $directory)
                                <tr>
                                    <td>{{ $directory }}</td>
                                    <td class="text-center">777</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
                <div class="col-sm-2"></div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
@endsection
