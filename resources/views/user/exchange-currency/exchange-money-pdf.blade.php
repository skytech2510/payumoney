@extends('user.pdf.app')

@section('title', __('Exchange pdf'))
    
@section('content')
    <!-- Transaction details start -->
    <table class="tabl-width">
        <tbody>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Transaction ID') }}</span>
                    <h2 class="text-lg">{{ $currencyExchange->uuid }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction Date') }}</span>
                    <h2 class="text-lg">{{ dateFormat($currencyExchange->created_at) }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Exchange From') }}</span>
                    <h2 class="text-lg">{{ optional(optional($currencyExchange->fromWallet)->currency)->code }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Exchange To') }}</span>
                    <h2 class="text-lg">{{ optional(optional($currencyExchange->toWallet)->currency)->code }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Status') }}</span>
                    <h2 class="text-lg {{ getColor($currencyExchange->status) }}">{{ $currencyExchange->status }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Transaction Type') }}</span>
                    <h2 class="text-lg">{{ ($currencyExchange->type == "Out" ? __('Exchange To') : __('Exchange From')) }}</h2>
                </td>
            </tr>
            <tr>
                <td class="px-30 pb-24">
                    <span class="text-sm">{{ __('Exchange Amount') }}</span>
                    <h2 class="text-lg">{{ moneyFormat(optional(optional($currencyExchange->fromWallet)->currency)->symbol, formatNumber($currencyExchange->amount)) }}</h2>
                </td>
                <td class="px-30 pb-24 align-rigt">
                    <span class="text-sm">{{ __('Exchange Rate') }}</span>
                    <h2 class="text-lg">1 {{ optional(optional($currencyExchange->fromWallet)->currency)->code }} = {{ (float)($currencyExchange->exchange_rate) }} {{ optional(optional($currencyExchange->toWallet)->currency)->code }}</h2>
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
                    <p class="text-md">{{ moneyFormat(optional(optional($currencyExchange->fromWallet)->currency)->symbol, formatNumber($currencyExchange->amount)) }}</p>
                </td>
            </tr>
            <tr>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ __('Fees') }}</p>
                </td>
                <td class="pb-10 pl-30 align-center">
                    <p class="text-md">{{ moneyFormat(optional(optional($currencyExchange->fromWallet)->currency)->symbol, formatNumber($currencyExchange->fee)) }}</p>
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
                    <p class="text-md">{{ moneyFormat(optional(optional($currencyExchange->fromWallet)->currency)->symbol, formatNumber($currencyExchange->amount + $currencyExchange->fee)) }}</p>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- Transaction amount end -->
@endsection