@extends('admin.layouts.master')

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules\Addons\Resources\assets\css\icon.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules\Addons\Resources\assets\css\materialize.min.css') }}">
@endsection

@section('page_content')
  <div class="card">
      <div class="card-content black-text">
              <div class="center-align">
                  <p class="card-title">{{ __('OOPS') }}</p>
                  <hr>
              </div>
              <div class="center-align">
                    @php
                        $name = explode('_', base64_decode($module));
                    @endphp
                  {{ __('Please verify your :x purchase code and username. ', ['x' => strtolower($name[0])]) }}
              </div>
      </div>
      <div class="card-action right-align">

          <a class="btn waves-effect blue waves-light red white-text" href="{{ route('addon.verify', $module) }}">
              {{ __('Verify Purchase Code') }}
              <i class="material-icons right">{{ __('send') }}</i>
          </a>
      </div>
  </div>
@endsection
