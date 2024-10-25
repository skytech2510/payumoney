@extends('user.pdf.app')

@section('title', __('Request-Money pdf'))
    
@section('content')
    <!-- Transaction details start -->
    <table class="tabl-width">
        <tbody>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Name') }}</span>
                    <h2 class="text-lg">{{ $transactionDetails->user_type == 'registered' ? getColumnValue($transactionDetails->end_user) : $transactionDetails->email }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction ID') }}</span>
                    <h2 class="text-lg">{{ $transactionDetails->uuid }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Currency') }}</span>
                    <h2 class="text-lg">{{ optional($transactionDetails->currency)->code }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction Date') }}</span>
                    <h2 class="text-lg">{{ dateFormat($transactionDetails->created_at) }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24 align-left">
                    <span class="text-sm">{{ __('Status') }}</span>
                    <h2 class="text-lg {{ getColor($transactionDetails->status) }}">{{ $transactionDetails->status }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Amount') }}</span>
                    <h2 class="text-lg">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->subtotal, $transactionDetails->currency_id)) }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Transaction type') }}</span>
                    <h2 class="text-lg">{{ str_replace('_', ' ', $transactionDetails->transaction_type?->name) }}</h2>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- Transaction details end -->

    <!-- Transaction amount start -->
    <table class="tabl-width">
        <tbody>

            <tr>
                <td class="px-desc">
                    <p class="desc-title">{{ __('Description') }}</p>
                </td>
            </tr>
            <tr>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ __('Sub Total') }}</p>
                </td>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->subtotal, $transactionDetails->currency_id)) }}</p>
                </td>
            </tr>
            <tr>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ __('Fees') }}</p>
                </td>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ getmoneyFormatFee($transactionDetails) }}</p>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="pt-0 pb-10 pl-80 pr-120">
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ __('Total') }}</p>
                </td>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->total, $transactionDetails->currency_id)) }}</p>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- Transaction amount end -->

    <!-- Transaction info start -->
    <table class="tabl-width">
        <tbody>
            <tr>
                <td class="px-info">
                    <p class="text-md">{{ __('Note') }}</p>
                </td>
            </tr>
            <tr>
                <td class="px-info-text">
                    <p class="text-sm">{{ $transactionDetails->note }}</p>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- Transaction info start -->

@endsection