@extends('admin.layouts.master')
@section('title', __('Preferences'))

@section('head_style')
@endsection

@section('page_content')
  <!-- Main content -->
  <div class="row">
      <div class="col-md-3 settings_bar_gap">
          {{-- settings_bar --}}
          @include('admin.common.settings_bar')
      </div>
      <div class="col-md-9">
          <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">{{ __('Manage Preferences') }}</h3>
              </div>
              <form action="{{ url(config('adminPrefix').'/save-preference') }}" method="post" id="preference-form" class="form-horizontal">
                {!! csrf_field() !!}
                <div class="box-body">

                  <!-- TimeZone -->
                  <div class="form-group row align-items-center">
                      <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="dflt_timezone">{{ __('TimeZone') }}</label>
                      <div class="col-sm-6">
                        <select class="select2" name="dflt_timezone" id="dflt_timezone">
                            @foreach($timezones as $timezone)
                              <option value="{{ $timezone['zone'] }}" {{ isset($prefData['preference']['dflt_timezone']) && $prefData['preference']['dflt_timezone'] == $timezone['zone'] ? 'selected' : "" }}>
                                {{ $timezone['diff_from_GMT'] . ' - ' . $timezone['zone'] }}
                              </option>
                            @endforeach
                        </select>
                      </div>
                  </div>

                  <!-- Send/Request Via -->
                  <div class="form-group row align-items-center">
                      <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="processed_by">{{ __('Send') }} {{ __('Request') }} {{ __('via') }}</label>
                      <div class="col-sm-6">
                        <select name="processed_by" class="select2" id="processed_by">
                            <option value="email" {{isset($prefData['preference']['processed_by']) && $prefData['preference']['processed_by'] == 'email' ? 'selected':""}}>{{ __('Email') }}</option>
                            <option value="phone" {{isset($prefData['preference']['processed_by']) && $prefData['preference']['processed_by'] == 'phone' ? 'selected':""}}>{{ __('Phone') }}</option>
                            <option value="email_or_phone" {{isset($prefData['preference']['processed_by']) && $prefData['preference']['processed_by'] == 'email_or_phone' ? 'selected':""}}>{{ __('Email or Phone') }}</option>
                        </select>
                        <span id="processed-by-check-error"></span>
                      </div>
                  </div>

                  <!-- Email Verification -->
                  <div class="form-group row align-items-center">
                  <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="verification_mail">{{ __('Email Verification') }}</label>
                      <div class="col-sm-6">
                        <select name="verification_mail" class="select2" id="verification_mail">
                            <option value="Enabled" {{ isset($prefData['preference']['verification_mail']) && $prefData['preference']['verification_mail'] == 'Enabled' ? 'selected' : "" }}>{{ __('Enabled') }}</option>
                            <option value="Disabled" {{ isset($prefData['preference']['verification_mail']) && $prefData['preference']['verification_mail'] == 'Disabled' ? 'selected' : "" }}>{{ __('Disabled') }}</option>
                        </select>
                      </div>
                  </div>

                  <!-- Phone Verification -->
                  <div class="form-group row align-items-center">
                  <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="phone_verification">{{ __('Phone Verification') }}</label>
                      <div class="col-sm-6">
                        <select name="phone_verification" class="select2" id="phone_verification">
                            <option value="Enabled" {{ isset($prefData['preference']['phone_verification']) && $prefData['preference']['phone_verification'] == 'Enabled' ? 'selected' : "" }}>{{ __('Enabled') }}</option>
                            <option value="Disabled" {{ isset($prefData['preference']['phone_verification']) && $prefData['preference']['phone_verification'] == 'Disabled' ? 'selected' : "" }}>{{ __('Disabled') }}</option>
                        </select>
                        <span id="phone_verification-error"></span>
                      </div>
                  </div>

                  <!-- 2-Factor Authentication -->
                  <div class="form-group row align-items-center">
                  <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="two_step_verification">{{ __('2-Factor Authentication') }}</label>
                      <div class="col-sm-6">
                        <select name="two_step_verification" id="two_step_verification" class="select2">
                            <option value="disabled" {{ isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'disabled' ? 'selected' : "" }}>{{ __('Disabled') }}</option>
                            <option value="by_email" {{isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_email' ? 'selected':""}}>{{ __('By email') }}</option>
                            <option {{ isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_google_authenticator' ? 'selected' : "" }}
                            value="by_google_authenticator">
                              {{ __('By google authenticator') }}
                            </option>
                            <option value="by_phone" {{ isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_phone' ? 'selected' : "" }}>{{ __('By phone') }}</option>
                            <option value="by_email_phone" {{ isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_email_phone' ? 'selected' : "" }}>{{ __('By email Or phone') }}</option>
                        </select>
                        <span id="sms-error"></span>
                      </div>
                  </div>

                  <!-- Row Per Page -->
                  <div class="form-group row align-items-center">
                    <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="row_per_page">{{ __('Row Per Page') }}</label>

                    <div class="col-sm-6">
                      <select name="row_per_page" class="select2" id="row_per_page">
                          <option value="10" {{ isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 10 ? 'selected' : "" }}>10</option>
                          <option value="25" {{ isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 25 ? 'selected' : "" }}>25</option>
                          <option value="50" {{ isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 50 ? 'selected' : "" }}>50</option>
                          <option value="100" {{ isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 100 ? 'selected' : "" }}>100</option>
                      </select>
                    </div>
                  </div>

                  <!-- Date Separator -->
                  <div class="form-group row align-items-center">
                    <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="date_sepa">{{ __('Date Separator') }}</label>

                    <div class="col-sm-6">
                      <select name="date_sepa" class="select2" id="date_sepa">
                          <option value="-" {{ isset($prefData['preference']['date_sepa']) && $prefData['preference']['date_sepa'] == '-' ? 'selected' : "" }}>-</option>
                          <option value="/" {{ isset($prefData['preference']['date_sepa']) && $prefData['preference']['date_sepa'] == '/' ? 'selected' : "" }}>/</option>
                          <option value="." {{ isset($prefData['preference']['date_sepa']) && $prefData['preference']['date_sepa'] == '.' ? 'selected' : "" }}>.</option>
                      </select>
                    </div>
                  </div>

                  <!-- Date Format -->
                  <div class="form-group row align-items-center">
                    <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="date_format">{{ __('Date Format') }}</label>
                    <div class="col-sm-6">
                      <select name="date_format" class="select2" id="date_format">
                          <option value="0" {{ isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 0 ? 'selected' : "" }}>yyyymmdd {2019 12 31}</option>
                          <option value="1" {{ isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 1 ? 'selected' : "" }}>ddmmyyyy {31 12 2019}</option>
                          <option value="2" {{ isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 2 ? 'selected' : "" }}>mmddyyyy {12 31 2019}</option>
                          <option value="3" {{ isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 3 ? 'selected' : "" }}>ddMyyyy &nbsp;&nbsp;&nbsp;{31 Dec 2019}</option>
                          <option value="4" {{ isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 4 ? 'selected' : "" }}>yyyyMdd &nbsp;&nbsp;&nbsp;{2019 Dec 31}</option>
                      </select>
                    </div>
                  </div>

                  <!-- Decimal Format -->
                  <div class="form-group row align-items-center">
                    <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="decimal_format_amount">{{ __('Decimal Format') }} (.)</label>
                    <div class="col-sm-6">
                      <select name="decimal_format_amount" class="select2" id="decimal_format_amount">
                          @for($i = 1; $i<=8; $i++)
                              <option value="{{$i}}" {{ isset($prefData['preference']['decimal_format_amount']) && $prefData['preference']['decimal_format_amount'] == $i ? 'selected' : "" }}>{{$i}}</option>
                          @endfor
                      </select>
                    </div>
                  </div>

                  <!-- Decimal Format In Crypto -->
                  <div class="form-group row align-items-center">
                    <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="decimal_format_amount_crypto">{{ __('Decimal Format') }} (.) {{ __('Crypto') }}</label>
                    <div class="col-sm-6">
                      <select name="decimal_format_amount_crypto" class="select2" id="decimal_format_amount_crypto">
                          @for($i = 1; $i<=8; $i++)
                              <option value="{{ $i }}" {{ isset($prefData['preference']['decimal_format_amount_crypto']) && $prefData['preference']['decimal_format_amount_crypto'] == $i ? 'selected' : "" }}>{{$i}}</option>
                          @endfor
                      </select>
                    </div>
                  </div>

                  {{-- Filesize --}}
        					<div class="form-group row">
        					  <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="file_size">{{ __('Max Filesize') }}</label>
        					  <div class="col-sm-6">
        					    <input type="text" name="file_size" class="form-control" value="{{ $prefData['preference']['file_size'] ?? '' }}" max="20" placeholder="{{ __('Max file upload size in MB (eg - 2MB)') }}" id="file_size">
        					  	<span class="text-danger">{{ $errors->first('file_size') }}</span>
                                <small class="form-text text-muted f-12"><strong>{{ __('Maximum uploaded file size upto :x MB', ['x' => __('20')]) }}</strong></small>
        					  </div>
        					</div>

                  <!-- Money Symbol Position -->
                  <div class="form-group row align-items-center">
                  <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="money_format">{{ __('Money Symbol Position') }}</label>
                      <div class="col-sm-6">
                        <select name="money_format" class="select2" id="money_format">
                            <option value="before" {{ isset($prefData['preference']['money_format']) && $prefData['preference']['money_format'] == 'before' ? 'selected' : "" }}>{{ __('Before') }} { $500 }</option>
                            <option value="after" {{ isset($prefData['preference']['money_format']) && $prefData['preference']['money_format'] == 'after' ? 'selected' : "" }}>{{ __('After') }} { 500$ }</option>
                        </select>
                      </div>
                  </div>

                  <!-- Thousand Separator -->
                  <div class="form-group row">
                  <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="thousand_separator">{{ __('Thousand Separator') }}</label>
                      <div class="col-sm-6">
                        <select name="thousand_separator" class="select2" id="thousand_separator">
                            <option data-decimal="." value="," {{ isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == ',' ? 'selected' : "" }}>,({{ __('comma') }})</option>
                            <option data-decimal="," value="." {{ isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == '.' ? 'selected' : "" }}>.({{ __('dot') }})</option>
                        </select>
                          <span class="f-14">{{ __('Followed By') }} <span>
                            <span class="f-20 fw-bold" id="separator">
                                {{ isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == '.' ? ',' : "" }}
                                {{ isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == ',' ? '.' : "" }}
                            </span> ({{ __('decimal') }})</span>
                          </span>
                      </div>
                  </div>

                  @if(Common::has_permission(auth('admin')->user()->id, 'edit_preference'))
                    <div class="row">
                        <div class="col-md-10">
                          <button type="submit" class="btn btn-theme pull-right f-14" id="preference-submit">
                                <i class="fa fa-spinner fa-spin d-none"></i> <span id="preference-submit-text">{{ __('Submit') }}</span>
                            </button>
                        </div>
                    </div>
                  @endif
                </div>
              </form>
          </div>
      </div>
  </div>
  <!-- /.box -->
@endsection

@push('extra_body_scripts')

  <!-- jquery.validate -->
  <script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

  <script type="text/javascript">

    $(window).on('load',function()
    {
        $(".select2").select2();
    });

    //thousand_separator
    $('#thousand_separator').on('change.select2', function()
    {
        $('#separator').html($('option:selected', this).attr('data-decimal'));
    });

    let condition;
    let errorElement;

    function checkSmsGatewaySettingsForPreference(condition, errorElement)
    {
        if (condition)
        {
            $.ajax(
            {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: SITE_URL+"/"+ADMIN_PREFIX+"/settings/check-sms-settings",
                dataType: "json",
                cache: false,
            }).done(function(response)
            {
                // console.log(response);
                if (response.status == false)
                {
                    errorElement.addClass('error').html(response.message).css("font-weight", "bold");
                    $('form').find("button[type='submit']").prop('disabled', true);
                }
                else
                {
                    errorElement.html('');
                    $('form').find("button[type='submit']").prop('disabled', false);
                }
            });
        }
        else
        {
            errorElement.html('');
            $('form').find("button[type='submit']").prop('disabled', false);
        }
    }

    //two_step_verification
    $(document).ready(function()
    {
        $("#two_step_verification").change(function()
        {
            condition = $(this).val() == 'by_phone' || $(this).val() == 'by_email_phone'
            errorElement = $('#sms-error');
            checkSmsGatewaySettingsForPreference(condition, errorElement);
        });
    });

    //check sms setting for phone_verification
    $(document).ready(function()
    {
        $("#phone_verification").change(function()
        {
            condition = $(this).val() == 'Enabled';
            errorElement = $('#phone_verification-error');
            checkSmsGatewaySettingsForPreference(condition, errorElement);
        });
    });

    //check sms setting for processed by - phone and email_or_phone
    $(document).ready(function()
    {
        $("#processed_by").change(function()
        {
            condition = $(this).val() == 'phone'||$(this).val() == 'email_or_phone';
            errorElement = $('#processed-by-check-error');
            checkSmsGatewaySettingsForPreference(condition, errorElement);
        });
    });


    $('#preference-form').validate({
        submitHandler: function(form)
        {
            $("#preference-submit").attr("disabled", true).click(function (e) {
                e.preventDefault();
            });
            $(".fa-spin").removeClass("d-none");
            $("#preference-submit-text").text('Submitting...');
            form.submit();
        }
    });

  </script>

@endpush