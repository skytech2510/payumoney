'use strict';

if ($('.content-wrapper').find('#ticket-reply').length) {
    $(function () {
        $(".select2").select2({
        });
    });

    $(function () {
        $('.message').wysihtml5({
            events: {
                change: function () {
                    if($('.message').val().length === 0 ) {
                        $('#error-message').addClass('error').html('This field is required.').css("font-weight", "bold");
                    } else {
                        $('#error-message').html('');
                    }
                }
            }
        });
    });

    $(function () {
        $('.editor').wysihtml5({
            events: {
                change: function () {
                    if($('.editor').val().length === 0 ) {
                        $('#error-message-modal').addClass('error').html('This field is required.').css("font-weight", "bold");
                    } else {
                        $('#error-message-modal').html('');
                    }
                }
            }
        });
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
            if (element.prop('name') === 'message') {
                $('#error-message').html(error);
            } else if (element.prop('id') === 'editor') {
                $('#error-message-modal').html(error);
            } else {
                error.insertAfter(element);
            }
        }
    });

    $('#reply_form').validate({
        ignore: ":hidden:not(textarea)",
        rules: {
            message: "required",
            file: {
                extension: extensions,
            },
        },
        messages: {
            file: {
                extension: fileErrorMessage
            },
        },
        submitHandler: function(form)
        {
            $("#reply").attr("disabled", true);
            $(".fa-spin").removeClass("d-none");
            $("#reply_text").text('Replying...');

            $("#customer_reply_button").attr("disabled", true);
            $("#admin_reply_button").attr("disabled", true);
            $(".edit-btn").attr("disabled", true);

            $('#customer_reply_button').click(false);
            $('#admin_reply_button').click(false);
            $('.edit-btn').click(false);
            form.submit();
        }
    });

    $('#replyModal').validate({
        rules: {
            message:{
               required: true,
            }
        }
    });


    $( document ).ready(function(e)
    {
        $(".edit-btn").on('click', function()
        {
            var id = $(this).attr('data-id');
            var message = $(this).attr('data-message');
            if (message) {
                $('#replyModal iframe').contents().find('.wysihtml5-editor').html(message);
            }
            $("#reply_id").val(id);
        });
    });

    $('#status_ticket').on('change', function()
    {
        var status_id = $("#status_ticket").val();

        $.ajax({
            url: ticketStatusChangeUrl,
            method: "POST",
            data:{
                'status_id': status_id,
                'ticket_id': ticket_id,
                '_token': token
            },
            dataType:"json",
            success:function(data)
            {
                if(data.status == '1' ) {
                    $('#status_label').html(data.message);
                    location.reload().delay(10000);
                }
            }
        });
    });
}