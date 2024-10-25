@extends('admin.layouts.master')

@section('title', __('Edit Country'))

@section('page_content')

  <div class="row">
    <div class="col-md-3 settings_bar_gap">
      @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
      <!-- Horizontal Form -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">{{ __('Edit Country') }}</h3>
        </div>

        <!-- form start -->
        <form method="POST" action="{{ url(config('adminPrefix').'/settings/edit_country', $result->id) }}" class="form-horizontal" id="edit_country_form">
          @csrf

          <div class="box-body">
            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="short_name">{{ __('Short Name') }}</label>
              <div class="col-sm-6">
                <input type="text" name="short_name" class="form-control f-14" placeholder="{{ __('Short Name') }}" id="short_name" value="{{ $result->short_name }}">
                @if($errors->has('short_name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('short_name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Long Name') }}</label>
              <div class="col-sm-6">
                <input type="text" name="name" class="form-control f-14" placeholder="{{ __('Long Name') }}" id="name" value="{{ $result->name }}">
                @if($errors->has('name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="iso3">{{ __('ISO3') }}</label>
              <div class="col-sm-6">
                <input type="text" name="iso3" class="form-control f-14" placeholder="{{ __('ISO3') }}" id="iso3" value="{{ $result->iso3 }}">
                @if($errors->has('iso3'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('iso3') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="number_code">{{ __('Number Code') }}</label>
              <div class="col-sm-6">
                <input type="text" name="number_code" class="form-control f-14" placeholder="{{ __('Number Code') }}" id="number_code" value="{{ $result->number_code }}">
                @if($errors->has('number_code'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('number_code') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="phone_code">{{ __('Phone Code') }}</label>
              <div class="col-sm-6">
                <input type="text" name="phone_code" class="form-control f-14" placeholder="{{ __('Phone Code') }}" id="phone_code" value="{{ $result->phone_code }}">
                @if($errors->has('phone_code'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('phone_code') }}</strong>
                </span>
                @endif
              </div>
            </div>

            @if ($result->is_default == 'yes')

            <div class="form-group row">
              <label class="col-sm-3 control-label f-14 fw-bold text-sm-end">{{ __('Default') }}</label>
              <div class="col-sm-6">
              <p class="f-14 mb-0"><span class="label label-danger">{{ __('The default :x can not be changed.', ['x' => __('status')]) }}</span></p><p><span>
              </div>
            </div>

            @else
            <div class="form-group row">
              <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="is_default">{{ __('Default') }}</label>
              <div class="col-sm-6">
                  <select class="select2 form-control f-14" name="is_default" id="is_default">
                      <option value='no' {{ $result->is_default == 'no' ? 'selected' : '' }}>{{ __('No') }}</option>
                      <option value='yes' {{ $result->is_default == 'yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                  </select>
                @if($errors->has('is_default'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('is_default') }}</strong>
                </span>
                @endif
              </div>
            </div>
            @endif

          </div>

          <div class="box-footer">
            <a class="btn btn-theme-danger f-14" href="{{ url(config('adminPrefix').'/settings/country') }}">{{ __('Cancel') }}</a>
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

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
<!-- /dist -->

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

  $('#edit_country_form').validate({
    rules: {
      short_name: {
        required: true,
        maxlength: 2,
        lettersonly: true,
      },
      name: {
        required: true,
        // letters_with_spaces: true,
      },
      iso3: {
        required: true,
        maxlength: 3,
        lettersonly: true,
      },
      number_code: {
        required: true,
        digits: true
      },
      phone_code: {
        required: true,
        digits: true
      },
    },
    messages: {
      short_name: {
        lettersonly: "Please enter letters only!",
      },
      iso3: {
        lettersonly: "Please enter letters only!",
      },
    },
  });

</script>
@endpush
