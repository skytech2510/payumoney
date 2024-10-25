'use strict';

if ($('.main-containt').find('#ticketCreate').length) {
    $('#ticketCreateForm').on('submit', function () {
        $(".spinner").removeClass('d-none');
        $("#ticketCreateSubmitBtn").attr("disabled", true);
        $("#ticketCreateSubmitBtnText").text(submitButtonText);
    });
}

if ($('.main-containt').find('#ticketList').length) {
    $('#ticketStatus').on('change', function() {
        $('#ticketSearchForm').trigger('submit');
    });
}

if ($('.main-containt').find('#ticketReply').length) {
    const actualBtn = $('#file');
    $(document).on('change', '#file', function () {
        let fileExtension = actualBtn.val().replace(/^.*\./, '');
        let fileInput = actualBtn[0];

        if (!extensions.includes(fileExtension)) {
            fileInput.value = '';
            $('.file-error').addClass('error').text(extensionsValidationMessage);
            $('#fileSpan').fadeIn('slow').delay(2000).fadeOut('slow');
            return false;
        } else {
            $('.file-error').text('');
            return true;
        }
    });

    var paginate = 1;

    loadMoreData(paginate);

    $('#load-more').on('click', function() {
        var page = $(this).data('paginate');
     
        loadMoreData(page);
        $(this).data('paginate', page+1);
    });
   
    function loadMoreData(paginate) {
        
        $.ajax({
            
            url:  ticketReplyLoadUrl,
            type: 'get',
            datatype: 'html',
            data: {
                'page': paginate
            },
            beforeSend: function() {
                $('#load-more').text(loadingText);
            }
        })
        .done(function(data) {
            
            if(data.length == 0) {
                $('.invisible').removeClass('invisible');
                $('#load-more').hide();
                return false;
            } else {
                $('#load-more').text(loadMoreText);
                $('#ticket-replies').append(data);
            }
        })
    }


    $('#ticketReplyForm').on('submit', function () {
        $("#ticketReplyLoader").removeClass('d-none');
        $("#ticketReplySubmitBtn").attr("disabled", true);
        $("#ticketReplySubmitBtnText").text('');
    });

    $('#ticketStatusChangeForm').on('submit', function () {
        $("#ticketStatusChangeLoader").removeClass('d-none');
        $("#ticketStatusChangeSubmitBtn").attr("disabled", true);
        $("#ticketStatusChangeSubmitBtnText").text('');
    });
}