@extends('admin.pdf.app')

@section('title', __('Send Money pdf'))

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
                <td>{{ __('Receiver') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($transfers as $transfer)
                <tr class="table-body">
                    <td>{{ dateFormat($transfer->created_at) }}</td>
                    <td>{{ getColumnValue($transfer->sender) }}</td>
                    <td>{{ formatNumber($transfer->amount, $transfer->currency_id) }}</td>
                    <td>{{ $transfer->fee == 0 ? '-' : formatNumber($transfer->fee, $transfer->currency_id) }}</td>
                    <td>{{ '-' . formatNumber($transfer->amount + $transfer->fee, $transfer->currency_id) }}</td>
                    <td>{{ optional($transfer->currency)->code }}</td>
                    <td>{{ $transfer->receiver ? getColumnValue($transfer->receiver) : ($transfer->email ? $transfer->email : ($transfer->phone ? $transfer->phone : '-')) }}</td>
                    <td>{{ getStatus($transfer->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
