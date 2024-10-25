@extends('vendor.installer.layout')

@section('content')
    <div class="card">
        <div class="card-content black-text">
            <div class="center-align">
                <p class="card-title">{{ trans('installer.welcome.serverpermissions') }}</p>
                <hr>
            </div>
            @foreach($permissions['permissions'] as $permission)
               <ul class="collection with-header">
                  <li class="collection-item">
                      <div class="row">
                          <div class="left">
                            {{ $permission['folder'] }}
                          </div>
                          <div class="right">
                              <p>{{ $permission['permission'] }}</p>
                              @if($permission['isActive'])
                                <i class="material-icons" style="color: #4F8A10">check_circle</i>
                              @else
                                <i class="material-icons" style="color: #D8000C">cancel</i>
                              @endif
                          </div>
                      </div>
                  </li>
               </ul>
            @endforeach
        </div>
        <div class="card-action">
            <div class="row">
               <div class="left">
                  <a class="btn waves-effect blue waves-light" href="{{ url('install/requirements') }}">
                      {{ trans('installer.welcome.back_button') }}
                      <i class="material-icons left">arrow_back</i>
                  </a>
                </div>
                @if ( ! isset($permissions['errors']))
                  <div class="right">
                    <a class="btn waves-effect blue waves-light" href="{{ url('install/verify-envato-purchase-code') }}">
                        {{ trans('installer.welcome.check_perchase_code') }}
                        <i class="material-icons right">send</i>
                    </a>
                  </div>
                @else
                  <div class="right">
                    <a class="btn waves-effect blue waves-light">
                        {{ trans('installer.welcome.check_perchase_code') }}
                        <i class="material-icons right">send</i>
                    </a>
                  </div>
                @endif
            </div>
        </div>
    </div>
@endsection