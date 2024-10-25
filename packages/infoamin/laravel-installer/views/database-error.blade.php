@extends('vendor.installer.layout')

@section('content')
    <div class="card lighten-1">
        <div class="card-content black-text">
            <p class="card-title center-align">{{ trans('installer.database-error.title') }}</p>
            <hr>
            <p>{{ trans('installer.database-error.sub-title') }}</p>
            <ol class="red-text">
                @for ($i = 1; $i < 4; $i++)
                    <li>{{ trans('installer.database-error.item' . $i) }}</li>
                @endfor
            </ol>
            <p>{{ trans('installer.database-error.message') }}</p>
        </div>
        <div class="card-action">
            <a class="btn waves-effect red waves-light" href="{{ url('install/database') }}">
                {{ trans('installer.database-error.button') }}
            </a>
        </div>
    </div>
@endsection