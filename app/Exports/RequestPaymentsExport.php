<?php

namespace App\Exports;

use App\Models\RequestPayment;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class RequestPaymentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;

        $requestpayments = (new RequestPayment())->getRequestPaymentsList($from, $to, $status, $currency, $user)->orderBy('request_payments.id', 'desc');

        return $requestpayments;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('User'),
            __('Requested Amount'),
            __('Accepted Amount'),
            __('Currency'),
            __('Receiver'),
            __('Status'),
        ];
    }

    public function map($requestPayment): array
    {
        return [
            dateFormat($requestPayment->created_at),
            isset($requestPayment->user) ? $requestPayment->user->first_name . ' ' . $requestPayment->user->last_name : "-",
            '+' . formatNumber($requestPayment->amount, $requestPayment->currency_id),
            ($requestPayment->accept_amount == 0) ? "-" : '+' . formatNumber($requestPayment->accept_amount, $requestPayment->currency_id),
            isset($requestPayment->currency->code) ? $requestPayment->currency->code : '',
            $this->receiver($requestPayment),
            getStatus($requestPayment->status)
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
        $transfer->getStyle('G')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('1')->getFont()->setBold(true);
    }
}
