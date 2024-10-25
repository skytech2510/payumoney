@extends('admin.pdf.app')

@section('title', __('Request Payment pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Requested Amount') }}</td>
                <td>{{ __('Accepted Amount') }}</td>
                <td>{{ __('Currency') }}</td>
                <td>{{ __('Receiver') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($requestpayments as $requestpayment)
                <tr class="table-body">
                    <td>{{ dateFormat($requestpayment->created_at) }}</td>
                    <td>{{ getColumnValue($requestpayment->user) }}</td>
                    <td>{{ '+' . formatNumber($requestpayment->amount, $requestpayment->currency_id) }}</td>
                    <td>{{ $requestpayment->accept_amount == 0 ? '-' : '+' . formatNumber($requestpayment->accept_amount, $requestpayment->currency_id) }}</td>
                    <td>{{ optional($requestpayment->currency)->code }}</td>
                    <td>{{ $requestpayment->receiver ? getColumnValue($requestpayment->receiver) : ($requestpayment->email ? $requestpayment->email : ($requestpayment->phone ? $requestpayment->phone : '-')) }}</td>
                    <td>{{ getStatus($requestpayment->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
