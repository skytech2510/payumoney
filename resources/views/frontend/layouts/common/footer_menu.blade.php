<!-- Footer section -->
	<div class="footer-sec {{ request()->is('login') || request()->is('forget-password') || request()->is('register') || request()->is('register/store-personal-info') || request()->is('2fa') || request()->is('google2fa') ? 'd-none' : '' }}">
		<div class="px-240">
			<div class="row">
				<div class="col-md-5 pb-54 align-center">
					<a class="d-flex" href="{{ request()->path() != 'merchant/payment' ? url('/') : 'javascript:void(0)' }}">
						<img class="mt-54 footer-logo" src="{{ image(settings('logo'), 'logo') }}" alt="{{ __('Brand Logo') }}">
					</a>

					<p class="pb-0 mt-26 w-358 txt-center text-white gilroy-light leading-29">{{ __(':x, a secured online payment gateway that allows payment in multiple currencies easily, safely and securely.', ['x' => settings('name')]) }}</p>
					<p class="mb-0 mt-20 mt-r24 gilroy-Semibold f-20 text-white txt-center">
						{{ __('Download Our App') }}
					</p>
					<div class="mt-21 d-flex direction">
						@foreach(getAppStoreLinkFrontEnd() as $app)
							@if (!empty($app->logo))
								<a href="{{ $app->link }}" target="_blank">
									<img class="{{ $app->company == 'Apple' ?  'ms-3 ml-r12' : '' }}app-imgs" src="{{ url('public/uploads/app-store-logos/thumb/'.$app->logo) }}" alt="{{ __('playstore') }}">
								</a>
							@else
								<a href="javascript:void(0)">
									<img class="app-imgs" src="{{ url('public/uploads/app-store-logos/default-logo.jpg') }}">
								</a>
							@endif
						@endforeach


					</div>
				</div>
				<div class="col-md-3 align-center">
					<p class="pb-0 mt-58 gilroy-Semibold text-white f-20 txt-center quick-res">
						{{ __('Quick Links') }}
					</p>

					<div class="mt-18">
						<ul class="links gilroy-light">
							<li><a href="{{ url('/') }}" class="poppins5">{{ __('Home') }}</a></li>
							@if(!empty(getMenuContent('Footer')))
								@foreach(getMenuContent('Footer') as $footer_navbar)
									<li>
										<a href="{{ url($footer_navbar->url) }}" class="poppins5">{{ $footer_navbar->name }}</a>
									</li>
								@endforeach
							@endif

							<li><a href="{{ url('/developer') }}" class="poppins5">{{ __('Developer') }}</a></li>
						</ul>
					</div>
				</div>
				<div class="col-md-4 align-center">
					<div class="custom-postion">
						<p class="mb-0 mt-58 gilroy-Semibold f-20 text-white txt-center socials-res">
							{{ __('Social Links') }}
						</p>

						<div class="d-flex col-gap-12 mt-21">
							 @foreach(getSocialLink() as $social)
                                @if (!empty($social->url))
                                    <a href="{{ $social->url }}">{!! $social->icon !!}</a>
                                @endif
                            @endforeach
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="{{ request()->is('login') || request()->is('forget-password') || request()->is('register') || request()->is('register/store-personal-info') || request()->is('2fa') || request()->is('google2fa') ? 'd-none' : '' }} bottom-footer">
		<div class="px-240 d-flex justify-content-between btn-footer align-center">
			<div class="mt-18">
				<p class="mb-0 OpenSans-600 text-white">
					{{ __('Copyright') }}&nbsp;© {{date('Y')}} &nbsp;&nbsp; {{ settings('name') }} | {{ __('All Rights Reserved') }}
				</p>
			</div>
			<div>
				<div class="d-flex align-center justify-center-res sp mt-18">
					<span class="text-white OpenSans-600 lan">{{ __('Language') }} : </span>
					<div class="form-group OpenSans-600 selectParent footer-font-16 OpenSans-600">
						<select class="select2 form-control footer-font-16 mb-2n" data-minimum-results-for-search="Infinity" id="lang">
							@foreach (getLanguagesListAtFooterFrontEnd() as $lang)
								<option class="footer-font-16" {{ \Session::get('dflt_lang') == $lang->short_name ? 'selected' : '' }} value='{{ $lang->short_name }}'>{{ $lang->name }}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
