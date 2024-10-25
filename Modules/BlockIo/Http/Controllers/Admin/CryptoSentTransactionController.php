<?php

namespace Modules\BlockIo\Http\Controllers\Admin;

use Modules\BlockIo\DataTables\CryptoSentTransactionsDataTable;
use Modules\BlockIo\Exports\CryptoSendsExport;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BlockIo\Classes\BlockIo;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class CryptoSentTransactionController extends Controller
{
    protected $transaction;
    protected $blockIo;

    public function __construct()
    {
        $this->transaction = new \App\Models\Transaction();
        $this->blockIo = new BlockIo();
    }

    public function index(CryptoSentTransactionsDataTable $dataTable)
    {
        $data['menu'] = 'transaction';
        $data['sub_menu'] = 'crypto-sent-transactions';

        $cryptoSentTransactions = $this->transaction->where('transaction_type_id', Crypto_Sent);
        $data['cryptoSentTransactionsCurrencies'] = $cryptoSentTransactions->with('currency:id,code')->groupBy('currency_id')->get(['currency_id']);
        $data['cryptoSentTransactionsStatus'] = $cryptoSentTransactions->groupBy('status')->get(['status']);

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['user']     = $user = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->transaction->getTransactionsUsersEndUsersName($user, Crypto_Sent);
        return $dataTable->render('blockio::admin.crypto_transactions.sent.index', $data);
    }

    public function cryptoSentTransactionsSearchUser(Request $request)
    {
        $search = $request->search;
        $user   = $this->transaction->getTransactionsUsersResponse($search, Crypto_Sent);
        $res    = [
            'status' => 'fail',
        ];
        if (count($user) > 0) {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function view($id)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'crypto-sent-transactions';

        $data['transaction'] = $transaction = $this->transaction->with([
            'user:id,first_name,last_name',
            'end_user:id,first_name,last_name',
            'currency:id,code,symbol',
            'payment_method:id,name',
            'cryptoAssetApiLog:id,object_id,payload,confirmations',
        ])
        ->where('transaction_type_id', Crypto_Sent)
        ->exclude(['merchant_id', 'bank_id', 'file_id', 'refund_reference', 'transaction_reference_id', 'email', 'phone', 'note'])
        ->find($id);

        // Get crypto api log details for Crypto_Sent
        if (!empty($transaction->cryptoAssetApiLog)) {
            $getCryptoDetails = $this->blockIo->getCryptoPayloadConfirmationsDetails($transaction->transaction_type_id, $transaction->cryptoAssetApiLog->payload, $transaction->cryptoAssetApiLog->confirmations);
            if (count($getCryptoDetails) > 0) {
                if (isset($getCryptoDetails['senderAddress'])) {
                    $data['senderAddress'] = $getCryptoDetails['senderAddress'];
                }
                if (isset($getCryptoDetails['receiverAddress'])) {
                    $data['receiverAddress'] = $getCryptoDetails['receiverAddress'];
                }
                if (isset($getCryptoDetails['network_fee'])) {
                    $data['network_fee'] = $getCryptoDetails['network_fee'];
                }

                $data['txId'] = $getCryptoDetails['txId'];
                $data['confirmations'] = $getCryptoDetails['confirmations'];
            }
        }
        return view('blockio::admin.crypto_transactions.sent.view', $data);
    }

    public function cryptoSentTransactionsCsv()
    {
        return Excel::download(new CryptoSendsExport(), 'crypto_sent_transactions_list_' . time() . '.xlsx');
    }

    public function cryptoSentTransactionsPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;

        $data['getCryptoSentTransactions'] = $this->transaction->getCryptoSentTransactions($from, $to, $status, $currency, $user)->orderBy('transactions.id', 'desc')->get();

        if (isset($from) && isset($to)) {
            $data['date_range'] = $from . ' To ' . $to;
        } else {
            $data['date_range'] = 'N/A';
        }

        // Input parameters (view, filename, printdata)
        generatePDF('blockio::admin.crypto_transactions.sent.crypto_sent_transactions_report_pdf', 'cyprto_sent_transactions_report_', $data);
    }
}