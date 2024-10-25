<script src="{{ asset('public/dist/libraries/jquery-3.6.1/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/frontend/templates/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('public/dist/libraries/bootstrap-5.0.2/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('public/frontend/templates/js/main.min.js') }}"></script>
<script src="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/js/select2.min.js') }}"></script>
<script>
	$(".custom-select").select2()
</script>

<!--Google Analytics Tracking Code-->
{!! settings('head_code') !!}

<script type="text/javascript">

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})

	function resizeHeaderOnScroll() {
		const distanceY = window.pageYOffset || document.documentElement.scrollTop,
		shrinkOn = 100,
		headerEl = document.getElementById('js-header');

		if (headerEl) {
			if (distanceY > shrinkOn) {
				headerEl.classList.add("smaller-header");
				$("#logo_container").attr('src', SITE_URL + '/public/frontend/images/logo_sm.png');
			} else {
				headerEl.classList.remove("smaller-header");
				$("#logo_container").attr('src', SITE_URL + '/public/frontend/images/logo.png');
			}
		}
	}
	window.addEventListener('scroll', resizeHeaderOnScroll);

	//Language script
	$('#lang').on('change', function (e)
	{
		e.preventDefault();
		lang = $(this).val();
		url = '{{url('change-lang')}}';

		$.ajax({
			type: 'get',
			url: url,
			data: {lang: lang},
			success: function (msg)
			{
				if (msg == 1) {
					if (lang == 'ar') {
						localStorage.setItem('lang', 'ar');
						localStorage.setItem('selected', 'ar');
					} else {
						localStorage.setItem('lang', lang)
						localStorage.setItem('selected', lang)
					}
					location.reload();
				}
			}
		});
	});

// custom dropdown

function create_custom_dropdowns() {
$('.select-custom').each(function(i, select) {
	if (!$(this).next().hasClass('dropdown')) {
	$(this).after('<div class="dropdown ' + ($(this).attr('class') || '') + '" tabindex="0"><span class="current"></span><div class="list"><ul></ul></div></div>');
	var dropdown = $(this).next();
	var options = $(select).find('option');
	var selected = $(this).find('option:selected');
	dropdown.find('.current').html(selected.data('display-text') || selected.text());
	options.each(function(j, o) {
		var display = $(o).data('display-text') || '';
		dropdown.find('ul').append('<li class="option ' + ($(o).is(':selected') ? 'selected' : '') + '" data-value="' + $(o).val() + '" data-display-text="' + display + '">' + $(o).text() + '</li>');
	});
	}
});
}

// Event listeners

// Open/close
$(document).on('click', '.dropdown', function(event) {
$('.dropdown').not($(this)).removeClass('open');
$(this).toggleClass('open');
if ($(this).hasClass('open')) {
	$(this).find('.option').attr('tabindex', 0);
	$(this).find('.selected').focus();
} else {
	$(this).find('.option').removeAttr('tabindex');
	$(this).focus();
}
});
// Close when clicking outside
$(document).on('click', function(event) {
if ($(event.target).closest('.dropdown').length === 0) {
	$('.dropdown').removeClass('open');
	$('.dropdown .option').removeAttr('tabindex');
}
event.stopPropagation();
});
// Option click
$(document).on('click', '.dropdown .option', function(event) {
$(this).closest('.list').find('.selected').removeClass('selected');
$(this).addClass('selected');
var text = $(this).data('display-text') || $(this).text();
$(this).closest('.dropdown').find('.current').text(text);
$(this).closest('.dropdown').prev('select').val($(this).data('value')).trigger('change');
});

// Keyboard events
$(document).on('keydown', '.dropdown', function(event) {
var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
// Space or Enter
if (event.keyCode == 32 || event.keyCode == 13) {
	if ($(this).hasClass('open')) {
	focused_option.trigger('click');
	} else {
	$(this).trigger('click');
	}
	return false;
	// Down
} else if (event.keyCode == 40) {
	if (!$(this).hasClass('open')) {
	$(this).trigger('click');
	} else {
	focused_option.next().focus();
	}
	return false;
	// Up
} else if (event.keyCode == 38) {
	if (!$(this).hasClass('open')) {
	$(this).trigger('click');
	} else {
	var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
	focused_option.prev().focus();
	}
	return false;
// Esc
} else if (event.keyCode == 27) {
	if ($(this).hasClass('open')) {
	$(this).trigger('click');
	}
	return false;
}
});

$(document).ready(function() {
create_custom_dropdowns();
});

</script>
