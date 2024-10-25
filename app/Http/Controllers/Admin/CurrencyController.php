<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CurrenciesDataTable;
use App\Http\Controllers\Controller;
use DB, Config, Common, Exception;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use App\Models\{
    Transaction,
    Currency,
    Wallet,
    User
};

class CurrencyController extends Controller
{
    protected $helper;
    protected $currency;

    public function __construct()
    {
        $this->helper = new Common();
        $this->currency = new Currency();
    }

    public function index(CurrenciesDataTable $dataTable)
    {
        $data['menu'] = 'currency';
        return $dataTable->render('admin.currencies.view', $data);
    }

    public function add(Request $request)
    {
        $data['menu'] = 'currency';
        
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'name' => 'required',
                'code' => 'required',
                'symbol' => 'required',
                'type' => 'required',
                'rate' => $request->type == 'fiat' ? 'required|numeric|min:0.0001' : '',
                'logo' => ['nullable', new CheckValidFile(getFileExtensions(3))],
                'exchange_from' => $request->type == 'fiat' ? 'required' : '',
                'address' => $request->type == 'crypto' ? 'required' : '',
                'status' => 'required',
            ], [
                'rate.min' => 'Please enter values greater than 0.0001',
                'code.unique' => 'The currency already exists',
                'exchange_from.required' => 'Exchange from is required' 
            ]);

            $getCurrency = $this->currency->getCurrency(['code' => $request->code], ['type', 'code']);

            if (!empty($getCurrency) && $getCurrency->code === $request->code && $getCurrency->type === $request->type) {
                $this->helper->one_time_message('error', __('The :x is already exist.', ['x' => __('currency')]));
                return redirect(config('adminPrefix').'/settings/add_currency')->withInput();
            }

            try {
                
                DB::beginTransaction();

                $currency = new Currency();
                $currency->name = $request->name;
                $currency->code = $request->code;
                $currency->symbol = $request->symbol;
                $currency->type = $request->type == 'fiat' ? 'fiat' : 'crypto';
                $currency->rate = $request->type == 'fiat' ? $request->rate : 0;
                $currency->address = $request->type == 'crypto' ?  $request->address : '';
                $currency->exchange_from = $request->exchange_from;
                $currency->status = $request->status == 'Active' ? 'Active' : 'Inactive';
                $currency->default = '0';
                $filename = $this->processCurrencyLogo('add', $request, null);
                $currency->logo = $filename;
                $currency->save();

                DB::commit();
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('currency')]));
                return redirect(config('adminPrefix').'/settings/currency');

            } catch (Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect(config('adminPrefix').'/settings/currency');
            }
        }

        return view('admin.currencies.add', $data);
    }

    public function update(Request $request, $id)
    {
        $data['menu'] = 'currency';

        if ($request->isMethod('post')) {

            $this->validate($request, [
                'name' => 'required',
                'code' => 'required',
                'symbol' => 'required',
                'type' => 'required',
                'rate' => $request->type == 'fiat' ? 'required|numeric|min:0.0001' : '',
                'logo' => ['nullable', new CheckValidFile(getFileExtensions(3))],
                'exchange_from' => $request->type == 'fiat' ? 'required' : '',
                'address' => $request->type == 'crypto' ? 'required' : '',
                'status' => $request->default_currency == '0' ? 'required' : '',
            ], [
                'rate.min' => 'Please enter values greater than 0.0001',
                'code.unique' => 'The currency already exists',
                'exchange_from.required' => 'Exchange from is required' 
            ]);

            $getCurrency = Currency::where('id', '!=', $id)->where(['code' => $request->code, 'type' => $request->type])->first(['id', 'code', 'type']);

            if (!empty($getCurrency)) {
                $this->helper->one_time_message('error', __('The :x is already exist.', ['x' => __('currency')]));
                return redirect()->back()->withInput();
            }

            $currency = Currency::find($id);

            if ($currency->default == 1 && $request->rate != 1) {
                $this->helper->one_time_message('error', __('For default currency exchange rate should be 1.'));
                return back()->withInput();
            }

            // Checking if the currency exist in modules operation
            $module = moduleExistChecker($currency);
            if (!empty($module) && $request->status == 'Inactive') {
                $this->helper->one_time_message('error',  __('The :x already exist for this currency. Currency cannot be deactivated.', ['x' => implode(', ', $module)]));
                return redirect(config('adminPrefix').'/settings/currency');
            }

            try {
                DB::beginTransaction();

               
                $currency->name = $request->name;
                $currency->symbol = $request->symbol;
                $currency->code = $request->code;
                $currency->type = $request->type == 'crypto' ? 'crypto' : 'fiat';
                $currency->rate = $request->type == 'fiat' ? $request->rate : 0;
                $currency->exchange_from = $request->exchange_from;
                $currency->address = $request->type == 'crypto' ?  $request->address : '';
                
                if ($request->default_currency == 1) {
                    $currency->status  = 'Active';
                    $currency->default = 1;
                } else {
                    $currency->status  = $request->status == 'Active' ? 'Active' : 'Inactive';
                    $currency->default = 0;
                }

                if (isset($request->logo)) {
                    $filename = $this->processCurrencyLogo('edit', $request, $currentLogo = $currency->logo);
                    $currency->logo = $filename;
                }
                if ($request->type == 'fiat' || $request->type == 'crypto') {
                    if ($request->create_wallet == 'on') {
                        $this->createUsersWallet($currency->id);
                        $currency->allowed_wallet_creation = 'Yes';
                    } else {
                        $currency->allowed_wallet_creation = 'No';
                    }
                }
                $currency->save();
                DB::commit();

                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('currency')]));
                return redirect(config('adminPrefix').'/settings/currency');
            }
            catch (Exception $e)
            {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect(config('adminPrefix').'/settings/currency');
            }
        }

        $data['currency'] = Currency::find($id);
        if (!empty($data['currency'])) {
            return view('admin.currencies.edit', $data);
        }
        $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('currency')]));
        return redirect(config('adminPrefix').'/settings/currency');
    }

    protected function processCurrencyLogo($type, $request, $currentLogo)
    {
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            if (isset($logo)) {
                if ($type == 'edit') {
                    $response = uploadImage($logo, getDirectory('currency'), '64*64', $currentLogo);
                } else {
                    $response = uploadImage($logo, getDirectory('currency'), '64*64');
                }

                if ($response['status']) {
                    return $response['file_name'];
                }

                return null;
            }
        }
    }

    public function deleteCurrencyLogo(Request $request)
    {
        $logo = $request->logo;

        if (isset($logo)) {
            $currency = Currency::where(['id' => $request->currency_id, 'logo' => $request->logo])->first();
            if ($currency) {
                Currency::where(['id' => $request->currency_id, 'logo' => $request->logo])->update(['logo' => null]);
                if ($logo != null) {
                    $dir = public_path('uploads/currency_logos/' . $logo);
                    if (file_exists($dir)) {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = __('The :x has been successfully deleted.', ['x' => __('logo')]);
            } else {
                $data['success'] = 0;
                $data['message'] = __('The :x does not exist.', ['x' => __('logo')]);
            }
        }
        echo json_encode($data);
        exit();
    }

    public function delete($id)
    {
        $currency = Currency::find($id);
        if (!empty($currency)) {
            $transaction = Transaction::where(['currency_id' => $currency->id])->first();

            // Checking if the currency exist in modules operation
            $module = moduleExistChecker($currency);
            if (!empty($module)) {
                $this->helper->one_time_message('error',  __('The :x already exist for this currency. Currency cannot be deleted.', ['x' => implode(', ', $module)]));
                return redirect(config('adminPrefix').'/settings/currency');
            }

            if (isset($currency) && $currency->default == 1) {
                $this->helper->one_time_message('error', __('The default :x cannot be deleted.', ['x' => __('currency')]));
            } elseif (isset($transaction)) {
                $this->helper->one_time_message('error', __('This currency cannot be deleted. Already transaction exist.'));
            } else {
                if (isset($currency)) {
                    $location = public_path('uploads/currency_logos/' . $currency->logo);
                    if (isset($currency->logo) && file_exists($location)) {
                        unlink($location);
                    }
                    $currency->delete();
                }
                $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('currency')]));
            }
            return redirect(config('adminPrefix').'/settings/currency');
        }
        $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('currency')]));
        return redirect(config('adminPrefix').'/settings/currency');
    }

    protected function createUsersWallet($currencyId)
    {
        $users = User::with(['wallets' => function ($q) use ($currencyId)
        {
            $q->where(['currency_id' => $currencyId]);
        }])
        ->where(['status' => 'Active'])
        ->get(['id']);

        if (!empty($users)) {
            foreach ($users as $user)
            {
                $getWalletObject = $this->helper->getUserWallet([], ['user_id' => $user->id, 'currency_id' => $currencyId], ['id']);
                if (empty($getWalletObject) && count($user->wallets) == 0) {
                    $wallet              = new Wallet();
                    $wallet->user_id     = $user->id;
                    $wallet->currency_id = $currencyId;
                    $wallet->is_default  = 'No';
                    $wallet->save();
                }
            }
        }
    }
}


