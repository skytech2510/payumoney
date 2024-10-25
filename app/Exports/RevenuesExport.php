<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class RevenuesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $type     = isset(request()->type) ? request()->type : null;
        $currency = isset(request()->currency) ? request()->currency : null;

        $revenues = (new Transaction())->getRevenuesList($from, $to, $currency, $type)->orderBy('transactions.id', 'desc');

        return $revenues;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('Transaction Type'),
            __('Percentage Charge'),
            __('Fixed Charge'),
            __('Total'),
            __('Currency'),
        ];
    }

    public function map($revenue): array
    {
        return [
            dateFormat($revenue->created_at),
            isset($revenue->transaction_type->name) ? str_replace('_', ' ', $revenue->transaction_type->name) : '',
            ($revenue->charge_percentage == 0) ? '-' : formatNumber($revenue->charge_percentage, $revenue->currency_id),
            ($revenue->charge_fixed == 0) ? '-' : formatNumber($revenue->charge_fixed, $revenue->currency_id),
            ($revenue->charge_percentage == 0) && ($revenue->charge_fixed == 0) ? '-' : '+' . formatNumber($revenue->charge_percentage + $revenue->charge_fixed, $revenue->currency_id),
            isset($revenue->currency->code) ? $revenue->currency->code : '',
        ];
    }

    public function styles($revenue)
    {
        $revenue->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $revenue->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $revenue->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $revenue->getStyle('1')->getFont()->setBold(true);
    }
}
