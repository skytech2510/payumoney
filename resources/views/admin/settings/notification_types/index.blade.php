@extends('admin.layouts.master')
@section('title', __('Notification Types'))

@section('page_content')
    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="tabs">
                      <li class="nav-item border-0"><a class="nav-link active" href="{{ url(config('adminPrefix').'/settings/notification-types') }}">{{ __('Notification Types') }}</a></li>
                      <li class="nav-item border-0"><a class="nav-link" href="{{ url(config('adminPrefix').'/settings/notification-settings/email') }}">{{ __('Email Notification Settings') }}</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade in show active" id="tab_1">
                            <div class="box-body f-14" >
                                @if($notificationTypes->count() > 0)
                                    <table class="table table-responsive text-center">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($notificationTypes as $notificationType)
                                                <tr>
                                                    <td>{{ $notificationType->name}} </td>
                                                    @if($notificationType->status =='Inactive')
                                                        <td><span class="label label-danger">{{ $notificationType->status }}</span></td>

                                                    @elseif($notificationType->status =='Active')
                                                        <td><span class="label label-success">{{ $notificationType->status }}</span></td>
                                                    @endif
                                                    <td>
                                                        <a href="{{ url(config('adminPrefix').'/settings/notification-types/edit/'.$notificationType->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <h5 class="pt-3 px-4">{{ __('Notifications not found!') }}</h5>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
