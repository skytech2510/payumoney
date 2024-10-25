<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>{{ __('Crypto Received Transactions') }}</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumiIo/Resources/assets/admin/css/crypto_received_transactions_report_pdf.min.css') }}">
    </head>

    <body>
        <div class="section-width">
            <div class="section-height">
                <div class="header-section">
                    <div>
                        <strong>
                            {{ ucwords(settings('name')) }}
                        </strong>
                    </div>
                    <br>
                    <div>
                        {{ __('Period') }} : {{ $date_range }}
                    </div>
                    <br>
                    <div>
                        {{ __('Print Date') }} : {{ dateFormat(now())}}
                    </div>
                </div>
                <div class="logo-section">
                    <div>
                        <div>
                            {!! getSystemLogo() !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="clear-both">
            </div>
            <div class="mt-30">
                <table class="table-section">
                    <tr class="table-header-text">
                        <td>{{ __('Date') }}</td>
                        <td>{{ __('Sender') }}</td>
                        <td>{{ __('Amount') }}</td>
                        <td>{{ __('Crypto Currency') }}</td>
                        <td>{{ __('Receiver') }}</td>
                    </tr>
                    @foreach($getCryptoReceivedTransactions as $transaction)
                        <tr class="table-row-text">
                            <td>{{ dateFormat($transaction->created_at) }}</td>
                            <!-- Sender -->
                            <td>{{ getColumnValue($transaction->end_user) }}</td>
                            <td>{{ '+' . $transaction->subtotal }}</td>
                            <td>{{ optional($transaction->currency)->code }}</td>
                            <!-- Receiver -->
                            <td>{{ getColumnValue($transaction->end_user) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </body>
</html>
