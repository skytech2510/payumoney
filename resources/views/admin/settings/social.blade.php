@extends('admin.layouts.master')
@section('title', __('Social Settings'))

@section('page_content')
    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Social Links Form') }}</h3>
                </div>

                <form action="{{ url(config('adminPrefix').'/settings/social_links') }}" method="post" class="form-horizontal"
                      id="social_links">
                {!! csrf_field() !!}

                <!-- box-body -->
                    <div class="box-body">
                    @foreach($result as $row)
                        <!-- facebook -->
                            <div class="form-group row">
                                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end">{{ str_replace('_',' ',ucfirst($row->name)) }}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="{{ $row->name }}" class="form-control f-14"
                                           value="{{ $row->url }}">

                                    @if($errors->has($row->name))
                                        <span class="help-block">
		                          <strong class="text-danger">{{ $errors->first($row->name) }}</strong>
		                        </span>
                                    @endif
                                </div>
                            </div>
                    @endforeach

                    </div>
                    <!-- /.box-body -->

                    <!-- box-footer -->
                    @if(Common::has_permission(auth('admin')->user()->id, 'edit_social_links'))
                        <div class="box-footer">
                            <button class="btn btn-theme pull-right f-14" type="submit">{{ __('Submit') }}</button>
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

    $('#social_links').validate({
        rules: {
            facebook: {
                // required: true,
                url: true,
            },
            google_plus: {
                // required: true,
                url: true,
            },
            twitter: {
                // required: true,
                url: true,
            },
            linkedin: {
                // required: true,
                url: true,
            },
            pinterest: {
                // required: true,
                url: true,
            },
            youtube: {
                // required: true,
                url: true,
            },
            instagram: {
                // required: true,
                url: true,
            },
        },
    });

</script>

@endpush
