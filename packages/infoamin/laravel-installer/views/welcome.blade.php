@extends('vendor.installer.layout')

@section('content')
    <div class="card darken-1">
        <div class="card-content black-text">
            <div class="center-align">
                <p class="card-title">{{ trans('installer.welcome.name') }}</p>
                <p><em>{{ trans('installer.welcome.version') }}</em></p>
                <hr>
                <p class="card-title">{{ trans('installer.welcome.title') }}</p>
            </div>
            <p class="center-align">{{ trans('installer.welcome.message') }}</p>
        </div>
        <div class="card-action right-align">
            <a class="btn waves-effect blue waves-light" href="{{ url('install/requirements') }}">
                {{ trans('installer.welcome.requirementcheckingbutton') }}
                <i class="material-icons right">send</i>
            </a>
        </div>
    </div>
@endsection