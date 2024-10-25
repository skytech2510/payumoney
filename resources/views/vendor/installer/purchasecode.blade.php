
@extends('vendor.installer.layout')

@section('content')
<div class="card">
    <div class="card-content black-text">
		<div class="center-align">
			<p class="card-title">{{ __('Verify Envato Purchase Code') }} | <a href="https://cutt.ly/PLFZenO" target="_blank">NULLED :: Web Community</a></p>
		</div>
		@if(isset($responseError))
			<div class="center-align red-text">
				{{ $responseError }}
			</div>
		@endif
        <form class="form-horizontal" action="{{ url('install/verify-envato-purchase-code?old=' . $old) }}" method="post">
            {{ csrf_field() }}

			<!-- Envato Username -->
			<div class="form-group">
				<div class="col-md-8 input-field offset-2">
					<label for="envatoUsername">{{ __('Envato Username') }}</label>
					<input type="text" class="form-control" id="envatoUsername" name="envatoUsername" value="{{ old('envatoUsername') }}" required>
					@if(isset($errors))
						<span class="text-danger" style="color: red">{{ $errors->first('envatoUsername') }}</span>
					@endif
				</div>
			</div>

			<!-- Purchase Code -->
			<div class="form-group">
				<div class="col-md-8 input-field offset-2">
					<label for="envatopurchasecode">{{ __('Envato Purchase code') }}</label>
					<input type="text" class="form-control" id="envatopurchasecode" name="envatopurchasecode" required>
					@if(isset($errors))
						<span class="text-danger" style="color: red">{{$errors->first('envatopurchasecode')}}</span>
					@endif
				</div>
			</div>
			<br><br>
			<div class="">
				<div class="row">
					<div class="left">
						<a class="btn waves-effect blue waves-light" href="{{ url('install/permissions') }}">
						{{ __('Back') }}
						<i class="material-icons left">arrow_back</i></a>
					</div>
					<div class="right">
						<button type="submit" class="btn waves-effect blue waves-light">
							{{ __('Verify Purchase Code') }}
							<i class="material-icons right">send</i>
						</button>
					</div>
				</div>
			</div>
        </form>
    </div>
</div>
@endsection
