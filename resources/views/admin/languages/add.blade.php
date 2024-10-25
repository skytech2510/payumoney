@extends('admin.layouts.master')

@section('title', __('Add Language'))

@section('page_content')

  <div class="row">
    <div class="col-md-3 settings_bar_gap">
      @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
      <!-- Horizontal Form -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">{{ __('Add Language') }}</h3>
        </div>

        <!-- form start -->
        <form method="POST" action="{{ url(config('adminPrefix').'/settings/add_language') }}" class="form-horizontal" enctype="multipart/form-data" id="add_language_form">
          @csrf

          <div class="box-body">
            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
              <div class="col-sm-6">
                <input type="text" name="name" class="form-control f-14" value="{{ old('name') }}" placeholder="{{ __('name') }}" id="name">
                @if($errors->has('name'))
                <span class="error">
                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="short_name">{{ __('Short Name') }}</label>
              <div class="col-sm-6">
                <input type="text" name="short_name" class="form-control f-14" value="{{ old('short_name') }}" placeholder="{{ __('short name') }}" id="short_name">
                @if($errors->has('short_name'))
                <span class="error">
                  <strong class="text-danger">{{ $errors->first('short_name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="language-flag">{{ __('Flag') }}</label>
              <div class="col-sm-6">
                <input type="file" name="flag" class="form-control f-14 input-file-field" id="language-flag">
                @if($errors->has('flag'))
                  <span class="error">
                    <strong class="text-danger">{{ $errors->first('flag') }}</strong>
                  </span>
                @endif
                <div class="clearfix"></div>
                <small class="form-text text-muted"><strong>{{ allowedImageDimension(120,80) }}</strong></small>
                <div class="setting-img">
                  <img src='{{ image('null', 'language_flag') }}' width="120" height="80" id="language-flag-demo-preview">
                </div>
              </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="status">{{ __('Status') }}</label>
                <div class="col-sm-6">
                    <select class="select2" name="status" id="status">
                        <option value='Active'>{{ __('Active') }}</option>
                        <option value='Inactive'>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
          </div>

          <div class="box-footer">
            <a class="btn btn-theme-danger f-14" href="{{ url(config('adminPrefix').'/settings/language') }}">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-theme pull-right f-14">{{ __('Add') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<!-- read-file-on-change -->
@include('common.read-file-on-change')

<script type="text/javascript">

  $(window).on('load',function() {
    $(".select2").select2({});
  });

  // preview language flag on change
  $(document).on('change','#language-flag', function()
  {
      let orginalSource = '{{ url('public/uploads/userPic/default-image.png') }}';
      readFileOnChange(this, $('#language-flag-demo-preview'), orginalSource);
  });

  $.validator.setDefaults({
      highlight: function(element) {
        $(element).parent('div').addClass('has-error');
      },
      unhighlight: function(element) {
       $(element).parent('div').removeClass('has-error');
     },
  });

  $('#add_language_form').validate({
    rules: {
      name: {
        required: true,
      },
      short_name: {
        required: true,
        maxlength: 2,
        lettersonly: true,
      },
      flag: {
        extension: "png|jpg|jpeg|gif|bmp",
      },
    },
    messages: {
      short_name: {
        lettersonly: "Please enter letters only.",
      },
      flag: {
        extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
      },
    },
  });

</script>
@endpush
