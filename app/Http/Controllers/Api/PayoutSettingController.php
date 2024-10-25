<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DB, Exception;
use App\Models\{Country,
    PaymentMethod,
    PayoutSetting,
    Currency
};

class PayoutSettingController extends Controller
{
    public $successStatus      = 200;
    public $unsuccessStatus    = 403;
    public $unauthorisedStatus = 401;

    public function index()
    {
        $user_id = request('user_id');
        $payout_setting_id = request('payout_setting_id');
        $payoutSettings = PayoutSetting::with(['paymentMethod:id,name','currency:id,code'])
            ->where(['user_id' => $user_id])
            ->where(function ($query) use ($payout_setting_id) {
                if (!is_null($payout_setting_id)) {
                    $query->where(['id' => $payout_setting_id]);
                }
            })->get();
        $success['status'] = $this->successStatus;
        return response()->json(['success' => $success, 'payoutSettings' => $payoutSettings,], $this->successStatus);
    }

    public function delete()
    {
        try {
            DB::beginTransaction();
            $payoutSetting       = PayoutSetting::where(['user_id' => request('user_id'), 'id' => request('payout_setting_id')])->first();
            if (!empty($payoutSetting)) {
                $payoutSetting->delete();
            }
            $success['status']   = $this->successStatus;
            $success['message']  = __('The :x has been successfully deleted.', ['x' => __('withdrawal settings')]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $success['status']         = $this->unsuccessStatus;
            $success['exception_msg']  = $e->getMessage();
            $success['message']        = "Something went wrong!";
        }
        return response()->json(['success' => $success,], $this->successStatus);
    }
    public function store()
    {
        try {
            DB::beginTransaction();
            $paymentMethodType = request('paymentmethod');
            $user_id = request('user_id');

            $payoutSetting = new PayoutSetting();
            $payoutSetting->type = $paymentMethodType;
            $payoutSetting->user_id = $user_id;

            if ($paymentMethodType == Bank) {
                $payoutSetting->account_name        = request('account_name');
                $payoutSetting->account_number      = request('account_number');
                $payoutSetting->swift_code          = request('swift_code');
                $payoutSetting->bank_name           = request('bank_name');
                $payoutSetting->bank_branch_name    = request('branch_name');
                $payoutSetting->bank_branch_city    = request('branch_city');
                $payoutSetting->bank_branch_address = request('branch_address');
                $payoutSetting->country             = request('country');
            } else if ($paymentMethodType == Paypal) {
                $payoutDuplicateEmailCheck = PayoutSetting::where(['user_id' => $user_id, 'email' => request('email')])->exists();
                if ($payoutDuplicateEmailCheck) {
                    $success['status']   = $this->unauthorisedStatus;
                    $success['reason']   = 'duplicate-email';
                    $success['message']  = "You can not add same email again as withdrawal setting.";
                    return response()->json(['success' => $success]);
                }
                $payoutSetting->email = request('email');

            } else if ($paymentMethodType == Crypto) {

                if (empty(request('crypto_address'))) {
                    $success['status']   = $this->unauthorisedStatus;
                    $success['reason']   = 'empty-crypto-address';
                    $success['message']  = "Crypto address field is required.";
                    return response()->json(['success' => $success]);

                } else if (empty(request('currency'))) {
                    $success['status']   = $this->unauthorisedStatus;
                    $success['reason']   = 'empty-currency-id';
                    $success['message']  = "Please select a currency for crypto withdrawal.";
                    return response()->json(['success' => $success]);
                }
                $duplicateAddressCheck = PayoutSetting::where(['user_id' => request('user_id'), 'crypto_address' => request('crypto_address')])->exists();
                if ($duplicateAddressCheck) {
                    $success['status'] = $this->unauthorisedStatus;
                    $success['reason'] = 'duplicate-crypto-address';
                    $success['message'] = "Crypto address is already exist.";
                    return response()->json(['success' => $success]);
                }

                $payoutSetting->currency_id = request('currency');
                $payoutSetting->crypto_address = request('crypto_address');
            }

            $payoutSetting->save();

            $success['status'] = $this->successStatus;
            $success['message'] = "Payout Setting Added Successfully!";
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $success['status'] = $this->unsuccessStatus;
            $success['exception_msg'] = $e->getMessage();
            $success['message'] = "Sorry, Unexpected error occurred";
        }
        return response()->json(['success' => $success,], $this->successStatus);
    }
    public function update()
    {
        try {
            DB::beginTransaction();
            $payout_setting_id = request('payout_setting_id');
            $user_id = request('user_id');
            $payoutSetting = PayoutSetting::where(['user_id' => $user_id])
                ->where(['id' => $payout_setting_id])
                ->first();

            $paymentMethodType = $payoutSetting->type;
            if ($paymentMethodType == Bank) {
                $payoutSetting->account_name        = request('account_name');
                $payoutSetting->account_number      = request('account_number');
                $payoutSetting->swift_code          = request('swift_code');
                $payoutSetting->bank_name           = request('bank_name');
                $payoutSetting->bank_branch_name    = request('branch_name');
                $payoutSetting->bank_branch_city    = request('branch_city');
                $payoutSetting->bank_branch_address = request('branch_address');
                $payoutSetting->country             = request('country');
            } else if ($paymentMethodType == Paypal) {
                $payoutDuplicateEmailCheck = PayoutSetting::where(['user_id' => $user_id, 'email' => request('email')])
                                            ->where(function($query) use ($payout_setting_id) {
                                                $query->where('id', '!=', $payout_setting_id);
                                            })->exists();
                if ($payoutDuplicateEmailCheck) {
                    $success['status'] = $this->unauthorisedStatus;
                    $success['reason'] = 'duplicate-email';
                    $success['message'] = "Can not update an email that already exist.";
                    return response()->json(['success' => $success]);
                }
                $payoutSetting->email = request('email');
            } else if ($paymentMethodType == Crypto) {

                $duplicateAddressCheck = PayoutSetting::where(['user_id' => $user_id, 'crypto_address' => request('crypto_address')])
                                            ->where(function($query) use ($payout_setting_id) {
                                                $query->where('id', '!=', $payout_setting_id);
                                            })->exists();
                if ($duplicateAddressCheck) {
                    $success['status'] = $this->unauthorisedStatus;
                    $success['reason'] = 'duplicate-crypto-address';
                    $success['message'] = "Crypto address is already exist.";
                    return response()->json(['success' => $success]);
                }

                $payoutSetting->currency_id = request('currency');
                $payoutSetting->crypto_address = request('crypto_address');
            }

            $payoutSetting->save();

            $success['status']   = $this->successStatus;
            $success['message']  = __('The :x has been successfully updated.', ['x' => __('withdrawal settings')]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $success['status']         = $this->unsuccessStatus;
            $success['exception_msg']  = $e->getMessage();
            $success['message']        = "Sorry, Unexpected error occurred";
        }
        return response()->json(['success' => $success,], $this->successStatus);
    }

    public function paymentMethods()
    {
        $paymentMethods    = PaymentMethod::whereNotIn('name', ['Mts', 'Stripe', '2Checkout', 'PayUMoney', 'Coinpayments', 'Payeer','BlockIo'])
            ->where(['status' => 'Active'])
            ->get(['id', 'name']);

        $success['status']  = $this->successStatus;
        return response()->json(['success' => $success, 'paymentMethods' => $paymentMethods,], $this->successStatus);
    }

    public function getAllCountries()
    {
        $success['countries'] = Country::get(['id', 'name']);
        $success['status']    = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function getWithdrawalCryptoCurrencies()
    {
        $userId = request('user_id');

        $success['currencies'] = Currency::whereHas('wallet', function($q) use ($userId)
        {
            $q->where(['user_id' => $userId]);
        })
        ->whereHas('fees_limit', function($query) 
        {
            $query->where(['has_transaction' => 'yes', 'transaction_type_id' => Withdrawal]);
        })
        ->where(['status' => 'Active', 'type' => 'crypto'])
        ->get(['id', 'code']);

        $success['status']    = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }
}
