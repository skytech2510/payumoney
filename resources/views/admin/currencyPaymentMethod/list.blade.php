@php
	$extensions = json_encode(getFileExtensions(7));
@endphp
@extends('admin.layouts.master')
@section('title', __('Currency Payment Methods'))

@section('head_style')
	<!-- sweetalert -->
	<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.css')}}">
	<link rel="stylesheet" type="text/css" href="{{ asset('public/admin/customs/css/currency_payment_methods/list.min.css')}}">
	<link rel="stylesheet" type="text/css" href="{{ asset('public/admin/customs/css/custom-checkbox.min.css')}}">
@endsection

@section('page_content')
	<div class="box box-default">
		<div class="box-body ps-2">
			<div class="row">
				<div class="col-md-12">
					<div class="top-bar-title padding-bottom">{{ __('Currency Payment Methods') }}</div>
				</div>
			</div>
		</div>
	</div>

	<div class="box">
		<div class="box-body ps-3">
			<div class="dropdown">
				<button class="btn btn-default dropdown-toggle f-14" type="button" data-bs-toggle="dropdown">{{ __('Currency') }} : <span class="currencyName">{{ $currency->name }}</span>
				<span class="caret"></span></button>
				<ul class="dropdown-menu xss f-14 p-0">
					@foreach($currencyList as $currencyItem)
						<li class="listItem px-2 py-1" data-rel="{{ $currencyItem->id }}">
							<a class="px-2 py-1 d-block" href="#">{{ $currencyItem->name }}</a>
						</li>
					@endforeach
				</ul>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-3">
			@include('admin.common.paymentMethod_menu')
		</div>

		<div class="col-md-9">
			<div class="box box-info">
				<div class="box-header with-border text-center">
					<h3 class="box-title">
						@if ($paymentMethod ==  Bank)
							{{ $paymentMethodName }} {{ __('Details') }}
						@elseif (config('mobilemoney.is_active') && $paymentMethod == (defined('MobileMoney') ? MobileMoney : ''))
							{{ $paymentMethodName }} {{ __('Details') }}
						@elseif($paymentMethod == Mts)
							{{ __('Wallet') }}
						@else
							{{ $paymentMethodName }} {{ __('Credentials') }}
						@endif
					</h3>
				</div>

				<form action='{{url(config('adminPrefix').'/settings/payment-methods/update-paymentMethod-Credentials')}}' class="form-horizontal" method="POST" id="currencyPaymentMethod_form">
					{!! csrf_field() !!}

					<input type="hidden" value="{{ isset($currencyPaymentMethod->id) ? $currencyPaymentMethod->id : '' }}" name="id" id="id">
					<input type="hidden" value="{{ $currency->id }}" name="currency_id" id="currency_id">
					<input type="hidden" value="{{ $paymentMethod }}" name="paymentMethod" id="paymentMethod">
					<input type="hidden" value="{{ $list_menu }}" name="tabText" id="tabText">

					<div class="box-body">

						@php
							$paymentMethodBlade = $list_menu == 'mts' ? 'admin.currencyPaymentMethod.wallet' : 'admin.currencyPaymentMethod.'.strtolower($list_menu);
						@endphp
						@if ($list_menu != 'bank')
							@include($paymentMethodBlade)
							@include('admin.currencyPaymentMethod.common')
						@endif
					</div>
				</form>

				@if ($list_menu == 'bank')
					@include('admin.currencyPaymentMethod.bank')
				@endif



				@if (config('mobilemoney.is_active') && $list_menu == 'mobilemoney')
					@include('admin.common.mobile-money')
				@endif
			</div>
		</div>
	</div>
@endsection

@push('extra_body_scripts')
	<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.js')}}" type="text/javascript"></script>
	<script>
		'use strict';
		var bankLogo = "{{ image(null, 'bank') }}";
		var isActiveMobileMoney = "{!! config('mobilemoney.is_active') !!}";
		var modeRequire = "{{ __('Please select a mode.') }}";
		var updateText = "{{ __('Updating...') }}";
		var titleText = "{{ __('Success') }}";
		var failedText = "{{ __('Failed') }}";
		var errorTitle = "{{ __('Oops...') }}";
		var errorText = "{{ __('Something went wrong with ajax.') }}";
		var deleteTitle = "{{ __('Deleted') }}";
		var deleteAlert = "{{ __('Are you sure you want to delete?') }}";
		var alertText = "{{ __('You won\'t be able to revert this.') }}";
		var confirmBtnText = "{{ __('Yes, delete it.') }}";
		var cancelBtnText = "{{ __('Cancel') }}";
		var cancelTitle = "{{ __('canceled') }}";
		var cancelAlert = "{{ __('You have cancelled it') }}";
		var copyTitle = "{{ __('Copied') }}";
		var copyText = "{{ __('IPN URL link copied.') }}";
		var noResponseText = "{{ __('No response.') }}";
		var yesText = "{{ __('Yes') }}";
		var noText = "{{ __('No') }}";
		var activeText = "{{ __('Active') }}";
		var inactiveText = "{{ __('Inactive') }}";
		var submitText = "{{ __('Submitting...') }}";
		var extensions = JSON.parse(@json($extensions));
		var extensionsValidationRule = extensions.join('|');
		var extensionsValidation = extensions.join(', ');
		var errorMessage = '{{ __("Please select (:x) file.") }}';
		var extensionsValidationMessage = errorMessage.replace(':x', extensionsValidation);
	</script>
	<script src="{{ asset('public/admin/customs/js/currency_payment_methods/list.min.js')}}" type="text/javascript"></script>
	@if (config('mobilemoney.is_active') && $list_menu == 'mobilemoney')
		<script>
			'use strict';
			var defaultImagePath = "{!! asset('public/uploads/userPic/default-image.png') !!}";
		</script>
		<script src="{{ asset('public/dist/js/mobile-money.min.js') }}"></script>
	@endif
@endpush
