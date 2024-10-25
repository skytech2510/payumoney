@extends('user.pdf.app')

@section('title', __('Deposit pdf'))
    
@section('content')
    <!-- Transaction details start -->
    <table class="tabl-width">
        <tbody>
            <tr>
                <td class="px-30">
                    <span class="text-sm">{{ __('Name') }}</span>
                    <h2 class="text-lg">{{ getColumnValue($transactionDetails->user) }}</h2>
                </td>
                <td class="px-30 align-rigt">
                    <span class="text-sm">{{ __('Transaction ID') }}</span>
                    <h2 class="text-lg">{{ $transactionDetails->uuid }}</h2>
                </td>
            </tr>
            <tr>
                <td class="py-24">
                    <span class="text-sm">{{ __('Currency') }}</span>
                    <h2 class="text-lg">{{ getColumnValue($transactionDetails->currency, 'code', '') }}</h2>
                </td>
                <td class="py-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction Date') }}</span>
                    <h2 class="text-lg">{{ dateFormat($transactionDetails->created_at) }}</h2>
                </td>
            </tr>
            <tr>
                <td class="pxy-36 align-left">
                    <span class="text-sm">{{ __('Status') }}</span>
                    <h2 class="text-lg {{ getColor($transactionDetails->status) }}">{{ $transactionDetails->status }}</h2>
                </td>
                <td class="pxy-36 align-rigt">
                    <span class="text-sm">{{ __('Deposited Amount') }}</span>
                    <h2 class="text-lg">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->subtotal, $transactionDetails->currency_id)) }}</h2>
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
                <td class="pt-10 align-center">
                    <p class="text-md">{{ __('Sub Total') }}</p>
                </td>
                <td class="pt-10 align-center">
                    <p class="text-md">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->subtotal, $transactionDetails->currency_id)) }}</p>
                </td>
            </tr>
            <tr>
                <td class="pb-5 align-center">
                    <p class="text-md">{{ __('Fees') }}</p>
                </td>
                <td class="pb-5 align-center">
                    <p class="text-md">{{ getmoneyFormatFee($transactionDetails) }}</p>
                </td>
            </tr>
            <tr>
                <td class="pb-right-100">
                    <hr>
                </td>
                <td class="pb-left-100">
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="pb-10 align-center">
                    <p class="text-md">{{ __('Total') }}</p>
                </td>
                <td class="pb-10 align-center">
                    <p class="text-md">{{ moneyFormat(optional($transactionDetails->currency)->symbol, formatNumber($transactionDetails->total, $transactionDetails->currency_id)) }}</p>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- Transaction amount end -->
@endsection