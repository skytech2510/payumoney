'use strict';

$(function(){

    var currentTimeInSeconds = Math.floor(Date.now() / 1000);

    var time_expires = $('#time_expires').val();

    var time = time_expires - currentTimeInSeconds;

    $('.timer').attr('data-seconds-left', time)

    $('.timer').startTimer({
        onComplete: function(){
            $('html, body').addClass('bodyTimeoutBackground');
        }
    });

});
