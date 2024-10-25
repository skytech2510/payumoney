<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('resources/views/user/pdf/style.min.css') }}">
</head>

<body class="main-class w-100">
    <table class="w-100" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td class="px-40">

                <!-- System Logo -->
                <table class="tabl-width">
                    <tbody>
                        <tr>
                            <td class="logo-container">
                                <a href="{{ url('/') }}">
                                    <img src="{{ image(settings('logo'), 'logo') }}" alt="logo" border="0">
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- System Logo -->
                
                @yield('content')
                
            </td>
        </tr>
    </table>
</body>
</html>
