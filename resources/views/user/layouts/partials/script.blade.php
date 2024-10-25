<script src="{{ asset('public/dist/libraries/jquery-3.6.1/jquery-3.6.1.min.js') }}" ></script>
<script src="{{ asset('public/dist/libraries/bootstrap-5.0.2/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/js/select2.min.js') }}"></script>
<script src="{{ asset('public/user/templates/js/chart.umd.min.js') }}"></script>
<script src="{{ asset('public/user/templates/js/main.min.js') }}"></script>
<script src="{{ asset('public/user/customs/js/common.min.js') }}"></script>

<script type="text/javascript">
    var SITE_URL = "{{ url('/') }}";
    var FIATDP = "{{ number_format(0, preference('decimal_format_amount', 2)) }}";
    var CRYPTODP = "{{ number_format(0, preference('decimal_format_amount_crypto', 8)) }}";

	$(document).ready(function() {
		$("#select_language").on("change", function() {
			if($("#select_language select").val() == 'ar'){
				localStorage.setItem('lang', 'ar');
				let lang = $("#select_language select").val();

				$.ajax({
					type: 'get',
					url: '{{ url('change-lang') }}',
					data: {lang: lang},
					success: function (msg) {
						if (msg == 1) {
							location.reload();
							$("html").attr("dir", "rtl");
						}
					}
				});

			} else {
				let lang = $("#select_language select").val();
				$.ajax({
					type: 'get',
					url: '{{url('change-lang')}}',
					data: {lang: lang},
					success: function (msg) {
						if (msg == 1) {
							location.reload()
							localStorage.setItem('lang', lang);
							$("html").removeAttr("dir", "rtl");
						}
					}
				});
			}
		});
	});  

</script>

@stack('js')