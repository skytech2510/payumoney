<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('resources/views/admin/pdf/style.min.css') }}">
</head>
    <body>
        <div class="width-100">
            <div class="height-80">
                <div class="heading-section">
                    <div>
                        <strong>{{ ucwords(settings('name')) }}</strong>
                    </div>
                    <br>
                    <div>{{ __('Period') }} : {{ $date_range }}</div>
                    <br>
                    <div>{{ __('Print Date') }} : {{ dateFormat(now()) }}</div>
                </div>
                <div class="logo-img">
                    <div>
                        <img src="{{ image(settings('logo'), 'logo') }}" alt="logo">
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            @yield('content')
        </div>
    </body>
</html>
