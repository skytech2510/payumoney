<?php

namespace App\Http\Controllers\Admin;

use Validator, DB, Session, Exception, Common;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use App\Models\{Bank,
    CurrencyPaymentMethod,
    PaymentMethod,
    Transaction,
    Currency,
    Country,
    File
};

class CurrencyPaymentMethodController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function paymentMethodList($tab, $id)
    {
        $data['menu'] = 'currency';
        $data['list_menu'] = $tab;

        $data['currency'] = (new Currency())->getCurrency(['id' => $id], ['id','name', 'type']);
        if (empty($data['currency'])) {
            $this->helper->one_time_message('error', __('The :x does not exist.', ['x' => __('payment method settings')]));
            return redirect()->back();
        }

        if ($data['currency']->type == 'crypto_asset') {
            $this->helper->one_time_message('error', __('Payment method setting is not allowed for crypto asset.'));
            return redirect()->back();
        }
        $data['currencyList'] = (new Currency())->getAllCurrencies(['status' => 'Active', 'type' => $data['currency']->type], ['id','name']);

        $paymentMethod = PaymentMethod::where(['name' => ucfirst($tab), 'status' => 'Active'])->first(['id', 'name']);
        $data['paymentMethodName'] = $paymentMethod->name;
        $data['paymentMethod'] = $paymentMethod->id;
        $data['currencyPaymentMethod'] = CurrencyPaymentMethod::where(['method_id' => $paymentMethod->id, 'currency_id' => $id])->first();

        $data['banks'] = Bank::where(['currency_id' => $id])->get();
        $data['countries'] = Country::get(['id', 'name']);

        return view('admin.currencyPaymentMethod.list', $data);
    }

    public function updatePaymentMethodCredentials(Request $request)
    {
        $paymentMethods = ['stripe', 'paypal', 'payUmoney', 'coinpayments', 'payeer', 'coinbase'];

        $activateFor = currencyTransactionTypes($request['transaction_type']);

        $currencyPaymentMethod = CurrencyPaymentMethod::where(['method_id' => $request->paymentMethod, 'currency_id' => $request->currency_id])->first();

        if (empty($currencyPaymentMethod)) {
            $currencyPaymentMethod = new CurrencyPaymentMethod();
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id   = $request->paymentMethod;
            $currencyPaymentMethod->activated_for = json_encode($activateFor);

            if (in_array($request->tabText, $paymentMethods)) {
                $currencyPaymentMethod->method_data = json_encode($request->{$request->tabText});
            } else {
                $currencyPaymentMethod->method_data = json_encode(['' => '']);
            }

            $currencyPaymentMethod->processing_time = $request->processing_time;

            $currencyPaymentMethod->save();
        } else {
            $currencyPaymentMethod = CurrencyPaymentMethod::find($request->id);
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id = $request->paymentMethod;
            $currencyPaymentMethod->activated_for = json_encode($activateFor);

            if (in_array($request->tabText, $paymentMethods)) {
                $currencyPaymentMethod->method_data = json_encode($request->{$request->tabText});
            } else {
                $currencyPaymentMethod->method_data = json_encode(['' => '']);
            }

            $currencyPaymentMethod->processing_time = $request->processing_time;

            $currencyPaymentMethod->save();
        }
        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('payment method settings')]));
        return redirect(config('adminPrefix').'/settings/payment-methods/' . $request->tabText . '/' . $request->currency_id);
    }

    public function getPaymentMethodsSpecificCurrencyDetails(Request $request)
    {
        $data = [];

        $currency = Currency::where(['id' => $request->currency_id])->first();
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['method_id' => $request->paymentMethod, 'currency_id' => $request->currency_id])->first();

        if ($currency && $currencyPaymentMethod) {
            $data['status'] = 200;
            $data['currency'] = $currency;
            $data['currencyPaymentMethod'] = $currencyPaymentMethod;
        } else {
            $data['status']   = 401;
            $data['currency'] = $currency;
        }

        if ($request->paymentMethod == Bank) {
            $banks = Bank::where(['currency_id' => $request->currency_id])->get();
            $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('method_data', 'like', "%bank_id%")->get();
            $bankList = $this->bankList($banks, $currencyPaymentMethods);

            if ($bankList) {
                $data['flag']  = true;
                $data['methodTitle'] = 'Bank';
                $data['banks'] = $bankList;
            } else {
                $data['flag']  = false;
                $data['banks'] = __('No bank available for this currency.');
            }
        }

        if (config('mobilemoney.is_active') && $request->paymentMethod == (defined('MobileMoney') ? MobileMoney : '')) {
            $mobileMoneys = \App\Models\MobileMoney::where(['currency_id' => $request->currency_id])->get();
            $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('method_data', 'like', "%mobilemoney_id%")->get();
           
            $mobileMoneyList = \App\Models\MobileMoney::getMobileMoneyLists($mobileMoneys, $currencyPaymentMethods);

            if ($mobileMoneyList) {
                $data['flag']  = true;
                $data['methodTitle']  = "MobileMoney";
                $data['mobileMoneys'] = $mobileMoneyList;
            } else {
                $data['flag'] = false;
                $data['mobileMoneys'] = __("No MobileMoney available for this currency.");
            }
        }

        return $data;
    }

    public function getPaymentMethodsDetails(Request $request)
    {
        $data = [];
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['method_id' => $request->paymentMethod, 'currency_id' => $request->currency_id])->first();

        if (empty($currencyPaymentMethod)) {
            $data['status'] = 401;
        }
        else {
            $data['status']                = 200;
            $data['currencyPaymentMethod'] = $currencyPaymentMethod;
        }

        if ($request->paymentMethod == Bank) {
            $banks = Bank::where(['currency_id' => $request->currency_id])->get();
            $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('method_data', 'like', "%bank_id%")->get();
            $bankList = $this->bankList($banks, $currencyPaymentMethods);
            if ($bankList) {
                $data['flag']  = true;
                $data['methodTitle']  = "Bank";
                $data['banks'] = $bankList;
            } else {
                $data['flag'] = false;
                $data['banks'] = __('No bank available for this currency.');
            }
        }

        if (config('mobilemoney.is_active') && $request->paymentMethod == (defined('MobileMoney') ? MobileMoney : '')) {
            $mobileMoneys  = \App\Models\MobileMoney::where(['currency_id' => $request->currency_id])->get();
            $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('method_data', 'like', "%mobilemoney_id%")->get();
            $mobileMoneyList = \App\Models\MobileMoney::getMobileMoneyLists($mobileMoneys, $currencyPaymentMethods);

            if ($mobileMoneyList) {
                $data['flag']   = true;
                $data['methodTitle']  = "MobileMoney";
                $data['mobileMoneys'] = $mobileMoneyList;
            } else {
                $data['flag']  = false;
                $data['mobileMoneys'] = __('No MobileMoney available for this currency.');
            }
        }
        return $data;
    }

    public function bankList($banks, $currencyPaymentMethods)
    {
        $selectedBanks = [];
        $i             = 0;
        foreach ($banks as $bank)
        {
            foreach ($currencyPaymentMethods as $cpm)
            {
                if ($bank->id == json_decode($cpm->method_data)->bank_id)
                {
                    $selectedBanks[$i]['id']             = $bank->id;
                    $selectedBanks[$i]['bank_name']      = $bank->bank_name;
                    $selectedBanks[$i]['account_name']   = $bank->account_name;
                    $selectedBanks[$i]['account_number'] = $bank->account_number;
                    $selectedBanks[$i]['bank_name']      = $bank->bank_name;
                    $selectedBanks[$i]['is_default']     = $bank->is_default;

                    $selectedBanks[$i]['activated_for'] = $cpm->activated_for;
                    $i++;
                }
            }
        }
        return $selectedBanks;
    }

    //Add Bank
    public function addBank(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'account_name'   => 'required',
            'account_number' => 'required',
            'swift_code'     => 'required',
            'bank_name'      => 'required',
            'branch_name'    => 'required',
            'branch_city'    => 'required',
            'branch_address' => 'required',
            'country'        => 'required',
            'bank_logo'      => ['nullable', new CheckValidFile(getFileExtensions(7), true)],
        ]);

        if ($validation->passes()) {
            // File Entry - Add
            if ($request->hasFile('bank_logo')) {
                $bank_logo = $request->file('bank_logo');

                if (isset($bank_logo)) {
                    $response = uploadImage($bank_logo, getDirectory('bank_logos'), '120*80', null, '70*70');

                    if ($response['status'] === true) {
                        $fileName = $response['file_name'];
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'status'  => 500,
                            'message' => $response['message']
                        ]);
                    }

                    $fields = [
                        'admin_id' => auth('admin')->user()->id,
                        'filename' => $fileName,
                        'originalname' => $bank_logo->getClientOriginalName(),
                        'type' => strtolower($bank_logo->getClientOriginalExtension())
                    ];
                    $file = File::add($fields);
                }
            }

            $bank              = new Bank();
            $bank->admin_id    = auth('admin')->user()->id;
            $bank->currency_id = $request->currency_id;
            $bank->country_id  = $request->country;

            if (!empty($request->bank_logo) && !empty($file)) {
                $bank->file_id = $file->id;
            }

            $bank->bank_name           = $request->bank_name;
            $bank->bank_branch_name    = $request->branch_name;
            $bank->bank_branch_city    = $request->branch_city;
            $bank->bank_branch_address = $request->branch_address;
            $bank->account_name        = $request->account_name;
            $bank->account_number      = $request->account_number;
            $bank->swift_code          = $request->swift_code;
            $bank->is_default          = $request->default;
            $bank->save();

            $activateFor = currencyTransactionTypes($request['transaction_type']);

            $currencyPaymentMethod              = new CurrencyPaymentMethod();
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id   = $request->paymentMethod;
            $currencyPaymentMethod->activated_for = json_encode($activateFor);

            $bankJson = ['bank_id' => $bank->id];
            $currencyPaymentMethod->method_data = json_encode($bankJson);
            $currencyPaymentMethod->save();

            if ($bank->is_default == 'Yes') {
                Bank::where(['is_default' => 'Yes', 'currency_id' => $bank->currency_id])->where('id', '!=', $bank->id)->update(['is_default' => 'No']);
            }

            return response()->json([
                'status'  => true,
                'message' => __('The :x has been successfully saved.', ['x' => __('bank')])
            ]);

        } else {
            return response()->json([
                'status'  => 500,
                'message' => $validation->errors()->all(),
            ]);
        }
    }

    public function getCpmId(Request $request)
    {
        $bank = Bank::where(['id' => $request->bank_id])->first();

        $bankJson = [];
        $bankJson['bank_id'] = $bank->id;
        $cpm = CurrencyPaymentMethod::where(['currency_id' => $request->currency_id, 'method_data' => json_encode($bankJson)])->first(['id', 'activated_for']);

        $data = [];
        if ($cpm) {
            $data['status']              = true;
            $data['cpmId']               = $cpm->id;
            $data['cpmActivatedFor']     = $cpm->activated_for;
            $data['is_default']          = $bank->is_default;
            $data['account_name']        = $bank->account_name;
            $data['account_number']      = $bank->account_number;
            $data['bank_branch_address'] = $bank->bank_branch_address;
            $data['bank_branch_city']    = $bank->bank_branch_city;
            $data['bank_branch_name']    = $bank->bank_branch_name;
            $data['bank_name']           = $bank->bank_name;
            $data['country_id']          = $bank->country_id;
            $data['swift_code']          = $bank->swift_code;

            if (!empty($bank->file_id)) {
                $data['bank_logo'] = $bank->file->filename;
                $data['file_id']   = $bank->file_id;
            }
        } else {
            $data['status'] = false;
            $data['cpmId']  = __('The :x does not exist.', ['x' => __('currency payment method')]);
        }
        return $data;
    }

    public function deleteBankLogo(Request $request)
    {
        if (isset($request->file_id)) {
            $file = File::find($request->file_id);
            $bank = Bank::where(['file_id' => $request->file_id])->first(['file_id']);

            if ($file && $bank) {
                Bank::where(['file_id' => $request->file_id])->update(['file_id' => null]);
                File::find($request->file_id)->delete();

                $dir = public_path('uploads/files/bank_logos/' . $file->filename);
                $dirThumb = public_path('uploads/files/bank_logos/thumbs/' . $file->filename);
                if (file_exists($dir)) {
                    unlink($dir);
                }
                if (file_exists($dirThumb)) {
                    unlink($dirThumb);
                }
                $data['success'] = 1;
                $data['message'] = __('The :x has been successfully deleted.', ['x' => __('bank logo')]);
            } else {
                $data['success'] = 0;
                $data['message'] = __('The :x does not exist.', ['x' => __('bank logo')]);
            }
        }
        echo json_encode($data);
        exit();
    }

    //Update Bank
    public function updateBank(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'account_name'   => 'required',
            'account_number' => 'required',
            'swift_code'     => 'required',
            'bank_name'      => 'required',
            'branch_name'    => 'required',
            'branch_city'    => 'required',
            'branch_address' => 'required',
            'country'        => 'required',
            'bank_logo'      => ['nullable', new CheckValidFile(getFileExtensions(7), true)],
        ]);

        if ($validation->passes()) {
            // File Entry - Update
            if ($request->hasFile('bank_logo')) {
                $bank_logo = $request->file('bank_logo');

                if (isset($bank_logo)) {
                    $oldFileName = File::where('id', $request->file_id)->value('filename');

                    $response = uploadImage($bank_logo, getDirectory('bank_logos'), '120*80', $oldFileName, '70*70');

                    if ($response['status'] === true) {
                        $fileName = $response['file_name'];
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'status'  => 500,
                            'message' => $response['message']
                        ]);
                    }

                    $updates = [
                        'admin_id' => auth('admin')->user()->id,
                        'filename' => $fileName,
                        'originalname' => $bank_logo->getClientOriginalName(),
                        'type' => strtolower($bank_logo->getClientOriginalExtension())
                    ];
                    $file = File::createOrUpdate(['id' => $request->file_id], $updates);
                }
            }

            $bank              = Bank::find($request->bank_id);
            $bank->currency_id = $request->currency_id;
            $bank->country_id  = $request->country;
            $bank->admin_id    = auth('admin')->user()->id;

            if (!empty($request->bank_logo) && !empty($file)) {
                $bank->file_id = $file->id;
            }

            $bank->bank_name           = $request->bank_name;
            $bank->bank_branch_name    = $request->branch_name;
            $bank->bank_branch_city    = $request->branch_city;
            $bank->bank_branch_address = $request->branch_address;
            $bank->account_name        = $request->account_name;
            $bank->account_number      = $request->account_number;
            $bank->swift_code          = $request->swift_code;
            $bank->is_default          = $request->default;
            $bank->save();

            $activateFor = currencyTransactionTypes($request['transaction_type']);

            $currencyPaymentMethod              = CurrencyPaymentMethod::find($request->currencyPaymentMethodId);
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id   = $request->paymentMethod;
            $currencyPaymentMethod->activated_for = json_encode($activateFor);

            $bankJson = ['bank_id' => $bank->id];
            $currencyPaymentMethod->method_data = json_encode($bankJson);
            $currencyPaymentMethod->save();

            if ($bank->is_default == 'Yes') {
                Bank::where(['is_default' => 'Yes', 'currency_id' => $bank->currency_id])->where('id', '!=', $bank->id)->update(['is_default' => 'No']);
            }

            return response()->json([
                'status'  => true,
                'message' => __('The :x has been successfully saved.', ['x' => __('bank')])
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => $validation->errors()->all(),
            ]);
        }
    }

    //Delete Bank
    public function deleteBank(Request $request)
    {
        $transaction = Transaction::where(['bank_id' => $request->bank_id])->exists();
        
        if ($transaction) {
            return response()->json([
                'status' => false,
                'type' => 'error',
                'message' => __('Transactions have already been processed with this bank, cannot be deleted.')
            ]);
        }
        
        $bank = Bank::with('file:id,filename')->find($request->bank_id);

        if (!empty($bank)) {

            $bankJson = [];
            $bankJson['bank_id'] = $bank->id;
            $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $bank->currency_id, 'method_data' => json_encode($bankJson)])->first();

            if (!empty($currencyPaymentMethod)) {
        
                if (isset($bank->file->filename) && !empty($bank->file->filename)) {
                    $fileLocation = public_path('uploads/files/bank_logos/' . $bank->file->filename);
                    $fileThumbLocation = public_path('uploads/files/bank_logos/thumb/' . $bank->file->filename);

                    if (file_exists($fileLocation)) {
                        unlink($fileLocation);
                    }
                    if (file_exists($fileThumbLocation)) {
                        unlink($fileThumbLocation);
                    }
                    File::find($bank->file->id)->delete();
                }
                $bank->delete();
                $currencyPaymentMethod->delete();
                return response()->json([
                    'status'  => true,
                    'type'    => 'success',
                    'message' => __('The :x has been successfully deleted.', ['x' => __('bank')])
                ]);
            } else {
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => __('The :x cannot be deleted.', ['x' => __('bank')])
                ]);
            }
        } else {
            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => __('The :x cannot be deleted.', ['x' => __('bank')])
            ]);
        }
    }

    // Add MobileMoney
    public function addMobileMoney(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'currency_id' => 'required',
            'paymentMethod' => 'required',
            'default' => 'required',
            'holder_name' => 'required',
            'merchant_code' => 'required',
            'mobilemoney_name' => 'required',
            'mobilemoney_number' => 'required',
            'country' => 'required',
        ]);

        if ($validation->passes()) {
            try {
                DB::beginTransaction();

                if ($request->hasFile('mobilemoney_logo')) {
                    $mobilemoney_logo = $request->file('mobilemoney_logo');
                    if (isset($mobilemoney_logo)) {
                        $response = uploadImage($mobilemoney_logo, 'public/uploads/files/mobilemoney_logos/', '120*80', null, '70*70');
        
                        if ($response['status'] === true) {
                            $fileName = $response['file_name'];
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'status'  => 500,
                                'message' => $response['message']
                            ]);
                        }

                        $fields = [
                            'admin_id' => auth('admin')->user()->id,
                            'user_id' => null,
                            'ticket_id' => null,
                            'ticket_reply_id' => null,
                            'filename' => $fileName,
                            'originalname' => $mobilemoney_logo->getClientOriginalName(),
                            'type' => strtolower($mobilemoney_logo->getClientOriginalExtension())
                        ];
                        $file = File::add($fields);
                    }
                }

                $fileId = !empty($request->file('mobilemoney_logo')) && !empty($file) ? $file->id : null;

                $fields = [
                    'admin_id' => auth('admin')->user()->id,
                    'currency_id' => $request->currency_id,
                    'file_id' => $fileId,
                    'country_id' => $request->country,
                    'mobilemoney_name' => $request->mobilemoney_name,
                    'mobilemoney_number' => $request->mobilemoney_number,
                    'holder_name' => $request->holder_name,
                    'merchant_code' => $request->merchant_code,
                    'is_default' => $request->default
                ];

                $mobileMoney = \App\Models\MobileMoney::add($fields);

                if ($request->status == 'Active') {
                    $activatedFor = json_encode(['deposit' => '']);
                } else {
                    $activatedFor = json_encode(['' => '']);
                }

                $mobileMoneyJson = [];
                $mobileMoneyJson['mobilemoney_id'] = $mobileMoney->id;
                $methodData = json_encode($mobileMoneyJson);

                $fields = [
                    'currency_id' => $request->currency_id,
                    'method_id' => $request->paymentMethod,
                    'activated_for' => $activatedFor,
                    'method_data' => $methodData
                ];
                $currencyPaymentMethod = CurrencyPaymentMethod::add($fields);

                if ($mobileMoney->is_default == 'Yes') {
                    \App\Models\MobileMoney::where(['is_default' => 'Yes', 'currency_id' => $mobileMoney->currency_id])->where('id', '!=', $mobileMoney->id)->update(['is_default' => 'No']);
                }

                DB::commit();
                return response()->json([
                    'status'  => 200,
                    'message' => __('The :x has been successfully saved.', ['x' => __('mobile money')])
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status'  => 500,
                    'message' => $e->getMessage(),
                ]);
            }
        } else {
            return response()->json([
                'status'  => 500,
                'message' => $validation->errors()->all(),
            ]);
        }
    }

    //Update MobileMoney
    public function updateMobileMoney(Request $request)
    {
        
        $validation = Validator::make($request->all(), [
            'currency_id'             => 'required',
            'mobilemoney_id'          => 'required',
            'currencyPaymentMethodId' => 'required',
            'paymentMethod'           => 'required',
            'default'                 => 'required',
            'holder_name'             => 'required',
            'merchant_code'           => 'required',
            'mobilemoney_name'        => 'required',
            'mobilemoney_number'      => 'required',
            'country'                 => 'required',
        ]);

        if ($validation->passes()) {

            try {
                DB::beginTransaction();
                if ($request->hasFile('mobilemoney_logo')) {
                    $mobilemoney_logo = $request->file('mobilemoney_logo');
                    if (isset($mobilemoney_logo)) {
                        $oldFileName = File::where('id', $request->file_id)->value('filename');
                       
                        $response = uploadImage($mobilemoney_logo, 'public/uploads/files/mobilemoney_logos/', '120*80', $oldFileName, '70*70');

                        if ($response['status'] === true) {
                            $fileName = $response['file_name'];
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'status'  => 500,
                                'message' => $response['message']
                            ]);
                        }

                        $updates = [
                            'admin_id' => auth('admin')->user()->id,
                            'filename' => $fileName,
                            'originalname' => $mobilemoney_logo->getClientOriginalName(),
                            'type' => strtolower($mobilemoney_logo->getClientOriginalExtension())
                        ];
                        $file = File::createOrUpdate(['id' => $request->file_id], $updates);
                        
                    }
                }

                $fileId = (!empty($request->file('mobilemoney_logo')) && !empty($file)) ? $file->id : null;

                $updates = [
                    'admin_id' => auth('admin')->user()->id,
                    'currency_id' => $request->currency_id,
                    'country_id' => $request->country,
                    'file_id' => $fileId,
                    'mobilemoney_name' => $request->mobilemoney_name,
                    'mobilemoney_number' => $request->mobilemoney_number,
                    'holder_name' => $request->holder_name,
                    'merchant_code' => $request->merchant_code,
                    'is_default' => $request->default
                ];

                if (is_null($fileId)) {
                    unset($updates['file_id']);
                }
                $mobileMoney = \App\Models\MobileMoney::updateMobileMoney(['id' => $request->mobilemoney_id], $updates);

                if ($request->status == 'Active') {
                    $activatedFor = json_encode(['deposit' => '']);
                } else {
                    $activatedFor = json_encode(['' => '']);
                }
                $mobileMoneyJson = [];
                $mobileMoneyJson['mobilemoney_id']  = $mobileMoney->id;
                $methodData = json_encode($mobileMoneyJson);

                $updates = [
                    'currency_id' => $request->currency_id,
                    'method_id' => $request->paymentMethod,
                    'activated_for' => $activatedFor,
                    'method_data' => $methodData
                ];
                CurrencyPaymentMethod::updateCurrencyPaymentMethod(['id' => $request->currencyPaymentMethodId], $updates);

                if ($mobileMoney->is_default == 'Yes') {
                    \App\Models\MobileMoney::where(['is_default' => 'Yes', 'currency_id' => $mobileMoney->currency_id])->where('id', '!=', $mobileMoney->id)->update(['is_default' => 'No']);
                }
                DB::commit();
                return response()->json([
                    'status'  => 200,
                    'message' => __('The :x has been successfully saved.', ['x' => __('mobile money')]),
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status'  => 500,
                    'message' => $e->getMessage(),
                ]);
            }
            
        } else {
            return response()->json([
                'status'  => 500,
                'message' => $validation->errors()->all(),
            ]);
        }
    }

    public function getMobileMoneyCpmId(Request $request)
    {
        $mobileMoney = \App\Models\MobileMoney::where(['id' => $request->mobilemoney_id])->first();

        $mobileMoneyJson = [];
        $mobileMoneyJson['mobilemoney_id'] = $mobileMoney->id;
        $cpm = CurrencyPaymentMethod::where(['currency_id' => $request->currency_id, 'method_data' => json_encode($mobileMoneyJson)])->first(['id', 'activated_for']);

        $data = [];
        if ($cpm) {
            $data['status'] = true;
            $data['cpmId'] = $cpm->id;
            $data['cpmActivatedFor']  = $cpm->activated_for;
            $data['is_default'] = $mobileMoney->is_default;
            $data['holder_name'] = $mobileMoney->holder_name;
            $data['mobilemoney_name'] = $mobileMoney->mobilemoney_name;
            $data['mobilemoney_number'] = $mobileMoney->mobilemoney_number;
            $data['country_id'] = $mobileMoney->country_id;
            $data['merchant_code']    = $mobileMoney->merchant_code;

            if (!empty($mobileMoney->file_id)) {
                $data['mobilemoney_logo'] = $mobileMoney->file->filename;
                $data['file_id'] = $mobileMoney->file_id;
            }
        } else {
            $data['status'] = false;
            $data['cpmId'] = __('There are no payment methods available for this currency.');
        }

        return $data;
        exit();
    }

    // elete MobileMoney
    public function deleteMobileMoney(Request $request)
    {
        $transaction = Transaction::where(['mobilemoney_id' => $request->mobilemoney_id])->exists();
        
        if ($transaction) {
            return response()->json([
                'status' => false,
                'type' => 'error',
                'message' => __('Transactions have already been processed with this MobileMoney, cannot be deleted.')
            ]);
        }

        $mobileMoney = \App\Models\MobileMoney::with('file:id,filename')->find($request->mobilemoney_id);

        if (!empty($mobileMoney)) {
            try {
                DB::beginTransaction();
                $mobileMoneyJson = [];
                $mobileMoneyJson['mobilemoney_id'] = $mobileMoney->id;
                $conditions = ['currency_id' => $mobileMoney->currency_id, 'method_data' => json_encode($mobileMoneyJson)];
                $currencyPaymentMethod = CurrencyPaymentMethod::deleteCurrencyPaymentMethod($conditions);
                $currencyPaymentMethod = json_decode($currencyPaymentMethod);

                if ($currencyPaymentMethod->status == 200) {
                    if (isset($mobileMoney->file->filename) && !empty($mobileMoney->file->filename)) {
                        $location = public_path('uploads/files/mobilemoney_logos/' . $mobileMoney->file->filename);
                        $thumbLocation = public_path('uploads/files/mobilemoney_logos/thumb/' . $mobileMoney->file->filename);
        
                        if (file_exists($location)) {
                            unlink($location);
                        }
                        if (file_exists($thumbLocation)) {
                            unlink($thumbLocation);
                        }
                        File::find($mobileMoney->file->id)->delete();
                    }
                    $mobileMoney->delete();
                    DB::commit();
                    return response()->json([
                        'status'  => 200,
                        'type'    => 'success',
                        'message' => __('The :x has been successfully deleted.', ['x' => __('mobile money')])
                    ]);
                } else {
                    return response()->json([
                        'status'  => false,
                        'type'    => 'error',
                        'message' => __('The :x cannot be deleted.', ['x' => __('mobile money')])
                    ]);
                }
            } catch (Exception $e) {
                DB::rollback();
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => $e->getMessage(),
                ]);
            }   

        } else {
            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => __('The :x cannot be deleted.', ['x' => __('mobile money')])
            ]);
        }
    }
}
