"use strict";

$(".success").on('click', function(){
  $(".alert-animate").animate({opacity:'1',right: '20px'},200);
});

// Theme change
document.querySelector('#switch').addEventListener('click', function() {
  var wasDarkMode = localStorage.getItem('dark') === '1';
  localStorage.setItem('dark', wasDarkMode ? '0' : '1');
  localStorage.setItem('theme', wasDarkMode ? 'dark' : 'light');
  document.documentElement.classList[wasDarkMode ? 'remove' : 'add']('dark');
});

if (localStorage.getItem('dark') === '1') {
  $('#flexSwitchCheckDefault').prop( "checked", true )
} else {
  $('#flexSwitchCheckDefault').prop( "checked", false )
}

// header profile dropdown
$(document).ready(function () {
  $(".notification-drop").on('click', function (event) {
    event .stopPropagation();
    $(this).find('.open-notify').toggle();
  });
  $(".open-notify").on("click", function (event) {
    event.stopPropagation();
  });
  $(document).on("click", function () {
    $(".open-notify").hide();
  });
});
 
// Side Navbar
var menu_btn = document.querySelector("#menu-btn");
var sidebar = document.querySelector("#sidebar");
var container = document.querySelector(".my-container");

menu_btn.addEventListener("click", () => {
  sidebar.classList.toggle("active-nav");
  container.classList.toggle("active-cont");
});
$('.containt-parent').on('click', function () {
  $('#sidebar').addClass('active-nav');
})

if ($(window).width() < 992) {
  $(document).ready(function () {
    $(".input-btn").on('click', function (event) {
      event.stopPropagation();
      $(".input-search").toggle();
    });

    $(".input-search").on("click", function (event) {
      event.stopPropagation();
    });
    $(document).on("click", function () {
      $(".input-search").hide();
    });
  });
}

// select2
$(document).ready(function () {
  $('.select2').select2();
});

// selcetbox color chang from option select
$('.list-parrent').on('change', function () {
  $(this).closest('.param-ref').find('.select2-container .select2-selection--single').css('background', '#F3F2FF');
})

// dropdown list with filter and images
const selected = document.querySelector(".selected");
const optionsContainer = document.querySelector(".options-container");
const optionsList = document.querySelectorAll(".option");

if (selected !== null) {
  selected.addEventListener("click", () => {
    optionsContainer.classList.toggle("active");
    document.querySelector('#search').value = "";
    document.querySelector('#search').dispatchEvent(new Event('keyup'));
  });

  optionsList.forEach(option => {
    option.addEventListener("click", () => {
      selected.innerHTML = option.querySelector("label").innerHTML;
      optionsContainer.classList.remove("active");
    });
  });
}

if (document.querySelector('.search-list') !== null) {
  var input = document.querySelector('#search');
  var items = document.querySelector('.search-list').getElementsByTagName('li');

  input.addEventListener('keyup', function (ev) {
    var text = ev.target.value;
    var pat = new RegExp(text, 'i');
    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      if (pat.test(item.innerText)) {
        item.classList.remove("hidden");
      } else {
        item.classList.add("hidden");
      }
    }
  });
}

// Bs-5 tooltip
$(document).ready(function () {
  $('[data-bs-toggle=tooltip]').tooltip();
});

// input field border
$('.apply-bg').on('change', function () {
  this.setAttribute('value', this.value);
});

// Choose file interaction
$('#formFileMultiple').on('change', function () {
  if (document.getElementById("formFileMultiple").files.length == 1) {
    $('#formFileMultiple').addClass("input-bg");
  } else {
    $('#formFileMultiple').removeClass("input-bg");
    $('#formFileMultiple').addClass("upload-filed");

  }
});
$('#formFileMultiple2').on('change', function () {
  if (document.getElementById("formFileMultiple").files.length == 1) {
    $('#formFileMultiple2').addClass("input-bg");
  } else {
    $('#formFileMultiple2').removeClass("input-bg");
    $('#formFileMultiple2').addClass("upload-filed");

  }
});

//profile camera button
$(function () {
  $("#upload_link").on('click', function (e) {
    e.preventDefault();
    $("#upload:hidden").trigger('click');
  });
});

function myFunction(e) {
  e.preventDefault();
}
// slight update to account for browsers not supporting e.which

$(document).ready(function () {
  $("#copied-client").on('click', function (event) {
    event.preventDefault();
    $('#copy-parent-div-client').addClass('show-copied');
    setInterval(remove_copy, 5000);
    var copyText = document.getElementById("client-input");
    navigator.clipboard.writeText(copyText.value);
  });
});


// text area focus control
$(document).ready(function () {
  $(".form-control").blur(function () {
    if ($(this).val() == '') {
      $(this).removeClass('not-focus-bg');
      $(this).addClass('focus-bgcolor');
    } else {
      $(this).removeClass('focus-bgcolor');
      $(this).addClass('not-focus-bg');
    }
  });
});

$(document).ready(function () {
  $("#generate-form").on('click', function (event) {
    event.preventDefault();
    $(".generate-section").addClass("di-none");
    $(".merchant-qr-section").removeClass("di-none");
  })
});

//icon rotate
$(".rotate").on('click', function () {
  $(this).toggleClass("down");
})
// password hide and show
$("#eye-icon-show").on('click', function () {
  $(this).addClass('di-none');
  $("#eye-icon-hide").removeClass('d-none');
  $("#eye-icon-hide").addClass('d-block');
  $('#show_hide_password input').attr('type', 'password');
})
$("#eye-icon-hide").on('click', function () {
  $(this).removeClass('di-block');
  $(this).addClass('d-none');
  $("#eye-icon-show").removeClass('d-none');
  $("#eye-icon-show").addClass('di-block');
  $('#show_hide_password input').attr('type', 'text');
})

var langSelect = localStorage.getItem('selected');
if (langSelect) { 
  $(".selectParent .select2").val(langSelect);
}
    
$(".selectParent .select2").on('change',function() {
  localStorage.setItem('selected', $(this).val());
});
// Popoverse
$(document).ready(function(){
  $('[data-toggle="popover"]').popover();   
});

jQuery(function($) {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  $(".show-tooltip").hover(function() {
    $(".tooltip").attr('data-color', $(this).data("color"));
  });
});
