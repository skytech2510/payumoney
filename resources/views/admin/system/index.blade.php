@extends('admin.layouts.master')
@section('title', __('System Information'))

@section('page_content')
    <!-- Main content -->
    <div class="col-sm-12">
        <div class="card mt-3">
            <div class="card-header py-2 bg-white border-bottom">
                
                <div class="d-flex justify-content-between align-items-center py-2">
                    <h5 class="f-18 mb-0">{{ __('Application Information') }}</h5>
                    <p class="f-14 mb-0"><b><a href="{{ url(config('adminPrefix').'/system-update') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> {{ __('Back') }}</a></b></p>
                </div>
            </div>
            <div class="card-body p-l-15 row f-17">
                <div class="col-lg-9 col">
                    <p>{{ __('Application Version') }}</p>
                </div>
                <div class="col-lg-3 col">
                    <p>{{ $applicationVersion }}</p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-3 bg-white border-bottom">
                <h5 class="f-18 mb-0">{{ __('Server Information') }}</h5>
            </div>
            <div class="card-body p-l-15">
                <table class="table table-striped table-hover f-14">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Software Name') }}</th>
                            <th scope="col">{{ __('Current Version') }}</th>
                            <th scope="col">{{ __('Required Version') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Php</td>
                            <td>{{ $phpVersion }}</td>
                            <td>{{ $minimumPhpVersion }}</td>
                            <td>
                                @if(version_compare($phpVersion, $minimumPhpVersion, '>='))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                            <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>MySQL</td>
                            <td>{{ $mysqlVersion }}</td>
                            <td>{{ $minimumMysqlVersion }}</td>
                            <td>
                                @if(version_compare($mysqlVersion, $minimumMysqlVersion, '>='))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-3 bg-white border-bottom">
                <h5 class="f-18 mb-0">php.ini {{ __('Configuration') }}</h5>
            </div>
            <div class="card-body p-l-15 table-responsive pt-2">
                <table class="table table-striped table-hover f-14">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Configuration Name') }}</th>
                            <th scope="col">{{ __('Current') }}</th>
                            <th scope="col">{{ __('Recommended') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>file_uploads</td>
                            <td>{{ $configurations['file_uploads'] }}</td>
                            <td>On</td>
                            <td>
                                @if($configurations['file_uploads'] === 'On')
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_file_uploads</td>
                            <td>{{ $configurations['max_file_uploads'] }}</td>
                            <td>20+</td>
                            <td>
                                @if((int)$configurations['max_file_uploads'] >= 20)
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>upload_max_filesize</td>
                            <td>{{ $configurations['upload_max_filesize'] }}</td>
                            <td>128M+</td>
                            <td>
                                @if((int)str_replace('M', '', $configurations['upload_max_filesize']) >= 128)
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>post_max_size</td>
                            <td>{{ $configurations['post_max_size'] }}</td>
                            <td>128M+</td>
                            <td>
                                @if((int)str_replace('M', '', $configurations['post_max_size']) >= 128)
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>allow_url_fopen</td>
                            <td>{{ $configurations['allow_url_fopen'] }}</td>
                            <td>On</td>
                            <td>
                                @if($configurations['allow_url_fopen'] === 'On')
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_execution_time</td>
                            <td>{{ $configurations['max_execution_time'] }}</td>
                            <td>600+</td>
                            <td>
                                @if((int)$configurations['max_execution_time'] >= 600)
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_input_time</td>
                            <td>{{ $configurations['max_input_time'] }}</td>
                            <td>120+</td>
                            <td>
                                @if((int)$configurations['max_input_time'] >= 120)
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_input_vars</td>
                            <td>{{ $configurations['max_input_vars'] }}</td>
                            <td>1000+</td>
                            <td>
                                @if((int)$configurations['max_input_vars'] >= 1000)
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>memory_limit</td>
                            <td>{{ $configurations['memory_limit'] }}</td>
                            <td>256M+</td>
                            <td>
                                @if((int)str_replace('M', '', $configurations['memory_limit']) >= 256)
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-3 bg-white border-bottom">
                <h5 class="f-18 mb-0">{{ __('Extension Information') }}</h5>
            </div>
            <div class="card-body p-l-15">
                <table class="table table-striped table-hover f-14">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Extension Name') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>json</td>
                            <td>
                                @if(in_array("json", $extensionArray))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>mbstring</td>
                            <td>
                                @if(in_array("mbstring", $extensionArray))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>openssl</td>
                            <td>
                                @if(in_array("openssl", $extensionArray))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>tokenizer</td>
                            <td>
                                @if(in_array("tokenizer", $extensionArray))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>curl</td>
                            <td>
                                @if(in_array("curl", $extensionArray))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>pdo</td>
                            <td>
                                @if(in_array("pdo", $extensionArray))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-3 bg-white border-bottom">
                <h5 class="f-18 mb-0">{{ __('File System Permission') }}</h5>
            </div>
            <div class="card-body p-l-15">
                <table class="table table-striped table-hover f-14">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('File or Folder') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fileSystemPaths as $fileSystemPath)
                        <tr>
                            <td>{{ $fileSystemPath }}</td>
                            <td>
                                @if(is_writable(base_path($fileSystemPath)))
                                <i class="fa fa-check-circle fa-2x text-success"></i>
                                @else
                                <i class="fa fa-times-circle fs-2 text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

