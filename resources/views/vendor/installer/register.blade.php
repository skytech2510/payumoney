@extends('vendor.installer.layout')

@section('style')
    <style>
        input { margin-bottom: 2px !important };
    </style>
@endsection

@section('content')
    <div class="card black-text">
         <form method="post" action="{{ url('install/register') }}">    
            <div class="card-content">
                <p class="card-title center-align">{{ __('Administrator creation') }}</p>
                <p class="center-align">{{ __('Now you must enter information to create administrator') }}</p>
                <hr>
                {{ csrf_field() }}
                <div class="input-field">
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}">
                    <label for="first_name">
                        {{ __('Your First Name') }}
                    </label>
                    @if ($errors->has('first_name'))
                        <small class="red-text text-lighten-2">{{ $errors->first('first_name') }}</small>
                    @endif
                </div>
                <div class="input-field">
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}">
                    <label for="last_name">
                        {{ __('Your Last Name') }}
                    </label>
                    @if ($errors->has('last_name'))
                        <small class="red-text text-lighten-2">{{ $errors->first('last_name') }}</small>
                    @endif
                </div>
                <div class="input-field">
                    <input type="text" id="email" name="email" value="{{ old('email') }}">
                    <label for="email">
                        {{ __('Your Email') }}
                    </label>
                    @if ($errors->has('email'))
                        <small class="red-text text-lighten-2">{{ $errors->first('email') }}</small>
                    @endif
                </div>
                <div class="input-field">
                    <input type="text" id="password" name="password" value="{{ old('password') }}">
                    <label for="password">
                        {{ __('Your Password') }}
                    </label>
                    @if ($errors->has('password'))
                        <small class="red-text text-lighten-2">{{ $errors->first('password') }}</small>
                    @endif
                </div>
                
            </div>
            <div class="card-action">
                <div class="row">
                     <p><em>{{ __('You will need your password to login, so keep it safe !') }}</em></p>
                      <div class="right">
                        <button type="submit" class="btn waves-effect blue waves-light">
                            {{ __('Create user') }}
                            <i class="material-icons right">send</i>
                        </button>
                      </div>
                  </div>
            </div>
        </form>
    </div>
@endsection