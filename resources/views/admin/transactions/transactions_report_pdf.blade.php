@extends('admin.pdf.app')

@section('title', __('Transaction List pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Type') }}</td>
                <td>{{ __('Amount') }}</td>
                <td>{{ __('Fees') }}</td>
                <td>{{ __('Total') }}</td>
                <td>{{ __('Currency') }}</td>
                <td>{{ __('Receiver') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($transactions as $transaction)
                <tr class="table-body">

                    <td>{{ dateFormat($transaction->created_at) }}</td>
                    <!-- User -->
                    @if (in_array($transaction->transaction_type_id, getPaymoneySettings('transaction_types')['web']['sent']))
                        <td>
                            @if (isset($transaction->user))
                                {{ getColumnValue($transaction->user) }}
                            @elseif (module('CryptoExchange') && isset($transaction->crypto_exchange) && !empty($transaction->crypto_exchange))
                                {{ optional($transaction->crypto_exchange)->email_phone }}
                            @else
                                {{ __('-') }}
                            @endif
                        </td>
                    @elseif (in_array($transaction->transaction_type_id, getPaymoneySettings('transaction_types')['web']['received']))
                        <td>{{ getColumnValue($transaction->end_user) }}</td>
                    @endif

                    <td>{{ str_replace('_', ' ', optional($transaction->transaction_type)->name) }}</td>

                    <!-- Amount -->
                    <td>{{ formatNumber($transaction->subtotal, $transaction->currency_id) }}</td>

                    <td>{{ getFormatFee($transaction) }}</td>

                    <!-- Total -->
                    <td>{{ $transaction->total > 0 ? '+' . formatNumber($transaction->total, $transaction->currency_id) : formatNumber($transaction->total, $transaction->currency_id) }}</td>

                    <td>{{ optional($transaction->currency)->code }}</td>

                    <!-- Receiver -->

                    @switch($transaction->transaction_type_id)
                        @case(Deposit)
                        @case(Exchange_From)
                        @case(Exchange_To)
                        @case(module('CryptoExchange') ? Crypto_Buy : false):
                        @case(module('CryptoExchange') ? Crypto_Sell : false):
                        @case(module('CryptoExchange') ? Crypto_Swap : false):
                        @case(Withdrawal)
                        @case(Payment_Sent)
                            <td>{{ getColumnValue($transaction->end_user) }}</td>
                        @break

                        @case(Transferred)
                        @case(Received)
                            <td>
                                @if (optional($transaction->transfer)->receiver)
                                    {{ getColumnValue(optional($transaction->transfer)->receiver) }}
                                @elseif (optional($transaction->transfer)->email)
                                    {{ optional($transaction->transfer)->email }}
                                @elseif (optional($transaction->transfer)->phone)
                                    {{ optional($transaction->transfer)->phone }}
                                @else
                                    {{ '-' }}
                                @endif
                            </td>
                        @break

                        @case(Request_Sent)
                        @case(Request_Received)
                            <td>{{ isset($transaction->request_payment?->receiver) ? getColumnValue(optional($transaction->request_payment)->receiver) : optional($transaction->request_payment)->email }}</td>
                        @break
                        
                        @case(Payment_Received)
                            <td>{{ getColumnValue($transaction->user) }}</td>
                        @break

                        @default 
                            <td> {{ getTransactionListUser($transaction, 'receiver', false) ?? '-'}} </td>
                    @endswitch

                    <td>{{ getStatus($transaction->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
