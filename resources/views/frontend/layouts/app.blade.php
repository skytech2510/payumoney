<!DOCTYPE html>
<html lang="en" class="scrol-pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ isset($exceptionMeta) ? $exceptionMeta->description : meta(Route::current()->uri(), 'description') }}">
    <meta name="keywords" content="{{ isset($exceptionMeta) ? $exceptionMeta->keywords : meta(Route::current()->uri(), 'keywords') }}">
    <title>{{ isset($exceptionMeta) ? $exceptionMeta->title : meta(Route::current()->uri(), 'title') }}<?= isset($additionalTitle) ? ' | '.$additionalTitle : '' ?></title>
    @include('frontend.layouts.common.style')

    <script type="text/javascript">
        var SITE_URL = "{{ url('/') }}";
    </script>

</head>
<body>
	
     <!-- Start scroll-top button -->
    <div id="scroll-top-area">
        <a href="{{url()->current()}}#top-header"><i class="fas fa-arrow-up"></i></a>
    </div>
    <!-- End scroll-top button -->
    
    <!-- Start Header -->
    @include('frontend.layouts.common.header')
    <!-- End Header -->

    @yield('content')

    <!-- Start Footer-->
    @include('frontend.layouts.common.footer_menu')
    <!-- End Footer -->

    @include('frontend.layouts.common.script')

    @yield('js')
</body>
</html>