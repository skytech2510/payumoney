@if (session('message') || session('status') || session('warning') || session('success') || session('error'))
<div class="d-flex justify-content-end alert-px">
	<div class="alert alert-error alert-dismissible fade show alert-animate top-right bg-white border-1p" role="alert">
		<div class="alert-body">
			<div class="d-flex gap-20">
				<div class="alert-icon">
					@if(session('success') || session('alert-class') == 'alert-success')
						<img src="{{ image(null, 'success') }}" alt="{{ __('Warning') }}">
					@elseif(session('error') || session('alert-class') == 'alert-danger' || !empty($error))
						<img src="{{ image(null, 'error') }}" alt="{{ __('Warning') }}">
					@else
						<img src="{{ image(null, 'warning') }}" alt="{{ __('Warning') }}">
					@endif
				</div>
				<div class="alert-text">
					<p class="alert-title-text">
						{{ session('success') || session('alert-class') == 'alert-success' ? __('Success!') : ( session('error') || session('alert-class') == 'alert-danger' || !empty($error) ? __('Error!') : __('Warning!') ) }}
					</p>
					<p class="alert-body-text">
						{{ 
							session()->has('message') 
								? session('message') 
								: (session()->has('success') 
									? session('success') 
									: (session()->has('error') 
										? session('error') 
										: session('status')
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
  
