<?php

namespace App\Exports;

use App\Models\Deposit;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class DepositsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $pm       = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $deposits = (new Deposit())->getDepositsList($from, $to, $status, $currency, $pm, $user)->orderBy('id', 'desc');

        return $deposits;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('User'),
            __('Amount'),
            __('Fees'),
            __('Total'),
            __('Currency'),
            __('Payment Method'),
            __('Status'),
        ];
    }

    public function map($deposit): array
    {
        return [
            dateFormat($deposit->created_at),
            getColumnValue($deposit->user),
            formatNumber($deposit->amount, $deposit->currency_id),
            ($deposit->charge_percentage == 0) && ($deposit->charge_fixed == 0) ? '-' : formatNumber($deposit->charge_percentage + $deposit->charge_fixed, $deposit->currency_id),
            "+" . formatNumber($deposit->amount + ($deposit->charge_percentage + $deposit->charge_fixed), $deposit->currency_id),
            $deposit->currency?->code,
            ($deposit->payment_method?->name == 'Mts' ? settings('name') : $deposit->payment_method?->name),
            getStatus($deposit->status)
        ];
    }

    public function styles($transfer)
    {
        $transfer->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('G:H')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('1')->getFont()->setBold(true);
    }
}
