'use strict';

if ($('.content-wrapper').find('#discussion-reply').length) {
    $('#reply').validate({
		rules: {
			description: {
				required: true,
			},
			file: {
                extension: extensionsValidationRule,
            },
		},
		messages: {
			file: {
				extension: extensionsValidationMessage
			},
        },
        submitHandler: function(form)
        {
            $("#dispute-reply").attr("disabled", true).click(function (e)
	        {
	            e.preventDefault();
	        });
            $(".fa-spin").removeClass("d-none");
            $("#dispute-reply-text").text(submittingText);
            form.submit();
        }
	});

	$("#status").on('change', function()
	{
		var status = $(this).val();
		var id = $("#id").val();

		$.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
			method: "POST",
			url: ADMIN_URL + "/dispute/change_reply_status",
			data: { status: status, id:id}
		})
	    .done(function(data)
	    {
	    	let message = statusChangeText.replace(":x", status);
	    	var messageBox = '<div class="alert alert-success" role="alert">'+ message +'</div><br>';
	    	$("#alertDiv").html(messageBox);
			location.reload().delay(10000);
	    });
	});
}