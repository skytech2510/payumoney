<?php

namespace App\Exports;

use App\Models\Withdrawal;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class WithdrawalsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $pm       = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;

        $withdrawals = (new withdrawal())->getWithdrawalsList($from, $to, $status, $currency, $pm, $user)->orderBy('withdrawals.id', 'desc');

        return $withdrawals;
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
            __('Method Info'),
            __('Status'),
        ];
    }

    public function map($withdrawal): array
    {
        return [
            dateFormat($withdrawal->created_at),
            isset($withdrawal->user->first_name) && !empty($withdrawal->user->first_name) ? $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name : "-",
            formatNumber($withdrawal->amount, $withdrawal->currency_id),
            ($withdrawal->charge_percentage == 0) && ($withdrawal->charge_fixed == 0) ? '-' : formatNumber($withdrawal->charge_percentage + $withdrawal->charge_fixed, $withdrawal->currency_id),
            '-' . formatNumber($withdrawal->amount + ($withdrawal->charge_percentage + $withdrawal->charge_fixed), $withdrawal->currency_id),
            isset($withdrawal->currency->code) ? $withdrawal->currency->code : '',
            ($withdrawal->payment_method->name == "Mts") ? settings('name') : $withdrawal->payment_method->name,
            $this->paymentMethodInfo($withdrawal), 
            getStatus($withdrawal->status)
        ];
    }

    public function paymentMethodInfo($withdrawal)
    {
        if (isset($withdrawal->payment_method_info) && $withdrawal->payment_method->name != "Bank") {
            $payment_method_info_withdrawal = !empty($withdrawal->payment_method_info) ? $withdrawal->payment_method_info : '-';
        } else {
            $payment_method_info_withdrawal = isset($withdrawal->withdrawal_detail->account_name) && !empty($withdrawal->withdrawal_detail->account_name) && !empty($withdrawal->withdrawal_detail->account_number) ?
                $withdrawal->withdrawal_detail->account_name . ' ' . '(' . ('*****' . substr($withdrawal->withdrawal_detail->account_number, -4)) . ')' . ' ' . $withdrawal->withdrawal_detail->bank_name : '-';
        }
        return $payment_method_info_withdrawal;
    }

    public function styles($withdrawal)
    {
        $withdrawal->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $withdrawal->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $withdrawal->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $withdrawal->getStyle('G:H')->getAlignment()->setHorizontal('center');
        $withdrawal->getStyle('I')->getAlignment()->setHorizontal('center');
        $withdrawal->getStyle('1')->getFont()->setBold(true);
    }
}
