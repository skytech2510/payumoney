"use strict";

$(function () {
    $(".select2").select2({});
});

$.ajaxSetup({
    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
});

// delete script for href
$(document).on('click', '.delete-warning', function(e){
    e.preventDefault();
    let url = $(this).attr('href');
    $('#delete-modal-yes').attr('href', url);
    $('#delete-warning-modal').modal('show');
});

//delete script for buttons
$('#confirmDelete').on('show.bs.modal', function (e) {
    $(this).find('.modal-body p').text($(e.relatedTarget).attr('data-message'));
    $(this).find('.modal-title').text($(e.relatedTarget).attr('data-title'));

    // Pass form reference to modal for submission on yes/ok
    var form  = $(e.relatedTarget).closest('form');
    $(this).find('.modal-footer #confirm').data('form', form);
});

$('#confirmDelete').find('.modal-footer #confirm').on('click', function(){
    $(this).data('form').trigger('submit');
});

// language
$('.lang').on('click', function() {
    var lang = $(this).attr('id');
    $.ajax({
        url: url,
        data: {
            _token:token,
            lang:lang
        },
        type: "POST",
        success:function(data){
            if(data == 1) {
                location.reload();
            }
        },
        error: function(xhr, desc, err) {
            return 0;
        }
    });
});