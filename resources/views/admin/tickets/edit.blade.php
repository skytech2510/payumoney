@extends('admin.layouts.master')

@section('title', __('Edit Ticket'))

@section('head_style')
    <!-- wysihtml5 -->
    <link rel="stylesheet" type="text/css" href="{{  asset('public/dist/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">

    <!-- jquery-ui-1.12.1 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.css')}}">
@endsection

@section('page_content')

<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="top-bar-title padding-bottom">{{ __('Edit Ticket') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-body">

        <form class="form-horizontal" action="{{ url(config('adminPrefix').'/tickets/update') }}" method="POST" id="edit_ticket_form">
            {{csrf_field()}}

            <input type="hidden" name="code" value="{{ $ticket->code }}">

            <input type="hidden" name="id" value="{{ $ticket->id }}">

            <input id="user_id" type="hidden" name="user_id" value="{{ $ticket->user_id }}">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-2 control-label f-14 fw-bold text-sm-end require" for="subject">{{ __('Subject') }}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control f-14" name="subject" value="{{ $ticket->subject }}" id="subject">
                            @if($errors->has('subject'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 control-label f-14 fw-bold text-sm-end require" for="message">{{ __('Body') }}</label>
                        <div class="col-sm-10">
                            <textarea class="message form-control f-14" name="message" id="message" cols="30" rows="10">{!!strip_tags($ticket->message)!!}</textarea>
                            @if($errors->has('message'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('message') }}</strong>
                                </span>
                            @endif
                            <div id="error-message"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end require" for="assignee">{{ __('Assignee') }}</label>
                        <div class="col-sm-8">
                            <select name="assignee" class="form-control select2" id="assignee">
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ $admin->id == $ticket->admin_id ? 'selected' : '' }} >{{ getColumnValue($admin) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="status">{{ __('Status') }}</label>
                        <div class="col-sm-8">
                            <select name="status" class="form-control select2" id="status">
                                @foreach($ticket_statuses as $ticket_status)
                                    <option value="{{ $ticket_status->id }}" {{ $ticket_status->id == $ticket->ticket_status_id ? 'selected' : '' }}>{{ $ticket_status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end require" for="user_input">{{ __('User') }}</label>
                        <div class="col-sm-8">

                            <input id="user_input" type="text" name="user" placeholder="{{ __('Enter Name') }}" class="form-control f-14" value="{{ getColumnValue($ticket->user) }}"
                            {{  isset($user) && ($user->id == $ticket->user_id) ? 'selected' : '' }}>

                            <span class="f-12" id="error-user"></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end require" for="priority">{{ __('Priority') }}</label>
                        <div class="col-sm-8">
                            <select name="priority" id="priority" class="form-control select2">
                                <option value="Low" {{ $ticket->priority == 'Low' ? 'selected' : '' }} >{{ __('Low') }}</option>
                                <option value="Normal" {{ $ticket->priority == 'Normal' ? 'selected' : '' }} >{{ __('Normal') }}</option>
                                <option value="High" {{ $ticket->priority == 'High' ? 'selected' : '' }} >{{ __('High') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6" id="assigned_email_div">
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 control-label f-14 fw-bold text-sm-end" for="assigned_email">{{ __('Email') }}</label>
                        <div class="col-sm-8">
                            <input id="assigned_email" type="text" class="form-control f-14" readonly name="email" value="{{ getColumnValue($ticket->user, 'email', '') }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-6 offset-md-2">
                    <a id="cancel_anchor" class="btn btn-theme-danger f-14 me-1" href="{{ url(config('adminPrefix').'/tickets/list') }}">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-theme f-14" id="update_ticket"><i class="fa fa-spinner fa-spin d-none"></i> <span id="update_ticket_text">{{ __('Update') }}</span></button>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/plugins/jquery-validation-1.17.0/dist/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- wysihtml5 -->
<script src="{{ asset('public/dist/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}" type="text/javascript"></script>

<!-- jquery-ui-1.12.1 -->
<script src="{{ asset('public/dist/libraries/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
        $('.message').wysihtml5({
            events: {
                change: function () {
                    if($('#message').val().length === 0 )
                    {
                        $('#error-message').addClass('error').html('This field is required.').css("font-weight", "bold");
                    }
                    else
                    {
                        $('#error-message').html('');
                    }
                }
            }
        });
        $(".select2").select2({});
    });

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element)
        {
            if (element.prop('name') === 'message')
            {
                $('#error-message').html(error);
            } else {
                error.insertAfter(element);
            }
        }
    });

    $('#edit_ticket_form').validate({
        ignore: ":hidden:not(textarea)",
        rules: {
            subject: {
                required: true,
            },
            message: "required",
            user: {
                required: true,
            },
        },
        submitHandler: function(form)
        {
            $("#update_ticket").attr("disabled", true);
            $(".fa-spin").removeClass("d-none");
            $("#update_ticket_text").text('Updating...');
            $('#cancel_anchor').attr("disabled",true);
            $('#update_ticket').click(false);
            form.submit();
        }
    });

    $(document).ready(function()
    {
        $("#user_input").on('keyup keypress', function(e)
        {
            if (e.type=="keyup" || e.type=="keypress")
            {
                $('#assigned_email_div').hide();

                var user_input = $('form').find("input[name='user']").val();

                if(user_input.length === 0)
                {
                    $('#user_id').val('');
                    $('#error-user').html('');
                }
            }
        });

        $('#user_input').autocomplete(
        {
            source:function(req,res)
            {
                if (req.term.length > 0)
                {
                    $.ajax({
                        url:'{{ url(config('adminPrefix').'/ticket_user_search') }}',
                        dataType:'json',
                        type:'get',
                        data:{
                            search:req.term
                        },
                        success:function (response)
                        {
                            // console.log(response);

                            $('form').find("button[type='submit']").prop('disabled',true);

                            if(response.status == 'success')
                            {
                                res($.map(response.data, function (item)
                                {
                                    $('#assigned_email_div').show();

                                    return {
                                            id : item.user_id,
                                            first_name : item.first_name,
                                            last_name : item.last_name,
                                            value: item.first_name + ' ' + item.last_name, //don't change value property name
                                            email: item.email,
                                        }
                                    }
                                ));

                            }
                            else if(response.status == 'fail')
                            {
                                $('#assigned_email').val('');
                                $('#assigned_email_div').hide();
                                $('#error-user').addClass('text-danger').html('User Does Not Exist!');
                            }
                        }
                    })
                }
                else
                {
                    // console.log(req.term.length);
                    $('#user_id').val('');
                }
            },
            select: function (event, ui)
            {
                var e = ui.item;

                $('form').find("button[type='submit']").prop('disabled',false);

                $('#error-user').html('');

                $('#user_id').val(e.id);

                $('#assigned_email').val(e.email);
            },
            minLength: 0,
            autoFocus: true
        });
    });

</script>

@endpush

