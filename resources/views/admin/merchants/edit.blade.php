@extends('admin.layouts.master')
@section('title', __('Edit Merchant'))

@section('head_style')
	<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.css')}}">
@endsection

@section('page_content')
	<div class="box">
		<div class="panel-body ml-20">
			<ul class="nav nav-tabs f-14 cus" role="tablist">
				<li class="nav-item">
				    <a class="nav-link active" href='{{ url(config('adminPrefix')."/merchant/edit", $merchant->id)}}'>{{ __('Profile') }}</a>
				</li>

				<li class="nav-item">
				    <a class="nav-link" href="{{ url(config('adminPrefix')."/merchant/payments", $merchant->id)}}">{{ __('Payments') }}</a>
				</li>
			</ul>
			<div class="clearfix"></div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-10">
			<p class="ml-5 panel-title text-bold pull-left fw-bold mb-0">{{ $merchant->business_name }}</p>
		</div>
		<div class="col-md-2">
			@if ($merchant->status)
				<p class="panel-title pull-right fw-bold mb-0">@if ($merchant->status == 'Approved')<span class="text-green">{{ __('Approved') }}</span>@endif
				@if ($merchant->status == 'Moderation')<span class="text-blue">{{ __('Moderation') }}</span>@endif
				@if ($merchant->status == 'Disapproved')<span class="text-red">{{ __('Disapproved') }}</span>@endif</p>
			@endif
		</div>
	</div>

	<div class="box mt-20">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<form action="{{ url(config('adminPrefix').'/merchant/update') }}" class="form-horizontal" id="merchant_edit_form" method="POST" enctype="multipart/form-data">
							{{ csrf_field() }}
							<input type="hidden" value="{{ $merchant->id }}" name="id" id="id">

							<div class="col-md-12 mt-1">
								
								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14">{{ __('User') }}</label>
									<div class="col-sm-6">
										<p class="form-control-static mb-0 f-14">{{ getColumnValue($merchant->user) }}</p>
									</div>
								</div>

								
								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14">{{ __('Merchant ID') }}</label>
									<div class="col-sm-6">
										<p class="form-control-static mb-0 f-14">{{ $merchant->merchant_uuid }}</p>
									</div>
								</div>

								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="type">{{ __('Type') }}</label>
									<div class="col-sm-6">
										<select class="select2" name="type" id="type">
											<option value="standard" {{ $merchant->type ==  'standard'? 'selected':"" }}>{{ __('Standard') }}</option>
											<option value="express"  {{ $merchant->type == 'express' ? 'selected':"" }}>{{ __('Express') }}</option>
										</select>
									</div>
								</div>

								
								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="business_name">{{ __('Business Name') }}</label>
									<div class="col-sm-6">
										<input type="text" class="form-control f-14" name="business_name" value="{{ $merchant->business_name }}" id="business_name">

										@if($errors->has('business_name'))
											<span class="error">
												<strong class="text-danger">{{ $errors->first('business_name') }}</strong>
											</span>
										@endif
									</div>
								</div>

				
								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="site_url">{{ __('Site Url') }}</label>
									<div class="col-sm-6">
										<input type="text" class="form-control f-14" name="site_url" value="{{ $merchant->site_url }}" id="site_url">

										@if($errors->has('site_url'))
											<span class="error">
												<strong class="text-danger">{{ $errors->first('site_url') }}</strong>
											</span>
										@endif
									</div>
								</div>

								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="currency_id">{{ __('Currency') }}</label>
									<div class="col-sm-6">
										<select class="form-control f-14 select2" name="currency_id" id="currency_id">
											@foreach($activeCurrencies as $result)
													<option data-type="{{ $result->type }}" value="{{ $result->id }}" {{ $merchant->currency_id == $result->id ? 'selected="selected"' : '' }}>{{ $result->code }}</option>
											@endforeach
										</select>

										@if($errors->has('currency_id'))
											<span class="error">
												<strong class="text-danger">{{ $errors->first('currency_id') }}</strong>
											</span>
										@endif
									</div>
								</div>

								<div class="form-group row">
									<label class="col-sm-3 control-label text-sm-end fw-bold f-14" for="merchantGroup">{{ __('Group') }}</label>
									<div class="col-sm-6">
										<select class="select2" name="merchantGroup" id="merchantGroup">
											@foreach ($merchantGroup as $group)
												<option value='{{ $group->id }}' {{ isset($group) && $group->id == $merchant->merchant_group_id ? 'selected':""}}> {{ $group->name }}</option>
											@endforeach
										</select>
									</div>
								</div>
								
								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="fee">{{ __('Fee') }} (%)</label>
									<div class="col-sm-6">
										<input type="text" class="form-control f-14" name="fee" onkeypress="return isNumberOrDecimalPointKey(this, event);" 
										value="{{ formatNumber((float)$merchant->fee, $merchant->currency_id) }}" id="fee"
										oninput="restrictNumberToPrefdecimalOnInput(this)">
										@if($errors->has('fee'))
											<span class="error">
												<strong class="text-danger">{{ $errors->first('fee') }}</strong>
											</span>
										@endif
										<div class="clearfix"></div>
									</div>
								</div>


								<div class="form-group row">
									<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="logo">{{ __('Logo') }}</label>
									<div class="col-sm-6">
										<input type="file" name="logo" class="form-control f-14 input-file-field" data-rel="{{ !empty($merchant->logo) ? $merchant->logo : '' }}" id="logo"
										value="{{ !empty($merchant->logo) ? $merchant->logo : '' }}">
										@if($errors->has('logo'))
											<span class="error">
												<strong class="text-danger">{{ $errors->first('logo') }}</strong>
											</span>
										@endif
										<div class="clearfix"></div>
										<small class="form-text f-12 text-muted"><strong>{{ allowedImageDimension(100,80) }}</strong></small>
										<div class="setting-img">
											<img src='{{ image($merchant->logo, 'merchant') }}' width="100" height="80" id="{{ isset($merchant->logo) ? 'merchant-logo-preview' : 'merchant-demo-logo-preview'}}">
											@if(isset($merchant->logo))
											<span class="remove_merchant_preview"></span>
											@endif
										</div>
									</div>
								</div>

								@if ($merchant->status)
									<div class="form-group row">
										<label class="control-label col-sm-3 mt-11 text-sm-end fw-bold f-14" for="status">{{ __('Change Status') }}</label>
										<div class="col-sm-6">
											<select class="select2" name="status" id="status">
												<option value="Approved" {{ isset($merchant->status) && $merchant->status ==  'Approved'? 'selected':"" }}>{{ __('Approved') }}</option>
												<option value="Moderation"  {{ isset($merchant->status) && $merchant->status == 'Moderation' ? 'selected':"" }}>{{ __('Moderation') }}</option>
												<option value="Disapproved"  {{ isset($merchant->status) && $merchant->status == 'Disapproved' ? 'selected':"" }}>{{ __('Disapproved') }}</option>
											</select>
										</div>
									</div>
								@endif
							</div>

							<div class="col-md-12">
                                <div class="form-group row align-items-center">
                                    <div class="col-sm-3">

                                    </div>
                                    <div class="col-sm-6 d-flex">
                                        <div>
                                            <a id="cancel_anchor" class="btn btn-theme-danger f-14 me-2" href="{{ url(config('adminPrefix').'/merchants') }}">{{ __('Cancel') }}</a>
                                        </div>
                                        <button type="submit" class="btn btn-theme form-control-static mb-0 f-14" id="merchant_edit">
                                            <i class="fa fa-spinner fa-spin d-none"></i> <span class="f-14" id="merchant_edit_text">{{ __('Update') }}</span>
                                        </button>
                                    </div>
                                </div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<!-- sweetalert -->
