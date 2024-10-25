@if(session('message') || session('success') || session('error') || !empty($error))
<div class="d-flex justify-content-end alert-px">
	<div class="alert alert-dismissible fade show alert-animate bg-white border-1p" role="alert">
		<div class="alert-body">
			<div class="d-flex gap-20">
				<div class="alert-icon">
					@if(session('success') || session('alert-class') == 'alert-success')
						<img src="{{ image(null, 'success') }}" >
					@elseif(session('error') || session('alert-class') == 'alert-danger' || !empty($error))
						<img src="{{ image(null, 'error') }}" >
					@else
						<img src="{{ image(null, 'warning') }}" >
					@endif
				</div>
				<div class="alert-text">
					<p class="mb-0 f-16 leading-22 text-dark gilroy-Semibold">
						{{ session('success') || session('alert-class') == 'alert-success' ? __('Success!') : ( session('error') || session('alert-class') == 'alert-danger' || !empty($error) ? __('Error!') : __('Warning!') ) }}
					</p>
					<p class="mb-0 f-13 leading-18 text-gray-100 gilroy-medium mt-1">
						{{ 
							session()->has('message') 
								? session('message') 
								: (session()->has('success') 
									? session('success') 
									: (session()->has('error') 
										? session('error') 
										: $error
									) 
								) 
						}}
					</p>
				</div>
			</div>
		</div>
		<button type="button" class="btn-close alert-btn-close" data-bs-dismiss="alert" aria-label="Close">
		{!! svgIcons('cross_icon') !!}
		</button>
	</div> 
</div>
@endif

@if ($errors->any())
	@foreach ($errors->all() as $error)
		<div class="d-flex justify-content-end alert-px">
			<div class="alert alert-dismissible fade show alert-animate bg-white border-1p" role="alert">
				<div class="alert-body">
					<div class="d-flex gap-20">
						<div class="alert-icon">
							<img src="{{ image(null, 'error') }}" >
						</div>
						<div class="alert-text">
							<p class="mb-0 f-16 leading-22 text-dark gilroy-Semibold">
								{{ __('Error!') }}
							</p>
							<p class="mb-0 f-13 leading-18 text-gray-100 gilroy-medium mt-1">
								{{ $error }}
							</p>
						</div>
					</div>
				</div>
				<button type="button" class="btn-close alert-btn-close" data-bs-dismiss="alert" aria-label="Close">
				{!! svgIcons('cross_icon') !!}
				</button>
			</div> 
		</div>
	@endforeach
@endif