@extends('admin.layouts.master')
@section('title', __('API Credentials Settings'))

@section('head_style')
  <!-- custom-checkbox -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/admin/customs/css/custom-checkbox.min.css') }}">

   <!-- sweetalert -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert/sweetalert.min.css')}}">

  <style type="text/css">
    @media only screen and (max-width: 767px) {
        .checkbox-container {
            padding-bottom: 7px;
        }
    }
  </style>
@endsection

@section('page_content')
    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>

        <div class="col-md-9">
            <div class="box box-info">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="tabs">
                      <li class="nav-item border-0"><a class="nav-link active" href="#tab_1" data-bs-toggle="tab" aria-expanded="false">{{ __('Google Play Store') }}</a></li>
                      <li class="nav-item border-0"><a class="nav-link" href="#tab_2" data-bs-toggle="tab" aria-expanded="false">{{ __('Apple Store') }}</a></li>
                    </ul>

                    <div class="tab-content">

                        <!-- Play Store -->
                        <div class="tab-pane fade in show active" id="tab_1">
                            <form action="{{ url(config('adminPrefix').'/settings/app-store-credentials/update-google-credentials') }}" method="POST" class="form-horizontal" id="app-store-google-credentials" enctype="multipart/form-data" >
                                {!! csrf_field() !!}

                                <input type="hidden" name="playstoreid" id="playstoreid" value="{{ isset($appStoreCredentialsForGoogle) ? $appStoreCredentialsForGoogle->id : '' }}">
                                <input type="hidden" name="playstorecompany" id="playstorecompany" value="{{ isset($appStoreCredentialsForGoogle) ? $appStoreCredentialsForGoogle->company : '' }}">

                                <div class="box-body">
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="has_app_playstore_credentials">{{ __('Available') }}</label>
                                        <div class="col-sm-6">
                                            <label class="checkbox-container">
                                              <input type="checkbox" class="has_app_playstore_credentials" name="has_app_playstore_credentials" value="Yes" id="has_app_playstore_credentials"
                                              {{ isset($appStoreCredentialsForGoogle->has_app_credentials) && $appStoreCredentialsForGoogle->has_app_credentials == 'Yes' ? 'checked' : '' }}
                                              >
                                              <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- PlayStore Link -->
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="playstore-link">{{ __('Play Store Link') }}</label>
                                        <div class="col-sm-5">
                                            <input class="form-control f-14 playstore-link" name="playstore[link]" type="text"
                                            value="{{ isset($appStoreCredentialsForGoogle->link) ? $appStoreCredentialsForGoogle->link : '' }}"
                                            id="playstore-link">
                                            @if ($errors->has('playstore[link]'))
                                                  <span class="error">
                                                      <strong>{{ $errors->first('playstore[link]') }}</strong>
                                                  </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- Play Store Logo -->
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end mt-2" for="playstore-logo">{{ __('Play Store Logo') }}</label>
                                        <div class="col-sm-5">
                                           <input type="file" name="playstore[logo]" class="form-control f-14 input-file-field" id="playstore-logo"
                                            data-rel="{{ isset($appStoreCredentialsForGoogle->logo) ? $appStoreCredentialsForGoogle->logo : '' }}" value="{{ isset($appStoreCredentialsForGoogle->logo) ? $appStoreCredentialsForGoogle->logo : '' }}">

                                            @if ($errors->has('playstore[logo]'))
                                                <span class="error">
                                                    <strong>{{ $errors->first('playstore[logo]') }}</strong>
                                                </span>
                                            @endif

                                            <div class="clearfix"></div>
                                            <small class="form-text text-muted f-12"><strong>{{ allowedImageDimension(125,50) }}</strong></small>

                                            @if (!empty($appStoreCredentialsForGoogle->logo))
                                                <div class="setting-img">
                                                    <div class="img-wrap">
                                                    <img src='{{ url('public/uploads/app-store-logos/'.$appStoreCredentialsForGoogle->logo) }}' width="125" height="50" id="play-store-logo-preview">
                                                    </div>
                                                    <span class="remove-store-logos" id="playstore-logo-delete"></span>
                                                </div>
                                            @else
                                                <img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}' width="125" height="50" id="play-store-logo-demo-preview">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="row">
                                  <div class="col-md-12">
                                      <div class="mt-10">
                                        <a href="{{ url(config('adminPrefix').'/settings/app-store-credentials') }}" class="btn btn-theme-danger f-14">{{ __('Cancel') }}</a>
                                        @if(Common::has_permission(auth('admin')->user()->id, 'edit_appstore_credentials'))
                                            <button class="btn btn-theme pull-right f-14" type="submit">{{ __('Sumbit') }}</button>
                                        @endif
                                      </div>
                                  </div>
                                </div>
                            </form>
                        </div>

                        <!-- Apple Store -->
                        <div class="tab-pane" id="tab_2">
                            <form action="{{ url(config('adminPrefix').'/settings/app-store-credentials/update-apple-credentials') }}" method="POST" class="form-horizontal" id="app-store-apple-credentials" enctype="multipart/form-data">
                                {!! csrf_field() !!}

                                <input type="hidden" name="appstoreid" id="appstoreid" value="{{ isset($appStoreCredentialsForApple) ? $appStoreCredentialsForApple->id : '' }}">
                                <input type="hidden" name="appstorecompany" id="appstorecompany" value="{{ isset($appStoreCredentialsForApple) ? $appStoreCredentialsForApple->company : '' }}">

                                <div class="box-body">
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="has_app_appstore_credentials">{{ __('Available') }}</label>
                                        <div class="col-sm-6">
                                            <label class="checkbox-container">
                                              <input type="checkbox" class="has_app_appstore_credentials" name="has_app_appstore_credentials" value="Yes" id="has_app_appstore_credentials" {{ isset($appStoreCredentialsForApple->has_app_credentials) && $appStoreCredentialsForApple->has_app_credentials == 'Yes' ? 'checked' : '' }}>
                                              <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- Apple Store Link -->
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end" for="applestore-link">{{ __('Apple Store Link') }}</label>
                                        <div class="col-sm-5">
                                            <input class="form-control f-14 applestore-link" name="applestore[link]" type="text"
                                            value="{{ isset($appStoreCredentialsForApple->link) ? $appStoreCredentialsForApple->link : '' }}"
                                            id="applestore-link">
                                            @if ($errors->has('applestore[link]'))
                                                  <span class="error">
                                                      <strong>{{ $errors->first('applestore[link]') }}</strong>
                                                  </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- Apple Store Logo -->
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label mt-11 f-14 fw-bold text-sm-end mt-2" for="applestore-logo">{{ __('Apple Store Logo') }}</label>
                                        <div class="col-sm-5">
                                          <input type="file" name="applestore[logo]" class="form-control f-14 input-file-field" id="applestore-logo"
                                            data-rel="{{ isset($appStoreCredentialsForApple->logo) ? $appStoreCredentialsForApple->logo : '' }}" value="{{ isset($appStoreCredentialsForApple->logo) ? $appStoreCredentialsForApple->logo : '' }}">

                                            @if ($errors->has('applestore[logo]'))
                                                <span class="error">
                                                    <strong>{{ $errors->first('applestore[logo]') }}</strong>
                                                </span>
                                            @endif

                                            <div class="clearfix"></div>
                                            <small class="form-text text-muted f-12"><strong>{{ allowedImageDimension(125,50) }}</strong></small>

                                            @if (!empty($appStoreCredentialsForApple->logo))
                                                <div class="setting-img">
                                                    <div class="img-wrap">
                                                    <img src='{{ url('public/uploads/app-store-logos/'.$appStoreCredentialsForApple->logo) }}' width="125" height="50" id="apple-store-logo-preview">
                                                    </div>
                                                    <span class="remove-store-logos" id="applestore-logo-delete"></span>
                                                </div>
                                            @else
                                                <img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}' width="125" height="50" id="apple-store-logo-demo-preview">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="row">
                                  <div class="col-md-12">
                                      <div class="mt-10">
                                        <a href="{{ url(config('adminPrefix').'/settings/app-store-credentials') }}" class="btn btn-theme-danger f-14">{{ __('Cancel') }}</a>

                                        @if(Common::has_permission(auth('admin')->user()->id, 'edit_appstore_credentials'))
                                            <button class="btn btn-theme pull-right f-14" type="submit">{{ __('Sumbit') }}</button>
                                        @endif
                                      </div>
                                  </div>
                                </div>
                            </form>
                        </div>

                    </div><!-- /.tab-content -->
                </div>
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

    $('#app-store-google-credentials').validate({
        rules: {
            "playstore[link]": {
                required: true,
                url: true,
            },
            "playstore[logo]":{
                required: true,
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
          "playstore[logo]": {
            extension: "Please select images(png|jpg|jpeg|gif|bmp) only!"
          },
        },
    });

    $('#app-store-apple-credentials').validate({
        rules: {
            "applestore[link]": {
                required: true,
                url: true,
            },
            "applestore[logo]":{
                required: true,
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
          "applestore[logo]": {
            extension: "Please select images(png|jpg|jpeg|gif|bmp) only!"
          },
        },
    });

    // preview play store logo on change
    $(document).on('change','#playstore-logo', function()
    {
        let orginalSource = '{{ url('public/uploads/app-store-logos/default-logo.jpg') }}';
        let playstoreLogo = $(this).attr('data-rel');
        if (playstoreLogo != '') {
          readFileOnChange(this, $('#play-store-logo-preview'), orginalSource);
          $('#playstore-logo-delete').remove();
        }
        readFileOnChange(this, $('#play-store-logo-demo-preview'), orginalSource);
    });

    // preview apple store logo on change
    $(document).on('change','#applestore-logo', function()
    {
        let orginalSource = '{{ url('public/uploads/app-store-logos/default-logo.jpg') }}';
        let applestore = $(this).attr('data-rel');
        if (applestore != '') {
          readFileOnChange(this, $('#apple-store-logo-preview'), orginalSource);
          $('#applestore-logo-delete').remove();
        }
        readFileOnChange(this, $('#apple-store-logo-demo-preview'), orginalSource);
    });

    //Delete playstoreLogo logo preview
    $(document).ready(function()
    {
        $('#playstore-logo-delete').click(function(){
            var playstoreLogo = $('#playstore-logo').attr('data-rel');
            var playstorecompany = $('#playstorecompany').val();

            if(playstoreLogo)
            {
              $.ajax(
              {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type : "POST",
                url : SITE_URL+"/"+ADMIN_PREFIX+"/settings/app-store-credentials/delete-playstore-logo",
                // async : false,
                data: {
                  'playstoreLogo' : playstoreLogo,
                  'playstorecompany' : playstorecompany,
                },
                dataType : 'json',
                success: function(reply)
                {
                  if (reply.success == 1)
                  {
                    swal({title: "", text: reply.message, type: "success"},function(){
                        location.reload();
                    });
                  }
                  else
                  {
                      alert(reply.message);
                      location.reload();
                  }
                }
              });
            }
        });
    });

    //Delete applestore logo preview
    $(document).ready(function()
    {
        $('#applestore-logo-delete').click(function(){
            var appleStoreLogo = $('#applestore-logo').attr('data-rel');
            var appstorecompany = $('#appstorecompany').val();

            if(appleStoreLogo)
            {
              $.ajax(
              {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type : "POST",
                url : SITE_URL+"/"+ADMIN_PREFIX+"/settings/app-store-credentials/delete-appstore-logo",
                // async : false,
                data: {
                  'appleStoreLogo' : appleStoreLogo,
                  'appstorecompany' : appstorecompany,
                },
                dataType : 'json',
                success: function(reply)
                {
                  if (reply.success == 1){
                    swal({title: "", text: reply.message, type: "success"},
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