<script src="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.js')}}" type="text/javascript"></script>

@include('common.restrict_number_to_pref_decimal')
@include('common.restrict_character_decimal_point')


<!-- read-file-on-change -->
@include('common.read-file-on-change')

<script type="text/javascript">

	function getMerchantGroupFee(merchant_group_id)
    {
		let currentMerchantGroupId = '{{ $merchant->merchant_group_id }}';
		if (currentMerchantGroupId != merchant_group_id)
		{
			$.ajax({
				headers:
				{
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				method: "POST",
				url: SITE_URL+"/"+ADMIN_PREFIX+"/merchants/change-fee-with-group-change",
				dataType: "json",
				data: {
					'merchant_group_id':merchant_group_id,
				}
			})
			.done(function(response)
			{
				if(response.status == true)
				{
					$('#fee').val(formatNumberToPrefDecimal(response.fee));
				}
			});
		}
		else
		{
			let merchantFee = '{{ $merchant->fee }}';
			$('#fee').val(formatNumberToPrefDecimal(merchantFee));
		}
    }

	function restrictNumberToPrefdecimalOnInput(e)
    {
        var type = $('select#currency_id').find(':selected').data('type')
        restrictNumberToPrefdecimal(e, type);
    }

    function determineDecimalPoint() {

        var currencyType = $('select#currency_id').find(':selected').data('type')

        if (currencyType == 'crypto') {
            $("#fee").attr('placeholder', CRYPTODP);

        } else if (currencyType == 'fiat') {
            $("#fee").attr('placeholder', FIATDP);
        }
    }

	function formatNumberToPrefDecimal(num = 0)
	{
		let type = $('#currency_id').find(':selected').data('type');
		let decimal_format = (type == 'fiat') ? "<?php echo preference('decimal_format_amount', 2); ?>" : "<?php echo preference('decimal_format_amount_crypto', 8); ?>";

		num = ((Math.abs(num)).toFixed(decimal_format))
		return num;
	}

	$(window).on('load',function(){
		$(".select2").select2({});
        let merchant_group_id = $('#merchantGroup option:selected').val();
        getMerchantGroupFee(merchant_group_id);
		determineDecimalPoint();
	});

	$(document).on('change','#merchantGroup',function(e)
    {
        e.preventDefault();
        let merchant_group_id = $('#merchantGroup option:selected').val();
        getMerchantGroupFee(merchant_group_id);
    });

	// preview logo on change
    $(document).on('change','#logo', function()
    {
		let orginalSource = '{{ url('public/uploads/userPic/default-image.png') }}';
		let logo = $('#logo').attr('data-rel');
		if (logo != '') {
			readFileOnChange(this, $('#merchant-logo-preview'), orginalSource);
			$('.remove_merchant_preview').remove();
		}
		readFileOnChange(this, $('#merchant-demo-logo-preview'), orginalSource);
    });

	$(document).ready(function()
    {
		$('.remove_merchant_preview').click(function()
		{
		var logo = $('#logo').attr('data-rel');
		var merchant_id = $('#id').val();
		if(logo)
		{
			$.ajax(
			{
			headers:
			{
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type : "POST",
			url : SITE_URL+"/"+ADMIN_PREFIX+"/merchant/delete-merchant-logo",
			// async : false,
			data: {
				'logo' : logo,
				'merchant_id' : merchant_id,
			},
			dataType : 'json',
			success: function(reply)
			{
				if (reply.success == 1)
				{
					swal({title: "Deleted!", text: reply.message, type: "success"},
						function(){
							location.reload();
						}
					);
				}
				else
				{
					alert(reply.message);
					location.reload();
				}
			}
			});
		}
		});
    });

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
    });

    $('#merchant_edit_form').validate({
        rules: {
            business_name: {
                required: true,
            },
            site_url: {
                required: true,
                url: true,
            },
            type: {
                required: true,
                lettersonly: true,
            },
            fee: {
                required: true,
                number: true,
            },
            logo: {
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
			logo: {
			extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
			},
			type: {
			lettersonly: "Please enter letters only!"
			}
        },
        submitHandler: function(form)
		{
			$("#merchant_edit").attr("disabled", true);
			$(".fa-spin").removeClass("d-none");
			$("#merchant_edit_text").text('Updating...');
			$('#cancel_anchor').attr("disabled",true);
			form.submit();
		}
    });

</script>

@endpush
