<?php

namespace App\Exports;

use App\Models\CurrencyExchange;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class CurrencyExchangesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status    = isset(request()->status) ? request()->status : null;
        $currency  = isset(request()->currency) ? request()->currency : null;
        $user      = isset(request()->user_id) ? request()->user_id : null;

        $exchanges = (new CurrencyExchange())->getExchangesListForCsvExport($from, $to, $status, $currency, $user)->orderBy('currency_exchanges.id', 'desc');

        return $exchanges;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('User'),
            __('Amount'),
            __('Fees'),
            __('Total'),
            __('Rate'),
            __('From'),
            __('To'),
            __('Status'),
        ];
    }

    public function map($currencyExchange): array
    {
        // Amount
        if ($currencyExchange->type == 'Out') {
            if ($currencyExchange->amount > 0) {
                $amount = formatNumber($currencyExchange->amount);
            }
        } elseif ($currencyExchange->type == 'In') {
            if ($currencyExchange->amount > 0) {
                $amount = formatNumber($currencyExchange->amount);
            }
        }

        //Total Amount
        if ($currencyExchange->type == 'Out') {
            if (($currencyExchange->fee + $currencyExchange->amount) > 0) {
                $total = '-' . formatNumber($currencyExchange->fee + $currencyExchange->amount);
            } else {
                $total = '-';
            }
        } elseif ($currencyExchange->type == 'In') {
            if (($currencyExchange->fee + $currencyExchange->amount) > 0) {
                $total = '-' . formatNumber($currencyExchange->fee + $currencyExchange->amount);
            } else {
                $total = '-';
            }
        }

        // From Currency Code
        if ($currencyExchange->type == 'Out') {
            $fromCurrencyCode = optional(optional($currencyExchange->fromWallet)->currency)->code;
        } else {
            $fromCurrencyCode = optional(optional($currencyExchange->fromWallet)->currency)->code;
        }

        // To Currency Code
        if ($currencyExchange->type == 'In') {
            $toCurrencyCode = optional(optional($currencyExchange->toWallet)->currency)->code;
        } else {
            $toCurrencyCode = optional(optional($currencyExchange->toWallet)->currency)->code;
        }

        return [
            dateFormat($currencyExchange->created_at),
            getColumnValue($currencyExchange->user),
            $amount,
            ($currencyExchange->fee == 0) ? "-" : formatNumber($currencyExchange->fee),
            $total,
            moneyFormat(optional(optional($currencyExchange->toWallet)->currency)->symbol, (float) ($currencyExchange->exchange_rate)),
            $fromCurrencyCode,
            $toCurrencyCode,
            getStatus($currencyExchange->status)
        ];
    }

    public function styles($currencyExchange)
    {
        $currencyExchange->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('G:H')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('I')->getAlignment()->setHorizontal('center');
        $currencyExchange->getStyle('1')->getFont()->setBold(true);
    }
}
