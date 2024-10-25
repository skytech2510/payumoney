<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Transaction,
    Wallet
};

class UserTransactionController extends Controller
{
    public function index()
    {
        $transaction      = new Transaction();
        $data['menu']     = 'transactions';
        $data['sub_menu'] = 'transactions';

        $data['from']     = $from   = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = $to     = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = $status = isset(request()->status) ? request()->status : 'all';
        $data['type']     = $type   = isset(request()->type) ? request()->type : 'all';
        $data['wallet']   = $wallet = isset(request()->wallet) ? request()->wallet : 'all';

        $data['transactions'] = $transaction->getTransactions($from, $to, $type, $wallet, $status);

        $data['wallets'] = Wallet::with(['currency:id,code'])->where(['user_id' => auth()->user()->id])->get(['currency_id']);
        $data['transactionTypes'] = getTransactionTypes();
        $data['statuses'] = Transaction::select('status')->distinct()->get();

        return view('user.transaction.index', $data);
    }


    /**
     * Generate pdf print for exchangeTransaction entries
     */
    public function exchangeTransactionPrintPdf($id)
    {
        $data['transaction'] = $transaction = Transaction::with([
            'currency:id,code,symbol',
        ])->where(['id' => $id])->first();
        
        generatePDF('user.exchange-currency.exchange-transaction-pdf', 'exchange_', $data);
    }

    /**
     * Generate pdf print for merchant payment entries
     */
    public function merchantPaymentTransactionPrintPdf($id)
    {
        $data['transaction'] = Transaction::with([
            'merchant:id,business_name',
            'currency:id,symbol,code',
        ])->where(['id' => $id])->first();

        generatePDF('user.merchant.merchant-payment-pdf', 'merchant-payment_', $data);
    }
}