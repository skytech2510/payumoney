<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
           {{ __('User Qr Code') }}
        </title>
        <style type="text/css">
        	body{ font-family: 'Lato', sans-serif; color:#121212; text-align: center;}
			hr { border-top:1px solid #f0f0f0;}
        </style>
    </head>

    <body>
        <img src="{{ image($qrCode?->qr_image, 'user_qrcode') }}"/>
        <h2 style="margin-top: 20px;">{{ __('Scan QR Code To Pay') }}</h2>
    </body>
</html>
