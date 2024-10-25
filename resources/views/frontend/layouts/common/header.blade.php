<!-- navbar -->
    <div class="navigation-wrap bg-white start-header start-style {{ request()->is('login') || request()->is('forget-password') || request()->is('password/resets/*') || request()->is('register') || request()->is('register/store-personal-info') || request()->is('2fa') || request()->is('google2fa') ? 'd-none' : '' }}">
		<div class="container-fluid px-240">
			<div class="row">
				<div class="col-12">
					<nav class="navbar navbar-expand-md navbar-light">
						<a class="navbar-brand" href="{{ request()->path() != 'merchant/payment' ? url('/') : 'javascript:void(0)' }}">
							<img src="{{ image(settings('logo'), 'logo') }}" alt="{{ __('Brand Logo') }}">
                        </a>	
						
						<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
							<svg class="mt-n4p" width="22" height="16" viewBox="0 0 22 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path class="path-white" fill-rule="evenodd" clip-rule="evenodd" d="M22 14.75C22 14.0597 21.4124 13.5 20.6875 13.5H12.8123C12.0874 13.5 11.4998 14.0597 11.4998 14.75C11.4998 15.4404 12.0874 16 12.8123 16H20.6875C21.4124 16 22 15.4404 22 14.75Z" fill="#403E5B"/>
								<path class="path-white" fill-rule="evenodd" clip-rule="evenodd" d="M22 8.00027C22 7.17183 21.403 6.50024 20.6666 6.50024H7.33307C6.59667 6.50024 5.99971 7.17183 5.99971 8.00027C5.99971 8.82871 6.59667 9.5003 7.33307 9.5003H20.6666C21.403 9.5003 22 8.82871 22 8.00027Z" fill="#403E5B"/>
								<path class="path-white" fill-rule="evenodd" clip-rule="evenodd" d="M22 1.25002C22 0.559654 21.3984 0 20.6562 0H1.84339C1.10124 0 0.499611 0.559654 0.499611 1.25002C0.499611 1.94039 1.10124 2.50005 1.84339 2.50005H20.6562C21.3984 2.50005 22 1.94039 22 1.25002Z" fill="#403E5B"/>
							</svg>
								
						</button>
						
						<div class="collapse navbar-collapse" id="navbarSupportedContent">
							<ul class="navbar-nav ms-auto py-4 py-md-0 gilroy-medium nav-align">
								<li class="nav-item {{ isset( $menu ) && ( $menu == 'home' ) ? 'nav-active': '' }}">
									<a class="nav-link" href="{{ url('/') }}">{{ __('Home') }}</a>
								</li>
                                
                                @if(!empty(getMenuContent('Header')))
                                    @foreach(getMenuContent('Header') as $top_navbar)
                                        <li class="nav-item {{ isset( $menu ) && ( $menu == $top_navbar->url ) ? 'nav-active': '' }}">
                                            <a href="{{ url($top_navbar->url) }}" class="nav-link"> {{ $top_navbar->name }}</a>
                                        </li>
                                    @endforeach
                                @endif
                                
                                <!-- Custom Addons Header Menu -->
                                @if (count(getCustomAddonNames()) > 0)
                                    @foreach (getCustomAddonNames() as $addon)
                                        @if (isActive($addon) && view()->exists(strtolower($addon) . '::frontend.header'))
                                            @include(strtolower($addon) . '::frontend.header')
                                        @endif
                                    @endforeach
                                @endif
                                <!-- Custom Addons Header Menu End -->

                                <li class="nav-item {{ isset( $menu ) && ( $menu == 'privacy-policy' ) ? 'nav-active': '' }}">
									<a class="nav-link" href="{{ route('privacy_policy') }}">{{ __('Privacy Policy') }}</a>
								</li>

								<li>
									<div class="color-parent mt-2p">
										<div class="switch">
											<div id="switch">
												<img src="{{ asset('public/frontend/templates/images/home/moon.png') }}" class="moon img-none" width="26px" alt="">
												<img src="{{ asset('public/frontend/templates/images/home/sun2.png') }}" class="img-none sun" width="26px" alt="">
											</div>
										</div>
									</div>
								</li>
                                
                                <div class="d-flex">
                                    @guest
                                        <a href="{{ url('login') }}" class="border d-flex align-items-center justify-content-center log-btn rounded ml-60 mt-n4p">
                                            {{ __('Login') }}
                                        </a>

                                        <a href="{{ url('register') }}" class="border d-flex align-items-center justify-content-center reg-btn rounded ml-18 mt-n4p">
                                            {{ __('Register') }}
                                        </a>
                                    @endguest
                                    @auth
                                        <a href="{{ url('dashboard') }}" class="border d-flex align-items-center justify-content-center log-btn rounded ml-60 mt-n4p">
                                            {{ __('Dashboard') }}
                                        </a>

                                        <a href="{{ url('logout') }}" class="border d-flex align-items-center justify-content-center reg-btn rounded ml-18 mt-n4p">
                                            {{ __('Logout') }}
                                        </a>
                                    @endauth
                                </div>
							</ul>
						</div>
						
					</nav>		
				</div>
			</div>
		</div>
	</div>