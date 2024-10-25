@extends('admin.layouts.master')

@section('title', __('Change Password'))

@section('page_content')

  <!-- Main content -->

    <!-- Default box -->
      <div class="row">
        <div class="offset-2 col-md-8">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="text-center f-24 mt-2">{{ __('Change Password') }}</h3>
            </div>
            <!-- /.box-header -->

            <!-- form start -->
            <form action='{{  url(config('adminPrefix')."/change-password") }}' method="POST" class="form-horizontal" id="password_form" >
              {!! csrf_field() !!}


              <input type="hidden" value="{{$admin_id}}" name="id" id="id" />

              <div class="mt-4">
                <div class="form-group row align-items-center">
                  <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="old_pass">{{ __('Current password') }}</label>
                  <div class="col-sm-7">
                    <input type="password" class="form-control" name="old_pass" id="old_pass">
                    <span id="password_error"></span>
                  </div>
                </div>

                <div class="form-group row align-items-center">
                  <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="new_pass">{{ __('New password') }}</label>
                  <div class="col-sm-7">
                    <input type="password" class="form-control" id="new_pass" name="new_pass">
                  </div>
                </div>

                <div class="form-group row align-items-center">
                  <label class="col-sm-3 control-label f-14 fw-bold text-sm-end" for="new_pass_confirmation">{{ __('Confirm new password') }}</label>
                  <div class="col-sm-7">
                    <input type="password" class="form-control" id="new_pass_confirmation" name="new_pass_confirmation">
                  </div>
                </div>

              </div>
              <!-- /.box-body -->

              <div class="form-group row pb-4">
                <div class="col-sm-7 offset-3">
                    <a class="btn btn-danger btn-flat rounded f-14" href="{{ url(config('adminPrefix').'/profile') }}">{{ __('Cancel') }}</a>
                    <button class="btn btn-primary rounded btn-flat f-14 ms-1" type="submit" id="change_password_submit">{{ __('Submit') }}</button>
                </div>
              </div>
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

  $('#password_form').validate({
      errorClass: "error",
      rules: {
          old_pass: {
            required: true,
            minlength: 6,
          },
          new_pass: {
            required: true,
            minlength: 6,
          },
          new_pass_confirmation: {
              required: true,
              minlength: 6,
              equalTo: "#new_pass",
          },
      },
      messages: {
          new_pass_confirmation: {
            equalTo: "Please enter same value as the new password field!",
          },
      },
  });

// Validate Old Password
$(document).ready(function()
{
  $("#old_pass").on('input', function(e)
  {
    var id = $('#id').val();
    var old_pass = $(this).val();
    $.ajax({
        headers:
        {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        url: SITE_URL+"/"+ADMIN_PREFIX+"/check-password",
        dataType: "json",
        data: {
            'id': id,
            'old_pass': old_pass,
        }
    })
    .done(function(response)
    {
        if (response.status == true)
        {
          if(old_pass.length >= 6)
          {
            $('#password_error').addClass('error').html(response.fail).css({
               'color' : 'red !important',
               'font-size' : '14px',
               'font-weight' : '800',
               'padding-top' : '5px',
            });
            $('#change_password_submit').prop('disabled', true);
          }
          else
          {
            $('#password_error').html('');
            $('#change_password_submit').prop('disabled', false);
          }
        }
        else{
          $('#password_error').html('');
          $('#change_password_submit').prop('disabled', false);
        }
    });
  });
});

</script>

@endpush
