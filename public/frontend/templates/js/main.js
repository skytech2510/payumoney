"use strict";

$("#switch").on('click', function () {
    if ($("body").hasClass("dark")) {
        $("body").removeClass("dark");
        $(this).find('img').toggle();
        $("#switch").removeClass("switched");
        localStorage.setItem('theme', 'dark');
        localStorage.setItem('dark', '0');
    }
    else {
        $("body").addClass("dark");
        $(this).find('img').toggle();
        $("#switch").addClass("switched");
        localStorage.setItem('theme', 'light');
        localStorage.setItem('dark', '1');
    }
});

$(document).ready(function () {
  $('.select2').select2();

  // User Select
  $(".usertype-module").on('click', function(){
    $(".usertype-module").removeClass("user-selected");
    $(this).addClass("user-selected");
  });

  // Gateway Select
  $(".gateway").on('click', function(){
    $(".gateway").removeClass("gateway-selected");
    $(this).addClass("gateway-selected");
  });

  // Privacy Page
  $(".privacy-nav").on('click', function () {
    $(".privacy-nav").removeClass("privacy-nav-active");
    $(this).addClass("privacy-nav-active");
  });

  // Input focus color
  $(".form-control").on('blur',function () {
    if ($(this).val() == '') {
      $(this).removeClass('not-focus-bg');
      $(this).addClass('focus-bgcolor');
    } else {
      $(this).removeClass('focus-bgcolor');
      $(this).addClass('not-focus-bg');
    }
  });

  $(window).scroll(function(){
    var scroll = $(window).scrollTop();
    if (scroll > 95) {
      $(".bg-white").addClass("shadow-sm");
    }
    else{
        $(".start-header").removeClass("shadow-sm");
    }
  })

});

// selcetbox color chang from option select
$('.list-parrent').on('change',function () {
  $(this).closest('.select2-extends').find('.select2-container .select2-selection--single').css('background', '#F3F2FF');
})

$("#eye-icon-show").on('click',function(){
  $(this).removeClass('di-block');
  $(this).addClass('d-none');
  $("#eye-icon-hide").removeClass('d-none');
  $("#eye-icon-hide").addClass('d-block');
  $('#password-div input').attr('type', 'text');
  $('#show_hide_password input').attr('type', 'text');
  $('#confirm-password-div input').attr('type', 'text');
})
$("#eye-icon-hide").on('click',function(){
  $(this).addClass('d-none');
  $("#eye-icon-show").removeClass('d-none');
  $("#eye-icon-show").addClass('d-block');
  $('#password-div input').attr('type', 'password');
  $('#show_hide_password input').attr('type', 'password');
  $('#confirm-password-div input').attr('type', 'password');
})


var theme = localStorage.getItem('theme');

if(theme == 'dark') {
    $("body").removeClass("dark");
    $("img.moon").removeClass("img-none")
    $("img.sun").addClass("img-none")
    $("#switch").removeClass("switched");
} else {
    $("body").addClass("dark");
    $("img.sun").removeClass("img-none")
    $("img.moon").addClass("img-none")
    $("#switch").addClass("switched");
}

(function () {
    var carousels = function () {
      $(".owl-carousel1").owlCarousel({
        loop: true,
        center: true,
        margin: 0,
        responsiveClass: true,
        nav: false,
        responsive: {
          0: {
            items: 1,
            nav: false
          },
          680: {
            items: 2,
            nav: true,
            loop: true
          },
          991: {
            items: 3,
            nav: true
          }
        }
      });
    };

    (function ($) {
      carousels();
    })(jQuery);
  })();