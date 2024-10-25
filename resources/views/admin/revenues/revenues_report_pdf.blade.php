@extends('admin.pdf.app')

@section('title', __('Revenue List pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('Transaction Type') }}</td>
                <td>{{ __('Percentage Charge') }}</td>
                <td>{{ __('Fixed Charge') }}</td>
                <td>{{ __('Total') }}</td>
                <td>{{ __('Currency') }}</td>
            </tr>

            @foreach ($revenues as $revenue)
                <tr class="table-body">
                    <td>{{ dateFormat($revenue->created_at) }}</td>
                    <td>{{ str_replace('_', ' ', optional($revenue->transaction_type)->name) }}</td>
                    <td>{{ $revenue->charge_percentage == 0 ? '-' : formatNumber($revenue->charge_percentage) }}</td>
                    <td>{{ $revenue->charge_fixed == 0 ? '-' : formatNumber($revenue->charge_fixed) }}</td>
                    <td>{{ '+' . getFormatFee($revenue) }}</td>
                    <td>{{ optional($revenue->currency)->code }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
