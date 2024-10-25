@extends('admin.layouts.master')

@section('title', __('Email Templates'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
@endsection

@section('page_content')
    <div class="row" id="emailTemplate">
        <div class="col-md-3">
            @foreach ($emailTemplates as $templateGroupName => $templates)
                <div class="box box-primary">
                    <!-- Email template menu -->
                    <div class="box-header with-border">
                        <h3 class="box-title underline">{{ $templateGroupName }}</h3>
                    </div>
                    <div class="box-body no-padding d-inline-block">
                        <ul class="nav nav-pills nav-stacked row">
                            @foreach ($templates as $templateName => $template)
                                <li class="{{ $templateAlias === $template['en']->alias ? 'active' : '' }}">
                                    <a href="{{ route('email.template.index', $template['en']->alias) }}">{{ $templateName }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $templateData[0]->subject }}</h3>
                </div>

                <form action='{{ route('email.template.update', $templateAlias) }}' method="post" id="emailTemplateForm">
                    @csrf

                    <!-- English -->
                    <div class="box-body">
                        <!-- Subject -->
                        <div class="form-group">
                            <label class="f-14 fw-bold mb-1" class="f-14 fw-bold mb-1" for="en_subject">{{ __('Subject') }}</label>
                            <input class="form-control f-14 " name="en[subject]" type="text" value="{{ $templateData[0]->subject }}" id="en_subject">
                        </div>

                        <!-- Body -->
                        <div class="form-group">
                            <textarea name="en[body]" class="form-control f-14 editor h-300">{{ $templateData[0]->body }}</textarea>
                        </div>

                        <div class="box-group" id="accordion">
                            <!-- Arabic -->
                            <div class="panel box box-primary">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapseOne" aria-expanded="false" class="collapsed">Arabic</a>
                                    </h4>
                                </div>
                                <div id="collapseOne" class="panel-collapse collapse h-auto" aria-expanded="false">
                                    <div class="box-body">
                                        <!-- Subject -->
                                        <div class="form-group">
                                            <label class="f-14 fw-bold mb-1" for="ar_subject">{{ __('Subject') }}</label>
                                            <input class="form-control f-14 " name="ar[subject]" type="text" value="{{ $templateData[1]->subject }}" id="ar_subject">
                                        </div>
                                        <!-- Body -->
                                        <div class="form-group">
                                            <textarea name="ar[body]" class="form-control f-14 editor h-300">{{ $templateData[1]->body }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- French -->
                            <div class="panel box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapseThree" class="collapsed" aria-expanded="false">French</a>
                                    </h4>
                                </div>
                                <div id="collapseThree" class="panel-collapse collapse h-auto" aria-expanded="false">
                                    <div class="box-body">
                                        <!-- Subject -->
                                        <div class="form-group">
                                            <label class="f-14 fw-bold mb-1" for="fr_subject">{{ __('Subject') }}</label>
                                            <input class="form-control f-14 " name="fr[subject]" type="text" value="{{ $templateData[2]->subject }}" id="fr_subject">
                                        </div>
                                        <!-- Body -->
                                        <div class="form-group">
                                            <textarea name="fr[body]" class="form-control f-14 editor h-300">{{ $templateData[2]->body }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Português -->
                            <div class="panel box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapseTwo" class="collapsed" aria-expanded="false">Português</a>
                                    </h4>
                                </div>
                                <div id="collapseTwo" class="panel-collapse collapse h-auto" aria-expanded="false">
                                    <div class="box-body">
                                        <!-- Subject -->
                                        <div class="form-group">
                                            <label class="f-14 fw-bold mb-1" for="pt_subject">{{ __('Subject') }}</label>
                                            <input class="form-control f-14 " name="pt[subject]" type="text" value="{{ $templateData[3]->subject }}" id="pt_subject">
                                        </div>
                                        <!-- Body -->
                                        <div class="form-group">
                                            <textarea name="pt[body]" class="form-control f-14 editor h-300">{{ $templateData[3]->body }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Russian -->
                            <div class="panel box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapseFour" class="collapsed" aria-expanded="false">Russian</a>
                                    </h4>
                                </div>
                                <div id="collapseFour" class="panel-collapse collapse h-auto" aria-expanded="false">
                                    <div class="box-body">
                                        <!-- Subject -->
                                        <div class="form-group">
                                            <label class="f-14 fw-bold mb-1" for="ru_subject">{{ __('Subject') }}</label>
                                            <input class="form-control f-14 " name="ru[subject]" type="text" value="{{ $templateData[4]->subject }}" id="ru_subject">
                                        </div>
                                        <!-- Body -->
                                        <div class="form-group">
                                            <textarea name="ru[body]" class="form-control f-14 editor h-300">{{ $templateData[4]->body }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Spanish -->
                            <div class="panel box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapseFive" class="collapsed" aria-expanded="false">Spanish</a>
                                    </h4>
                                </div>
                                <div id="collapseFive" class="panel-collapse collapse h-auto" aria-expanded="false">
                                    <div class="box-body">
                                        <!-- Subject -->
                                        <div class="form-group">
                                            <label class="f-14 fw-bold mb-1" for="es_subject">{{ __('Subject') }}</label>
                                            <input class="form-control f-14 " name="es[subject]" type="text" value="{{ $templateData[5]->subject }}" id="es_subject">
                                        </div>
                                        <!-- Body -->
                                        <div class="form-group">
                                            <textarea name="es[body]" class="form-control f-14 editor h-300">{{ $templateData[5]->body }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Turkish -->
                            <div class="panel box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapseSix" class="collapsed" aria-expanded="false">Turkish</a>
                                    </h4>
                                </div>
                                <div id="collapseSix" class="panel-collapse collapse h-auto" aria-expanded="false">
                                    <div class="box-body">
                                        <!-- Subject -->
                                        <div class="form-group">
                                            <label class="f-14 fw-bold mb-1" for="tr_subject">{{ __('Subject') }}</label>
                                            <input class="form-control f-14 " name="tr[subject]" type="text" value="{{ $templateData[6]->subject }}" id="tr_subject">
                                        </div>
                                        <!-- Body -->
                                        <div class="form-group">
                                            <textarea name="tr[body]" class="form-control f-14 editor h-300">{{ $templateData[6]->body }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chinese -->
                            <div class="panel box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapseSeven" class="collapsed" aria-expanded="false">Chinese</a>
                                    </h4>
                                </div>
                                <div id="collapseSeven" class="panel-collapse collapse h-auto" aria-expanded="false">
                                    <div class="box-body">
                                        <!-- Subject -->
                                        <div class="form-group">
                                            <label class="f-14 fw-bold mb-1" for="ch_subject">{{ __('Subject') }}</label>
                                            <input class="form-control f-14 " name="ch[subject]" type="text" value="{{ $templateData[7]->subject }}" id="ch_subject">
                                        </div>
                                        <!-- Body -->
                                        <div class="form-group">
                                            <textarea name="ch[body]" class="form-control f-14 editor h-180">{{ $templateData[7]->body }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="box-footer">
                        <div class="pull-right">
                            @if (Common::has_permission(auth()->guard('admin')->id(), 'edit_email_template'))
                                <button type="submit" class="btn btn-theme f-14" id="emailTemplateUpdateSubmitBtn">
                                    <i class="fa fa-spinner fa-spin f-14 d-none"></i>
                                    <span id="emailTemplateUpdateSubmitBtnText">{{ __('Update') }}</span>
                                </button>
                            @endif

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')
    <script src="{{ asset('public/dist/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}" type="text/javascript"></script>
    <script>
        "use strict"
        var csrfToken = $('[name="_token"]').val();
        var submitBtnText = "{{ __('Updating...') }}";
    </script>
    <script src="{{ asset('public/admin/customs/js/verifications.min.js') }}"></script>
@endpush
