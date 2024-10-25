@extends('vendor.installer.layout')

@section('content')
    <div class="card">
        <div class="card-content black-text">
            <p class="card-title center-align">{{ __('Your application has been successfully installed!') }}</p>
            <div class="card-action center-align">
                <a class="btn waves-effect blue waves-light" href="{{ url(config('installer.login-url')) }}">
                    {{ __('Login') }}
                </a>
            </div>
        </div>
    </div>
@endsection