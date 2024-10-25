@extends('admin.layouts.master')

@section('title', __('Edit Merchant Group'))

@section('page_content')
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('Edit Merchant Package') }}</h3>
                </div>

                <!-- form start -->
                <form method="POST" action="{{ url(config('adminPrefix').'/settings/edit-merchant-group', $merchantGroup->id) }}" class="form-horizontal" id="merchant-group-edit-form">
                    @csrf

                    <div class="box-body">
                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="name">{{ __('Name') }}</label>
                            <div class="col-sm-6">
                                <input type="text" name="name" class="form-control f-14" value="{{ $merchantGroup->name }}" placeholder="{{ __('Edit name') }}" id="name">
                                @if($errors->has('name'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="description">{{ __('Description') }}</label>
                            <div class="col-sm-6">
                                <textarea placeholder="{{ __('Edit description') }}" rows="3" class="form-control f-14" name="description" id="description">{{ $merchantGroup->description }}</textarea>
                                @if($errors->has('description'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('description') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="fee">{{ __('Fee') }} (%)</label>
                            <div class="col-sm-6">
                                <input type="text" name="fee" class="form-control f-14" value="{{ number_format((float)$merchantGroup->fee, preference('decimal_format_amount')) }}" placeholder="{{ __('Edit fee') }}" id="fee" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                                @if($errors->has('fee'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('fee') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="fee-bearer">{{ __('Fee Bearer') }}</label>
                            <div class="col-sm-6">
                            <select class="select2" name="fee_bearer" id="fee-bearer">
                                <option value='User' {{ $merchantGroup->fee_bearer == 'User' ? 'selected' : '' }} >{{ __('User') }}</option>
                                <option value='Merchant' {{ $merchantGroup->fee_bearer == 'Merchant' ? 'selected' : '' }} >{{ __('Merchant') }}</option>
                            </select>
                            @if($errors->has('fee_bearer'))
                                <span class="error">
                                <strong class="text-danger">{{ $errors->first('fee_bearer') }}</strong>
                                </span>
                            @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="default">{{ __('Default') }}</label>
                            <div class="col-sm-6">
                                @if ($merchantGroup->is_default == 'Yes')
                                    <p class="f-14 mb-0 mt-10"><span class="label label-success">{{$merchantGroup->is_default}}</span></p>
                                    <input type="hidden" value="{{ $merchantGroup->is_default }}" name="default">
                                @else
                                    <select class="select2" name="default" id="default">
                                        <option value='No' {{ $merchantGroup->is_default == 'No' ? 'selected':"" }}>{{ __('No') }}</option>
                                        <option value='Yes' {{ $merchantGroup->is_default == 'Yes' ? 'selected':"" }}>{{ __('Yes') }}</option>
                                    </select> 
                                @endif
                            </div>
                        </div>
                    </div> 

                    <div class="box-footer">
                        <a class="btn btn-theme-danger f-14" href="{{ url(config('adminPrefix').'/settings/merchant-group') }}">{{ __('Cancel') }}</a>
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

@include('common.restrict_number_to_pref_decimal')
@include('common.restrict_character_decimal_point')

<script type="text/javascript">

    $(function () {
        $(".select2").select2({});
    });

    function restrictNumberToPrefdecimalOnInput(e)
    {
        restrictNumberToPrefdecimal(e, 'fiat');
    }

    jQuery.validator.addMethod("letters_with_spaces", function (value, element) {
        return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
    }, "Please enter letters only!");

    $.validator.setDefaults({
        highlight: function (element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            if (element.prop('type') === 'checkbox') {
                $('#error-message').html(error);
            } else {
                error.insertAfter(element);
            }
        }
    });


    $('#merchant-group-edit-form').validate({
        rules: {
            name: {
                required: true,
                letters_with_spaces: true,
            },
            description: {
                required: true,
            },
            fee: {
                required: true,
                number: true,
            },
        },
    });
</script>

@endpush
