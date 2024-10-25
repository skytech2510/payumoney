<?php

namespace App\Exports;

use App\Models\Transfer;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class TransfersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;

        $transfers = (new Transfer())->getTransfersList($from, $to, $status, $currency, $user)->orderBy('transfers.id', 'desc');

        return $transfers;
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
            __('Receiver'),
            __('Status'),
        ];
    }

    public function map($transfer): array
    {
        return [
            dateFormat($transfer->created_at),
            isset($transfer->sender->first_name) && !empty($transfer->sender->first_name) ? $transfer->sender->first_name . ' ' . $transfer->sender->last_name : "-",
            formatNumber($transfer->amount,  $transfer->currency_id),
            ($transfer->fee == 0) ? '-' : formatNumber($transfer->fee, $transfer->currency_id),
            '-' . formatNumber($transfer->amount + $transfer->fee, $transfer->currency_id),
            isset($transfer->currency->code) ? $transfer->currency->code : '',
            $this->receiver($transfer),
            getStatus($transfer->status)
        ];
    }

    public function receiver($data)
    {
        if (isset($data->receiver->first_name) && !empty($data->receiver->first_name)) {
            return $data->receiver->first_name . ' ' . $data->receiver->last_name;
        } elseif (!empty($data->email)) {
            return $data->email;
        } elseif (!empty($data->phone)) {
            return $data->phone;
        } 

        return '-';
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
