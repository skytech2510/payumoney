<?php

namespace Modules\BlockIo\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\{FromQuery,
    ShouldAutoSize,
    WithHeadings,
    WithMapping,
    WithStyles
};

class CryptoReceivesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to       = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;

        $getCryptoReceivedTransactions = (new Transaction())->getCryptoReceivedTransactions($from, $to, $currency, $user)->orderBy('transactions.id', 'desc');

        return $getCryptoReceivedTransactions;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Sender',
            'Amount',
            'Crypto Currency',
            'Receiver',
        ];
    }

    public function map($getCryptoReceivedTransactions): array
    {
        return [
            dateFormat($getCryptoReceivedTransactions->created_at),
            getColumnValue($getCryptoReceivedTransactions->end_user),
            '+' . $getCryptoReceivedTransactions->subtotal,
            $getCryptoReceivedTransactions->currency->code,
           getColumnValue($getCryptoReceivedTransactions->user)
        ];
    }

    public function styles($getCryptoReceivedTransactions)
    {
        $getCryptoReceivedTransactions->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $getCryptoReceivedTransactions->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $getCryptoReceivedTransactions->getStyle('E')->getAlignment()->setHorizontal('center');
        $getCryptoReceivedTransactions->getStyle('1')->getFont()->setBold(true);
    }
}
