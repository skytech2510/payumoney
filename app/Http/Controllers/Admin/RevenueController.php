<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\RevenuesDataTable;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Exports\RevenuesExport;

class RevenueController extends Controller
{
    public function revenues_list(RevenuesDataTable $dataTable)
    {
        $data['menu'] = 'revenues';

        $revenueTransactionTypes = [Deposit, Withdrawal, Exchange_From, Transferred, Request_Received, Payment_Received]; 

        if (module('CryptoExchange') ) {
            array_push($revenueTransactionTypes, Crypto_Swap, Crypto_Buy, Crypto_Sell);
        }

        $data['revenues_currency'] = (new Transaction())->where(function($query) {
            $query->where('charge_percentage', '>', 0);
            $query->orWhere('charge_fixed', '!=', 0);
        })
        ->where('status', 'Success')
        ->whereIn('transaction_type_id', $revenueTransactionTypes)->groupBy('currency_id')->select('currency_id')->get();

        $data['revenues_type'] = (new Transaction())->where(function($query) {
            $query->where('charge_percentage', '>', 0);
            $query->orWhere('charge_fixed', '!=', 0);
        })
        ->where('status', 'Success')
        ->whereIn('transaction_type_id', $revenueTransactionTypes)->groupBy('transaction_type_id')->select('transaction_type_id')->get();
        

        $data['from']     = $from    = isset(request()->from) && !empty(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = $to      = isset(request()->to) && !empty(request()->to) ? setDateForDb(request()->to) : null;
        $data['type']     = $type    = isset(request()->type) ? request()->type : null;
        $data['currency'] = $currency= isset(request()->currency) ? request()->currency : 'all';

        $getRevenuesListForCurrencyIfo = (new Transaction())->getRevenuesList($from, $to, $currency, $type)->orderBy('transactions.id', 'desc')->get();

        $array = $codes =[];

        if ($getRevenuesListForCurrencyIfo->count() > 0) {
            foreach ($getRevenuesListForCurrencyIfo as $value) {
                if (isset($value->currency->code)) {
                    if (!in_array($value->currency->code, $codes)) {
                        $array[$value->currency->code]['revenue'] = 0;
                        $array[$value->currency->code]['currency_id'] = $value->currency->id;
                        $codes[] = $value->currency->code;
                    }
                    $array[$value->currency->code]['revenue'] += ($value->charge_percentage + $value->charge_fixed);
                }
            }
            $data['currencyInfo'] = $array;
        } else {
            $data['currencyInfo'] = [];
        }
        return $dataTable->render('admin.revenues.list', $data);
    }

    public function revenueCsv()
    {
        return Excel::download(new RevenuesExport(), 'revenues_list_' . time() . '.xlsx');
    }

    public function revenuePdf()
    {
        $from     = isset(request()->startfrom) && !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to       = isset(request()->endto) && !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $type     = isset(request()->type) ? request()->type : null;
        $currency = isset(request()->currency) ? request()->currency : null;

        $data['revenues'] = (new Transaction())->getRevenuesList($from, $to, $currency, $type)->orderBy('transactions.id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.revenues.revenues_report_pdf', 'revenues_report_', $data);
    }
}
