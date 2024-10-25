@extends('admin.layouts.master')
@section('title', __('Money Exchange'))
@section('page_content')

<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="col-md-12">
					{{ __('Money Exchange Information') }}
				</div>
			</div>
		</div>
		<div class="panel-body">
		<div class="row">
            <div class="col-md-3 col-xs-6">
	            <div class="form-group">
	              <label>{{ __('Date') }}</label>
	              <p class="form-control-static">{{ dateFormat($info->created_at) }}</p>
	            </div>
            </div>

  			 <div class="col-md-3 col-xs-6">
	            <div class="form-group">
	              <label>{{ __('Exchange Rate') }}</label>
	              <p class="form-control-static">{{ decimalFormat($info->exchange_rate) }} </p>
	            </div>
            </div>

            <div class="col-md-3 col-xs-6">
	            <div class="form-group">
	              <label>{{ __('From Wallet') }}</label>
	              <p class="form-control-static"> {{  moneyFormat($info->fromWallet?->currency?->symbol, decimalFormat($info->amount)) }}</p>
	            </div>
            </div>

			<div class="col-md-3 col-xs-6">
				<div class="form-group">
				  <label>{{ __('To Wallet') }} </label>
				  <p class="form-control-static">
				  	@if ($info->type == 'In')
				  		{{ moneyFormat($info->toWallet?->currency?->symbol, decimalFormat($info->amount / $info->exchange_rate)) }}
				  	@elseif($info->type == 'Out')
				  		{{ moneyFormat($info->toWallet?->currency?->symbol, decimalFormat($info->amount * $info->exchange_rate)) }}
				  	@endif
				   </p>
				</div>
			</div>
          </div>

            <div class="row">
	          <div class="col-md-3 col-xs-6">
	            <div class="form-group">
	              <label>{{ __('Currency') }}</label>
	              <p class="form-control-static">{{ $info->currency?->code }}</p>
	            </div>
	          </div>

	          <div class="col-md-3 col-xs-6">
	            <div class="form-group">
	              <label>{{ __('Comment') }}</label>
	              <p class="form-control-static">{{ __('Exchange operation') }}</p>
	            </div>
	          </div>
        	</div>
		</div>
	</div>
</div>

@endsection

@push('extra_body_scripts')

<script type="text/javascript">
</script>

@endpush