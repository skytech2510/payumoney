@extends('vendor.installer.layout')

@section('style')
    <style>
        .card-panel { display: none; }
    </style>
@endsection

@section('content')
    <div class="card">
         <form method="post" action="{{ url('install/database') }}">    
            <div class="card-content black-text">
                <p class="card-title center-align">{{ __('Database setting') }}</p>
                <p class="center-align">{{ __('If you dont know how to fill this form contact your hoster') }}</p>
                <hr>
                {{ csrf_field() }}
                <div class="input-field">
                    <i class="material-icons prefix">settings</i>
                    <input type="text" id="dbname" name="dbname" value="{{ $database }}" required>
                    <label for="dbname">{{ __('Database name (where you want your application to be)') }}</label>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">perm_identity</i>
                    <input type="text" id="username" name="username" value="{{ $username }}" required>
                    <label for="username">{{ __('Username (Your database login)') }}</label>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">vpn_key</i>
                    <input type="text" id="password" name="password" value="{{ $password }}">
                    <label for="password">{{ __('Password (Your database password)') }}</label>
                </div>
                <div class="input-field ">
                    <i class="material-icons prefix black-text">select_all</i>
                    <input type="text" id="port" name="port" value="{{ $port }}" required>
                    <label for="port">{{ __('Port (For MySQL use port 3306 and for MariaDB 3307)') }}</label>
                </div>
                <div class="input-field "><i class="material-icons prefix black-text">language</i>
                    <input type="text" id="host" name="host" value="{{ $host }}" required>
                    <label for="host">{{ __('Host name (should be localhost, if it does not work ask your hoster)') }}</label>
				</div>
            </div>
            <div class="card-action">
                <div class="row">
                     <div class="left">
                        <a class="btn waves-effect blue waves-light" href="{{ url('install/verify-envato-purchase-code') }}">
                            {{ __('Back') }}
                            <i class="material-icons left">arrow_back</i>
                        </a>
                      </div>
                      <div class="right">
                        <button type="submit" class="btn waves-effect blue waves-light">
                            {{ __('Create database') }}
                            <i class="material-icons right">send</i>
                        </button>
                      </div>
                  </div>
            </div>
        </form>             
    </div>  
    <div class="card-panel teal red">
        <div class="card-content white-text">
            {{ __('Please wait a moment...') }}
            <br>
            <div class="progress">
                <div class="indeterminate red"></div>
            </div>
        </div>
    </div>  
@endsection

@section('script')
    <script>
        $(function(){
            $(document).on('submit', 'form', function(e) {  
                $('.card').hide();
                $('.card-panel').show();
            });
        })      
    </script>
@endsection