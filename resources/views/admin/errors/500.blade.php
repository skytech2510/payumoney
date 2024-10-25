<!DOCTYPE html>
<html>
    <head>
        <title>{{ __('Internal Server Error') }}</title>

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: red;
                display: table;
                font-weight: 100;
                font-family: 'Comin Sans', sans-serif;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 40px;
                margin-bottom: 40px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">
                    <h4>
                        <p>{{ __('The server encountered an internal error or misconfiguration.') }} </p>
                        <p>{{ __('Please try again later.') }}</p>
                    </h4>
                </div>
            </div>
        </div>
    </body>
</html>
