@extends('user.layouts.app')

@section('content')

<section class="min-vh-100">
	<div class="container mt-5">
			<div class="row">
				<div class="col-md-12 col-xs-12">
					<div>
						<div class="card-body">
							<div class="text-center">
								<div class="text-center">
									<div class="h3 mt10 text-danger">
										<div class="">
											<div class="alert alert-danger">
												<i class="fa fa-flag"></i> <strong>{{ __('Inactive!') }}</strong>
												<hr class="message-inner-separator">
												<p>{{ $message }}</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
	</div>
</section>

@endsection
