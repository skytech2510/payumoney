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

class IdentityProofsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from   = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to     = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;

        $identityProofs = (new DocumentVerification())->getDocumentVerificationsList($from, $to, $status)->orderBy('id', 'desc');

        return $identityProofs;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('User'),
            __('Identity Type'),
            __('Identity Number'),
            __('Status'),
        ];
    }

    public function map($identityProof): array
    {
        return [
            dateFormat($identityProof->created_at),
            (isset($identityProof->user->first_name) && !empty($identityProof->user->first_name)) ? $identityProof->user->first_name . ' ' . $identityProof->user->last_name : "-",
            str_replace('_', ' ', ucfirst($identityProof->identity_type)),
            $identityProof->identity_number,
            getStatus($identityProof->status),
        ];
    }

    public function styles($transfer)
    {
        $transfer->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('E')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('1')->getFont()->setBold(true);
    }
}
