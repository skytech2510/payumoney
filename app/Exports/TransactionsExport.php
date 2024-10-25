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

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $type     = isset(request()->type) ? request()->type : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;

        $transaction = (new Transaction())->getTransactionsList($from, $to, $status, $currency, $type, $user)->orderBy('transactions.id', 'desc')->take(1100);

        return $transaction;
    }

    public function headings(): array
    {
        return [
            __('Date'),
            __('User'),
            __('Type'),
            __('Amount'),
            __('Fees'),
            __('Total'),
            __('Currency'),
            __('Receiver'),
            __('Status'),
        ];
    }

    public function map($transaction): array
    {
        return [
            dateFormat($transaction->created_at),

            $this->user($transaction),

            isset($transaction->transaction_type->name) ? str_replace('_', ' ', $transaction->transaction_type->name) : '',

            // amount
            formatNumber($transaction->subtotal, $transaction->currency_id),
            // fees
            ($transaction->charge_percentage == 0) && ($transaction->charge_fixed == 0) ? '-' :  formatNumber($transaction->charge_percentage + $transaction->charge_fixed, $transaction->currency_id),
            // total
            ($transaction->total > 0) ?  '+' . formatNumber($transaction->total, $transaction->currency_id) : formatNumber($transaction->total, $transaction->currency_id),

            isset($transaction->currency->code) ? $transaction->currency->code : '',

            $this->receiver($transaction),
            getStatus($transaction->status)
        ];
    }

    public function user($transaction)
    {
        $user = '-';
        $transactionTypes = getPaymoneySettings('transaction_types')['web'];

        if (in_array($transaction->transaction_type_id, $transactionTypes['sent'])) {
            if (isset($transaction->user->first_name) && !empty($transaction->user->first_name)) {
                $user = $transaction->user->first_name . ' ' . $transaction->user->last_name;
            } elseif (module('CryptoExchange') && isset($transaction->crypto_exchange) && !empty($transaction->crypto_exchange)) {
                $user = (isset($transaction->crypto_exchange->email_phone) && !empty($transaction->crypto_exchange->email_phone)) ? $transaction->crypto_exchange->email_phone : '-';
            } 
        } elseif (in_array($transaction->transaction_type_id, $transactionTypes['received'])) {
            $user = isset($transaction->end_user->first_name) && !empty($transaction->end_user->first_name) ? $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name : "-";
        }

        return $user;
    }

    public function receiver($transaction)
    {
        $receiver = getTransactionListUser($transaction, 'receiver', false);
        if (!is_null($receiver)) {
            return $receiver;
        }

        $receiver = '-';

        switch ($transaction->transaction_type_id) {
            case Deposit:
            case Exchange_From:
            case Exchange_To:
            case Withdrawal:
            case (module('CryptoExchange') ? Crypto_Buy : false ):
            case (module('CryptoExchange') ? Crypto_Sell : false ):
            case (module('CryptoExchange') ? Crypto_Swap : false ):
                $receiver = isset($transaction->end_user->first_name) && !empty($transaction->end_user->first_name) ? $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name : "-";
                break;
            case Transferred:
            case Received:
                if (isset($transaction->transfer->receiver->first_name) && !empty($transaction->transfer->receiver->first_name)) {
                    $receiver = $transaction->transfer->receiver->first_name . ' ' . $transaction->transfer->receiver->last_name;
                } elseif (isset($transaction->transfer->email) && !empty($transaction->transfer->email)) {
                    $receiver = $transaction->transfer->email;
                } elseif (isset($transaction->transfer->phone) && !empty($transaction->transfer->phone)) {
                    $receiver = $transaction->transfer->phone;
                } else {
                    $receiver = '-';
                }
                break;
            case Request_Sent:
            case Request_Received:
                $receiver = isset($transaction->request_payment->email) ? $transaction->request_payment->email : '-';
                $receiver = isset($transaction->request_payment->receiver->first_name) && !empty($transaction->request_payment->receiver->first_name) ? $transaction->request_payment->receiver->first_name . ' ' . $transaction->request_payment->receiver->last_name : $receiver;
                break;
            case Payment_Sent:
                $receiver = isset($transaction->end_user->first_name) && !empty($transaction->end_user->first_name) ? $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name : "-";
                break;
            case Payment_Received:
                $receiver = isset($transaction->user->first_name) && !empty($transaction->user->first_name) ? $transaction->user->first_name . ' ' . $transaction->user->last_name : "-";
                break;
        }

        return $receiver;
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
