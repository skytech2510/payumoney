@extends('admin.layouts.master')

@section('title', __('Edit Meta'))

@section('page_content')

  <div class="row">
      <div class="col-md-3 settings_bar_gap">
        @include('admin.common.settings_bar')
      </div>
      <div class="col-md-9">
        <!-- Horizontal Form -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">{{ __('Edit Meta') }}</h3>
          </div>

          <!-- form start -->
          <form method="POST" action="{{ url(config('adminPrefix').'/settings/edit_meta', $result->id) }}" class="form-horizontal" id="meta_edit_form">
              @csrf

              <div class="box-body">

                  <div class="form-group row">
                    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="url">{{ __('Page Url') }}</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control f-14" name="url" value="{{ $result->url }}" placeholder="{{ __('url') }}" id="url">
                      @if($errors->has('url'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('url') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="title">{{ __('Page Title') }}</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control f-14" name="title" value="{{ $result->title }}" placeholder="{{ __('title') }}" id="title">
                      @if($errors->has('title'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('title') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="description">{{ __('Meta Description') }}</label>
                    <div class="col-sm-6">
                      <textarea rows="3" class="form-control f-14" name="description" placeholder="{{ __('meta description') }}" id="description">{{ $result->description }}</textarea>
                      @if($errors->has('description'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('description') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group row">
                    <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="keywords">{{ __('Keywords') }}</label>
                    <div class="col-sm-6">
                      <textarea rows="3" class="form-control f-14" name="keywords" placeholder="{{ __('meta keywords') }}" id="keywords">{{ $result->keywords }}</textarea>
                      @if($errors->has('keywords'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('keywords') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

              </div>

              <div class="box-footer">
                <a class="btn btn-theme-danger f-14" href="{{ url(config('adminPrefix').'/settings/metas') }}">{{ __('Cancel') }}<a>
                <button type="submit" class="btn btn-theme pull-right f-14">{{ __('Update') }}</button>
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

  jQuery.validator.addMethod("letters_with_spaces", function(value, element)
  {
    return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
  }, "Please enter letters only!");

  $.validator.setDefaults({
    highlight: function(element) {
      $(element).parent('div').addClass('has-error');
    },
    unhighlight: function(element) {
     $(element).parent('div').removeClass('has-error');
    },
  });

  $('#meta_edit_form').validate({
    rules: {
      url: {
        required: true,
      },
      title: {
        required: true,
        // letters_with_spaces: true,
      },
      description: {
        required: true,
        // letters_with_spaces: true,
      },
      keywords: {
        // required: true,
        // letters_with_spaces: true,
      },
    },
  });
</script>

@endpush
