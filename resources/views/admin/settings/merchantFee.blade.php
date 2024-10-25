@extends('admin.layouts.master')
@section('title', __('Merchant Fee'))

@section('page_content')

    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border text-center">
                  <h3 class="box-title">{{ __('Merchant Fee') }}</h3>
                </div>

                <form action="{{ url(config('adminPrefix').'/settings/merchant-fee') }}" method="post" class="form-horizontal" id="merchant-fee" >
                    {!! csrf_field() !!}

                    <!-- box-body -->
                    <div class="box-body">

                        <!-- facebook_client_id -->
                        <div class="form-group">
                          <label class="col-sm-3 control-label" for="merchant_fee">{{ __('Set Merchant Fee') }}</label>
                          <div class="col-sm-6">
                            <input type="text" name="merchant_fee" class="form-control" value="{{ isset($merchantFee) ? $merchantFee['fee'] : '-' }}" placeholder="0.00" id="merchant_fee">

                            @if($errors->has('merchant_fee'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('merchant_fee') }}</strong>
                                </span>
                            @endif
                          </div>
                        </div>

                    </div>
                    <!-- /.box-body -->

                    <!-- box-footer -->
                    @if(Common::has_permission(auth('admin')->user()->id, 'edit_merchant_fee'))
                        <div class="box-footer">
                          <button class="btn btn-primary btn-flat pull-right" type="submit">{{ __('Submit') }}</button>
                        </div>
                    @endif
                    <!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>


<script type="text/javascript">

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    $('#merchant-fee').validate({
        rules: {
            merchant_fee: {
                required: true,
                number: true,
            },
        },
    });

</script>

@endpush
