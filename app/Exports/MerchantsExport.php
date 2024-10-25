<?php

namespace App\Exports;

use App\Models\Merchant;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class MerchantsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from   = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to     = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $user   = isset(request()->user_id) ? request()->user_id : null;

        $merchants = (new Merchant())->getMerchantsList($from, $to, $status, $user)->orderBy('merchants.id', 'desc');

        return $merchants;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('ID'),
            __('Type'),
            __('Business Name'),
            __('User'),
            __('Url'),
            __('Group'),
            __('Logo'),
            __('Status'),
        ];
    }

    public function map($merchant): array
    {
        return [
            dateFormat($merchant->created_at),
            (isset($merchant->merchant_uuid)) ? $merchant->merchant_uuid : '-',
            ucfirst($merchant->type),
            $merchant->business_name,
            getColumnValue($merchant->user),
            $merchant->site_url,
            getColumnValue($merchant->merchant_group, 'name'),
            isset($merchant->logo) ? $merchant->logo : "-",
            getStatus($merchant->status)
        ];
    }

    public function styles($transfer)
    {
        $transfer->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('G:H')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('I')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('1')->getFont()->setBold(true);
    }
}
