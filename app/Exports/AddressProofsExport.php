<?php

namespace App\Exports;

use App\Models\DocumentVerification;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class AddressProofsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;

        $addressProofs = (new DocumentVerification())->getAddressVerificationsList($from, $to, $status)->orderBy('id', 'desc');

        return $addressProofs;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('User'),
            __('Status'),
        ];
    }

    public function map($addressProof): array
    {
        return [
            dateFormat($addressProof->created_at),
            getColumnValue($addressProof->user),
            getStatus($addressProof->status),
        ];
    }

    public function styles($transfer)
    {
        $transfer->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('C')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('1')->getFont()->setBold(true);
    }
}
