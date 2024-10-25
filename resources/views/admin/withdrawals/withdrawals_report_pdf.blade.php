@extends('admin.pdf.app')

@section('title', __('Withdrawal List pdf'))

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
                <td>{{ __('Method Info') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($withdrawals as $withdrawal)
                <tr class="table-body">
                    <td>{{ dateFormat($withdrawal->created_at) }}</td>
                    <td>{{ getColumnValue($withdrawal->user) }}</td>
                    <td>{{ formatNumber($withdrawal->amount) }}</td>
                    <td>{{ getFormatFee($withdrawal) }}</td>
                    <td>{{ '-' . formatNumber($withdrawal->amount + calculateFee($withdrawal)) }}</td>
                    <td>{{ optional($withdrawal->currency)->code }}</td>
                    <td>{{ optional($withdrawal->payment_method)->id == Mts ? settings('name') : optional($withdrawal->payment_method)->name }}</td>
                    <td>{{ getPaymentMethodInfo($withdrawal) }}</td>
                    <td>{{ getStatus($withdrawal->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
