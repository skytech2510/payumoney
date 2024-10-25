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
					<h3>{{ settings('name') }} {{ __('PrestaShop Plugin Installation') }}</h3>
				</div>
				<div class="details-box">
					<h4>{{ __('Download PrestaShop Plugin') }}</h4>
					<p>{{ __('To install PrestaShop Plugin in your admin panel, you need to first click on the download button to get the plugin file on your computer.') }}</p>
					<div class="download-btn mt-12">
						<a href="{{ url('public/uploads/prestashop', $plugin_name) }}" class="btn btn-sm btn-primary">{{ __('Download') }}</a>
					</div>
				</div>
				<div class="details-box">
					<h4>{{ __('Go to Module Menu in Admin Penal') }} </h4>
					<p>{{ __('After downloading the plugin (which will be a zip file), you will need to go to PrestaShop admin area and click Module Manager') }} </p>
                    <div>
						<img src="{{ asset('Modules/PrestaShop/Resources/assets/images/add-new.png') }}"  class="img-fluid">
					</div>
				</div>
				<div class="details-box">
					<h4>{{ __('Click Upload a Module') }}</h4>
					<p>{{ __('On top of the Module manager page you will get Upload a Module button, after clicking the button plugin upload option appers.') }}</p>
					<div>
						<img src="{{ asset('Modules/PrestaShop/Resources/assets/images/upload.png') }}"  class="img-fluid">
					</div>
				</div>

				<div class="details-box">
					<h4>{{ __('Install Plugin') }}</h4>
					<p>{{ __('After uploading prestashop plugin file, plugin will automaticly install on your prestashop site. You need to click on the configure button.') }}</p>
					<div>
                        <img src="{{ asset('Modules/PrestaShop/Resources/assets/images/upload_complete.png') }}"  class="img-fluid">
					</div>
				</div>

                <div class="details-box">
					<h4>{{ __('Configure') }}</h4>
					<p>{{ __('You have to provide your merchant Client Id & Client Secret on configuration') }}</p>
					<div>
                        <img src="{{ asset('Modules/PrestaShop/Resources/assets/images/configuration.png') }}"  class="img-fluid">
					</div>
				</div>

                <div class="details-box">
					<h4>{{ __('Plugin List') }}</h4>
					<p>{{ __('If the plugin is successfully installed then it will be shown in Module Manager >> Payment section. You can configure your plugin credentials by clicking configure button') }}</p>
					<div>
                        <img src="{{ asset('Modules/PrestaShop/Resources/assets/images/configuration_list.png') }}"  class="img-fluid">
					</div>
				</div>


			</div>
		</div>
 	</div>
@endsection
