<?php

namespace App\Exports;

use App\Models\MerchantPayment;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class MerchantPaymentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $pm       = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;

        $merchantPayments = (new MerchantPayment())->getMerchantPaymentsList($from, $to, $status, $currency, $pm)->orderBy('merchant_payments.id', 'desc');

        return $merchantPayments;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('Merchant'),
            __('User'),
            __('Amount'),
            __('Fees'),
            __('Total'),
            __('Currency'),
            __('Payment Method'),
            __('Status'),
        ];
    }

    public function map($merchantPayment): array
    {
        return [
            dateFormat($merchantPayment->created_at),
            isset($merchantPayment->merchant->user->first_name) && !empty($merchantPayment->merchant->user->first_name) ? $merchantPayment->merchant->user->first_name . ' ' . $merchantPayment->merchant->user->last_name : "-",
            isset($merchantPayment->user->first_name) && !empty($merchantPayment->user->first_name) ? $merchantPayment->user->first_name . ' ' . $merchantPayment->user->last_name : "-",
            formatNumber($merchantPayment->amount, $merchantPayment->currency_id),
            ($merchantPayment->charge_percentage == 0) && ($merchantPayment->charge_fixed == 0) ? '-' : formatNumber($merchantPayment->charge_percentage + $merchantPayment->charge_fixed, $merchantPayment->currency_id),
            '+' . formatNumber($merchantPayment->amount + ($merchantPayment->charge_percentage + $merchantPayment->charge_fixed), $merchantPayment->currency_id),
            isset($merchantPayment->currency->code) ? $merchantPayment->currency->code : '',

            ($merchantPayment->payment_method->name == "Mts") ? settings('name') : $merchantPayment->payment_method->name,
            getStatus($merchantPayment->status)
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
