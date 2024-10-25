<?php

namespace Modules\BlockIo\Exports;

use Maatwebsite\Excel\Concerns\{FromQuery,
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithStyles
};

class CryptoSendsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to       = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;

        $getCryptoSentTransactions = (new \App\Models\Transaction())->getCryptoSentTransactions($from, $to, $status, $currency, $user)->orderBy('transactions.id', 'desc');

        return $getCryptoSentTransactions;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Sender',
            'Amount',
            'Fees',
            'Total',
            'Crypto Currency',
            'Receiver',
            'status'
        ];
    }

    public function map($cryptoSentTransaction): array
    {
        return [
            dateFormat($cryptoSentTransaction->created_at),
            getColumnValue($cryptoSentTransaction->user),
            $cryptoSentTransaction->subtotal,
            $cryptoSentTransaction->charge_fixed,
            $this->total($cryptoSentTransaction),
            optional($cryptoSentTransaction->currency)->code,
            getColumnValue($cryptoSentTransaction->end_user),
            $cryptoSentTransaction->status
        ];
    }

    public function total($transaction)
    {
        if ($transaction->total > 0) {
            $total = '+' . $transaction->total;
        } else {
            $total = formatNumber($transaction->total - json_decode($transaction->cryptoAssetApiLog->payload)->network_fee, $transaction->currency_id);
        }
        return $total;
    }

    public function styles($cryptoSentTransaction)
    {
        $cryptoSentTransaction->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $cryptoSentTransaction->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $cryptoSentTransaction->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $cryptoSentTransaction->getStyle('G:H')->getAlignment()->setHorizontal('center');
        $cryptoSentTransaction->getStyle('1')->getFont()->setBold(true);
    }
}
