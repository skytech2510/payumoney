@extends('vendor.installer.layout')

@section('content')
    <div class="card darken-1">
        <div class="card-content black-text">
            <div class="center-align">
                <p class="card-title">{{ __('Pay Money') }}</p>
                <p><em>{{ env('APP_VERSION') }}</em></p>
                <hr>
                <p class="card-title">{{ __('Welcome to the Installer !') }}</p>
            </div>
            <p class="center-align">{{ __('Easy installation and setup wizard') }}</p>
        </div>
        <div class="card-action right-align">
            <a class="btn waves-effect blue waves-light" href="{{ url('install/requirements') }}">
                {{ __('Start with checking requirements') }}
                <i class="material-icons right">send</i>
            </a>
        </div>
    </div>
@endsection