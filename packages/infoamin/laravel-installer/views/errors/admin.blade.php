@extends('vendor.installer.layout')

@section('content')
  <div class="card">
      <div class="card-content black-text">
              <div class="center-align">
                  <p class="card-title">{{trans('installer.error.oops')}}</p>
                  <hr>
              </div>
              <div class="center-align">
                  {{trans('installer.error.code')}}
              </div>
      </div>
      <div class="card-action right-align">
          <a class="btn waves-effect blue waves-light red white-text" href="{{ url('install/verify-envato-purchase-code?old=true') }}">
              {{ trans('installer.welcome.verify_button') }}
              <i class="material-icons right">send</i>
          </a>
      </div>
  </div>
@endsection
