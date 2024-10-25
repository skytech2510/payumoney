@extends('admin.pdf.app')

@section('title', __('Merchant Payment pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('Merchant') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Amount') }}</td>
                <td>{{ __('Fees') }}</td>
                <td>{{ __('Total') }}</td>
                <td>{{ __('Currency') }}</td>
                <td>{{ __('Payment Method') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($merchant_payments as $merchant_payment)
                <tr class="table-body">
                    <td>{{ dateFormat($merchant_payment->created_at) }}</td>
                    <td>{{ getColumnValue(optional($merchant_payment->merchant)->user) }}</td>
                    <td>{{ getColumnValue($merchant_payment->user) }}</td>
                    <td>{{ formatNumber($merchant_payment->amount) }}</td>
                    <td>{{ getFormatFee($merchant_payment) }}</td>
                    <td>{{ '+' . formatNumber($merchant_payment->amount + calculateFee($merchant_payment)) }}</td>
                    <td>{{ optional($merchant_payment->currency)->code }}</td>
                    <td>{{ optional($merchant_payment->payment_method)->name == 'Mts' ? settings('name') : optional($merchant_payment->payment_method)->name }}</td>
                    <td>{{ getStatus($merchant_payment->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
