@extends('admin.layouts.master')
@section('title', __('Create Dispute'))

@section('page_content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border text-center">
                <h3 class="box-title">{{ __('Add Dispute') }}</h3>
            </div>

            <form method="POST" action="{{url(config('adminPrefix').'/dispute/open')}}" class="form-horizontal" id="dispute_add_form" accept-charset='UTF-8'>

                <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                <input type="hidden" name="transaction_id" value="{{$transaction->id}}">
                <input type="hidden" name="claimant_id" value="{{$transaction->user_id}}">
                <input type="hidden" name="defendant_id" value="{{$transaction->end_user_id}}">

                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="title">{{ __('Title') }}</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" value="{{old('title')}}" name="title" id="title">
                            <span class="text-danger">{{ $errors->first('title') }}</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="reason_id">{{ __('Reason') }}</label>
                        <div class="col-sm-6">
                            <select class="form-control" name="reason_id" id="reason_id">
                                @foreach ($reasons as $reason)
                                    <option value="{{ $reason->id }}">{{ $reason->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="description">{{ __('Description') }}</label>
                        <div class="col-sm-6">
                            <textarea class="form-control" rows="5" name="description" id="description">{{old('description')}}</textarea>
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a id="cancel_anchor" href="{{  url(config('adminPrefix')."/disputes") }}" class="btn btn-danger btn-flat">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary btn-flat pull-right" id="submit">
                            <i class="fa fa-spinner fa-spin f-14 d-none"></i> <span id="dispute_add_text">{{ __('Submit') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

$('#dispute_add_form').validate({
    rules: {
        title: {
            required: true,
        },
        description: {
            required: true,
        },
    },
    submitHandler: function(form)
    {
        $("#submit").attr("disabled", true);
        $(".fa-spin").removeClass("d-none");
        $("#dispute_add_text").text('Submitting...');
        $('#cancel_anchor').attr("disabled",true);
        form.submit();
    }
});

</script>
@endpush