@extends('admin.pdf.app')

@section('title', __('Deposit List pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Amount') }}</td>
                <td>{{ __('Fees') }}</td>
                <td>{{ __('Total') }}</td>
                <td>{{ __('Currency') }}</td>
                <td>{{ __('Payment Method') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($deposits as $deposit)
                <tr class="table-body">
                    <td>{{ dateFormat($deposit->created_at) }}</td>
                    <td>{{ getColumnValue($deposit->user) }}</td>
                    <td>{{ formatNumber($deposit->amount, $deposit->currency_id) }}</td>
                    <td>{{ getFormatFee($deposit) }}</td>
                    <td>{{ '+' . formatNumber($deposit->amount + calculateFee($deposit), $deposit->currency_id) }}</td>
                    <td>{{ optional($deposit->currency)->code }}</td>
                    <td>{{ optional($deposit->payment_method)->id == Mts ? settings('name') : optional($deposit->payment_method)->name }}
                    </td>
                    <td>{{ getStatus($deposit->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
