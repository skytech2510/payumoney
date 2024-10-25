<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payments') }}</title>
    @include('merchantPayment.layouts.common.style')
</head>
<body class="bg-body-muted">
    <div class="container-fluid container-layout px-0">
        @yield('content')
    </div>
    @include('merchantPayment.layouts.common.script')
</body>
</html>
