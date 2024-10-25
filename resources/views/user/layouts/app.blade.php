<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Techvillage">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ !empty(meta(Route::current()->uri(), 'title')) ? meta(Route::current()->uri(), 'title') . ' | ' : '' }} {{ settings('name') }}</title>

    <!-- css -->
    @include('user.layouts.partials.style')
    <!-- end css -->
    
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ faviconPath() }}">

    <script>
      'use strict';
      var SITE_URL = "{{ url('/') }}";
      var FIATDP = "{{ number_format(0, preference('decimal_format_amount', 2)) }}";
      var CRYPTODP = "{{ number_format(0, preference('decimal_format_amount_crypto', 8)) }}";

      if (localStorage.getItem('dark') === '1') {
        document.documentElement.classList.add('dark');
      }

      if (localStorage.getItem('lang') == 'ar') {
        document.getElementsByTagName("html")[0].setAttribute("dir", "rtl");
        document.querySelector("html").setAttribute("dir", "rtl");
      } else {
        document.querySelector("html").removeAttribute("dir", "rtl");
      }
    </script>
  </head>
  <body>

    <!-- sidebar section -->
    @include('user.layouts.partials.sidebar')
    <!-- end sidebar section -->

    <div class="my-container active-cont bg-white-50">

        <!-- header section -->
        @include('user.layouts.partials.header')
        <!-- end header section -->

      <div class="position-relative">
        <div class="containt-parent">
          <div class="main-containt">
            
            <!-- main-containt -->
            @yield('content')
            <!-- main-containt -->

          </div>
        </div>
       </div>

       <!-- footer -->
       @include('user.layouts.partials.footer')
       <!-- end footer -->

    </div>

    <!-- js -->
    @include('user.layouts.partials.script')
    <!-- end js -->

  </body>
</html>