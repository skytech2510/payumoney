@extends('admin.layouts.master')

@section('title', __('Edit Language'))

@section('head_style')
  <!-- sweetalert -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.css')}}">
@endsection

@section('page_content')
  <div class="row">
    <div class="col-md-3 settings_bar_gap">
      @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
      <!-- Horizontal Form -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">{{ __('Edit Language') }}</h3>
        </div>

        <!-- form start -->
        <form method="POST" action="{{ url(config('adminPrefix').'/settings/edit_language', $result->id) }}" class="form-horizontal" enctype="multipart/form-data" id="edit_language_form">
          @csrf

          <input type="hidden" value="{{ $result->id }}" name="id" id="id">

          <div class="box-body">
            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
              <div class="col-sm-6">
                <input type="text" name="name" class="form-control f-14" value="{{ $result->name }}" placeholder="{{ __('name') }}" id="name">
                @if($errors->has('name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="short_name">{{ __('Short Name') }}</label>
              <div class="col-sm-6">
                <input type="text" name="short_name" class="form-control f-14" value="{{ $result->short_name }}" placeholder="{{ __('short name') }}" id="short_name">
                @if($errors->has('short_name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('short_name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label for="flag" class="col-sm-3 control-label f-14 fw-bold text-sm-end">{{ __('Flag') }}</label>
              <div class="col-sm-6">
                <input type="file" name="flag" class="form-control f-14 input-file-field" data-rel="{{ isset($result->flag) ? $result->flag : '' }}" id="flag"
                value="{{ isset($result->flag) ? $result->flag : '' }}">
                <strong class="text-danger">{{ $errors->first('flag') }}</strong>
                <div class="clearfix"></div>
                <small class="form-text text-muted"><strong>{{ allowedImageDimension(120,80) }}</strong></small>


                <div class="setting-img">
                    <img src='{{ image($result->flag, 'language_flag') }}' width="120" height="80" id="{{ isset($result->flag) ? 'language-flag-preview' : 'language-demo-flag-preview' }}">
                    @if (isset($result->flag))
                    <span class="remove_language_preview" id="flag_preview"></span>
                    @endif
                </div>
              </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="status">{{ __('Status') }}</label>
                <div class="col-sm-6">
                    <select class="select2" name="status" id="status">
                        <option value='Active' {{ $result->status == 'Active' ? 'selected':"" }}>{{ __('Active') }}</option>
                        <option value='Inactive' {{ $result->status == 'Inactive' ? 'selected':"" }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
          </div>

          <div class="box-footer">
            <a class="btn btn-theme-danger f-14" href="{{ url(config('adminPrefix').'/settings/language') }}">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-theme pull-right f-14"> {{ __('Update') }} </button>
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

<!-- sweetalert -->
<script src="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.js')}}" type="text/javascript"></script>

<!-- read-file-on-change -->
@include('common.read-file-on-change')

<script type="text/javascript">

  $(window).on('load',function() {
    $(".select2").select2({});
  });

  // preview language logo on change
  $(document).on('change','#flag', function()
  {
      let orginalSource = '{{ url('public/uploads/userPic/default-image.png') }}';
      let flag = $('#flag').attr('data-rel');
      if (flag != '') {
        readFileOnChange(this, $('#language-flag-preview'), orginalSource);
        $('.remove_language_preview').remove();
      }
      readFileOnChange(this, $('#language-demo-flag-preview'), orginalSource);
  });

  $.validator.setDefaults({
      highlight: function(element) {
        $(element).parent('div').addClass('has-error');
      },
      unhighlight: function(element) {
       $(element).parent('div').removeClass('has-error');
     },
  });

  $('#edit_language_form').validate({
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

  $(document).ready(function()
  {
    $('#flag_preview').click(function()
    {
      var flag = $('#flag').attr('data-rel');
      var language_id = $('#id').val();

      if(flag)
      {
        $.ajax(
        {
          headers:
          {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type : "POST",
          url : SITE_URL+"/"+ADMIN_PREFIX+"/settings/language/delete-flag",
          // async : false,
          data: {
            'flag' : flag,
            'language_id' : language_id,
          },
          dataType : 'json',
          success: function(reply)
          {
            if (reply.success == 1){
                swal({title: "Deleted!", text: reply.message, type: "success"},
                   function(){
                       location.reload();
                   }
                );
            }else{
                alert(reply.message);
                location.reload();
            }
          }
        });
      }
    });
  });
</script>

@endpush
