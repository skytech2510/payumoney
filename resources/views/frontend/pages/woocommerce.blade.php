@extends('frontend.layouts.app')
@section('content')
	<!-- Hero section -->
	<div class="standards-hero-section">
		<div class="px-240">
			<div class="d-flex flex-column align-items-start">
				<nav class="customize-bcrm">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('Developer') }}</li>
					</ol>
				</nav>
				<div class="btn-section">
					<button class="btn btn-dark btn-lg">{{ __('Developer') }}</button>
				</div>
				<div class="merchant-text">
					<p>{{ __('With :x Standard and Express, you can easily and safely receive online payments from your customer.', ['x' => settings('name')]) }}</p>
				</div>
			</div>
		</div>
	</div>

    <!-- Merchant tab -->
    @include('frontend.pages.merchant_tab')


  	<div class="px-240 code-snippet-section">
		<div class="snippet-module">
			<div class="">
				<div class="standard-title-text mb-28">
					<h3>{{ settings('name') }} {{ __('WooCommerce Plugin Installation') }}</h3>
				</div>
				<div class="details-box">
					<h4>{{ __('Download WooCommerce Plugin') }}</h4>
					<p>{{ __('To install WooCommerce Plugin in your admin panel, you need to first click on the download button to get the plugin file on your computer.') }}</p>
					<div class="download-btn mt-12">
						<a href="{{ url('public/uploads/woocommerce').'/'.$plugin_name }}" class="btn btn-sm btn-primary">{{ __('Download') }}</a>
					</div>
				</div>
				<div class="details-box">
					<h4>{{ __('Go to Plugins Menu in Admin Penal') }} </h4>
					<p>{{ __('After downloading the plugin (which will be a zip file), you will need to go to WordPress admin area and click Plugins') }} </p>
				</div>
				<div class="details-box">
					<h4>{{ __('Click Add New') }}</h4>
					<p>{{ __('To install WooCommerce Plugin in your admin panel, you need to first click on the download button to get the plugin file on your computer.') }}</p>
					<div>
						<img src="{{ asset('public/frontend/templates/images/woocommerce/ad-new.svg') }}"  class="img-fluid">
					</div>
				</div>
				<div class="details-box">
					<h4>{{ __('Upload Plugin') }}</h4>
					<p>{{ __('After that, you will an Upload Plugin button on top of the page. Click here that bring you to the plugin upload page. Here you need to click on the choose file button and select the plugin file you downloaded earlier to your computer.') }}</p>
					<div class="download-btn mt-12">
						<a href="{{ url('public/uploads/woocommerce').'/'.$plugin_name }}" class="btn btn-sm btn-primary">{{ __('Download') }}</a>
					</div>
				</div>
				<div class="details-box">
					<h4>{{ __('Install Plugin') }}</h4>
					<p>{{ __('After upending your WooCommerce plugin file, you need to click on the install now button.') }}</p>
					<div>
						<img src="{{ asset('public/frontend/templates/images/woocommerce/up-plugin.svg') }}" class="img-fluid">
					</div>
				</div>
			</div>
		</div>
 	</div> 
@endsection
