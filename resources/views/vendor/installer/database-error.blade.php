@extends('vendor.installer.layout')

@section('content')
    <div class="card lighten-1">
        <div class="card-content black-text">
            <p class="card-title center-align">{{ __('Database connection error') }}</p>
            <hr>
            <p>{{ __('We cant connect to database with your settings :') }}</p>
            <ol class="red-text">
                <li>{{ __('Are you sure of your username and password ?') }}</li>
                <li>{{ __('Are you sure of your host name ?') }}</li>
                <li>{{ __('Are you sure that your database server is working ?') }}</li>
            </ol>
            <p>{{ __('If your are not very sure to understand all these terms you should contact your hoster.') }}</p>
        </div>
        <div class="card-action">
            <a class="btn waves-effect red waves-light" href="{{ url('install/database') }}">
                {{ __('Try again !') }}
            </a>
        </div>
    </div>
@endsection