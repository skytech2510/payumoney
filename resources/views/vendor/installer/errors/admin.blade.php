@extends('vendor.installer.layout')

@section('content')
  <div class="card">
      <div class="card-content black-text">
              <div class="center-align">
                  <p class="card-title">{{ __('OOPS') }}</p>
                  <hr>
              </div>
              <div class="center-align">
                  {{ __('Please verify your purchase code and username.') }}
              </div>
      </div>
      <div class="card-action right-align">
          <a class="btn waves-effect blue waves-light red white-text" href="{{ url('install/verify-envato-purchase-code?old=true') }}">
              {{ __('Verify Purchase Code') }}
              <i class="material-icons right">send</i>
          </a>
      </div>
  </div>
@endsection
