<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CurrencyExchangesDataTable;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use App\Models\{CurrencyExchange,
    Transaction,
    Wallet
};
use App\Exports\CurrencyExchangesExport;
use Illuminate\Support\Facades\Config;

class ExchangeController extends Controller
{
    protected $helper;
    protected $exchange;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->exchange = new CurrencyExchange();
    }

    public function index(CurrencyExchangesDataTable $dataTable)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'exchanges';

        $data['exchanges_status'] = $this->exchange->select('status')->groupBy('status')->get();

        $data['exchanges_currency'] = $this->exchange->join('wallets', function ($join)
        {
            $join->on('wallets.id', '=', 'currency_exchanges.from_wallet')->orOn('wallets.id', '=', 'currency_exchanges.to_wallet');
        })
            ->groupBy('wallets.currency_id')->select('wallets.currency_id', 'wallets.id as wallet_id')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['user']     = $user    = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->exchange->getExchangesUserName($user);

        return $dataTable->render('admin.exchange.list', $data);
    }

    public function exchangesUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->exchange->getExchangesUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0)
        {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function exchangeCsv()
    {
        return Excel::download(new CurrencyExchangesExport(), 'exchanges_list_' . time() . '.xlsx');
    }

    public function exchangePdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;

        $exchanges = $this->exchange->getExchangesListForCsvExport($from, $to, $status, $currency, $user)->get();
        $data['exchanges'] = $exchanges = collect($exchanges)->sortByDesc('id');

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.exchange.exchanges_report_pdf', 'exchanges_report_', $data);
    }

    public function edit($id)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'exchanges';
        $data['exchange'] = $exchange = CurrencyExchange::find($id);

        $data['transaction'] = Transaction::select('transaction_type_id', 'status', 'transaction_reference_id', 'percentage', 'charge_percentage', 'charge_fixed', 'uuid')
            ->where(['transaction_reference_id' => $exchange->id, 'uuid' => $exchange->uuid])
            ->whereIn('transaction_type_id', [Exchange_From, Exchange_To])
            ->first();

        return view('admin.exchange.edit', $data);
    }

    public function update(Request $request)
    {
        if ($request->type == "Out")
        {
            if ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('Exchange'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix').'/exchanges');
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    $exchange         = CurrencyExchange::find($request->id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    // Exchange_From
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Exchange_To
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $from_wallet = Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->update([
                        'balance' => $from_wallet->balance - $request->total,
                    ]);

                    //receiver wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        'balance' => $to_wallet->balance + ($request->amount * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('backup')]));
                    return redirect(config('adminPrefix').'/exchanges');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($request->transaction_status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('exchange'), 'y' => __('blocked')]));
                    return redirect(config('adminPrefix').'/exchanges');
                }
                elseif ($request->transaction_status == 'Success') //current status
                {
                    $exchange         = CurrencyExchange::find($request->id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    // // Exchange_From
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // // Exchange_To
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sent wallet entry update
                    $from_wallet = Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->update([
                        'balance' => $from_wallet->balance + $request->total,
                    ]);

                    //received wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        'balance' => $to_wallet->balance - ($request->amount * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('exchange')]));
                    return redirect(config('adminPrefix').'/exchanges');
                }
            }
        }
    }
}
