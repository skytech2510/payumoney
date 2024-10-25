<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            {{ __('Crypto Sent Transactions') }}
        </title>
        <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumIo/Resources/assets/admin/css/crypto_sent_transactions_report_pdf.min.css') }}">
    </head>

    <body>
        <div class="section-width">
            <div class="section-height">
                <div class="section-style">
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
                       {{ __(' Print Date') }} : {{ dateFormat(now())}}
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
                    <tr class="table-row-header">
                        <td>{{ __('Date') }}</td>
                        <td>{{ __('Sender') }}</td>
                        <td>{{ __('Amount') }}</td>
                        <td>{{ __('Fees') }}</td>
                        <td>{{ __('Total') }}</td>
                        <td>{{ __('Crypto Currency') }}</td>
                        <td>{{ __('Receiver') }}</td>
                        <td>{{ __('Status') }}</td>
                    </tr>

                    @foreach($getCryptoSentTransactions as $transaction)
                        <tr class="table-row-text">

                            <td>{{ dateFormat($transaction->created_at) }}</td>
                            <!-- User -->
                            <td>{{ getColumnValue($transaction->user) }}</td>

                            <td>{{ formatNumber($transaction->subtotal, $transaction->currency_id) }}</td>

                            <td>{{ formatNumber($transaction->charge_fixed, $transaction->currency_id) }}</td>

                            @if ($transaction->total > 0)
                                <td>{{ '+'.$transaction->total }}</td>
                            @else
                                <td>
                                    {{ formatNumber($transaction->total - (json_decode($transaction->cryptoAssetApiLog->payload)->network_fee), $transaction->currency_id) }}
                                </td>
                            @endif

                            <td>{{ optional($transaction->currency)->code }}</td>

                            <!-- Receiver -->
                            <td>{{ getColumnValue($transaction->end_user) }}</td>

                            <td>{{ $transaction->status }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </body>
</html>
