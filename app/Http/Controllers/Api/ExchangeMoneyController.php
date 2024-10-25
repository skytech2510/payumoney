<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Currency,
    CurrencyExchange,
    FeesLimit,
    Wallet
};

class ExchangeMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    protected $helper;
    protected $exchange;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->exchange = new CurrencyExchange();
    }

    //Exchange Money Starts here
    public function getUserWalletsWithActiveAndHasTransactionCurrency()
    {
        $feesLimitCurrency                               = FeesLimit::where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $userCurrencyList                                = array_column(Wallet::where(['user_id' => request('user_id')])->get()->toArray(), 'currency_id');
        $userCurrencyList                                = Currency::whereIn('id', $userCurrencyList)->where(['status' => 'Active'])->get(['id', 'code', 'status']);
        $success['activeHasTransactionUserCurrencyList'] = $activeHasTransactionUserCurrencyList = $this->activeHasTransactionUserCurrencyList($userCurrencyList, $feesLimitCurrency);

        //Set default wallet as selected - starts
        $defaultWallet                      = Wallet::where(['user_id' => request('user_id'), 'is_default' => 'Yes'])->first(['currency_id']);
        $success['defaultWalletCurrencyId'] = $defaultWallet->currency_id;
        //Set default wallet as selected - ends

        $success['status'] = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Users Active, Has Transaction and Existing Currency Wallets/list
    public function activeHasTransactionUserCurrencyList($userCurrencyList, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        foreach ($userCurrencyList as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $aCurrency->status == 'Active' && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$aCurrency->id]['id']   = $aCurrency->id;
                    $selectedCurrency[$aCurrency->id]['code'] = $aCurrency->code;
                }
            }
        }
        return $selectedCurrency;
    }

    public function getWalletsExceptSelectedFromWallet()
    {
        $feesLimitCurrency = FeesLimit::where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);

        $activeCurrency = Currency::where('id', '!=', request('currency_id'))->where(['status' => 'Active'])->get(['id', 'code', 'status']);

        $currencyList = $this->currencyList($activeCurrency, $feesLimitCurrency, request('user_id'));

        if ($currencyList)
        {
            return response()->json([
                'currencies' => $currencyList,
                'status'     => true,
            ]);
        }
        else
        {
            return response()->json([
                'currencies' => null,
                'status'     => false,
            ]);
        }
    }

    public function currencyList($activeCurrency, $feesLimitCurrency, $user_id)
    {
        $selectedCurrency = [];
        foreach ($activeCurrency as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $aCurrency->status == 'Active' && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$aCurrency->id]['id']   = $aCurrency->id;
                    $selectedCurrency[$aCurrency->id]['code'] = $aCurrency->code;

                    $wallet = Wallet::where(['currency_id' => $aCurrency->id, 'user_id' => $user_id])->first(['balance']);
                    if (!empty($wallet))
                    {
                        $selectedCurrency[$aCurrency->id]['balance'] = isset($wallet->balance) ? $wallet->balance : 0.00;
                    }
                }
            }
        }
        return $selectedCurrency;
    }

    public function getBalanceOfFromAndToWallet()
    {
        $wallet = Wallet::where(['currency_id' => request('currency_id'), 'user_id' => request('user_id')])->first(['balance', 'currency_id']); //added by parvez - for wallet balance check
        if (!empty($wallet))
        {
            return response()->json([
                'status'       => true,
                'balance'      => number_format((float) $wallet->balance, 2, '.', ''),
                'currencyCode' => $wallet->currency->code,
            ]);
        }
        else
        {
            return response()->json([
                'status'       => false,
                'balance'      => null,
                'currencyCode' => null,
            ]);
        }
    }

    public function exchangeReview()
    {
        $amount     = request('amount');
        $fromWallet = request('currency_id');
        $user_id    = request('user_id');

        $wallet      = Wallet::where(['currency_id' => $fromWallet, 'user_id' => $user_id])->first(['currency_id', 'balance']);
        $feesDetails = FeesLimit::where(['transaction_type_id' => Exchange_From, 'currency_id' => $fromWallet])->first(['max_limit', 'min_limit', 'has_transaction', 'currency_id', 'charge_percentage', 'charge_fixed']);

        //Wallet Balance Limit Check Starts here
        if (@$feesDetails)
        {
            if ($feesDetails->has_transaction == 'No')
            {
                $success['reason']       = 'noHasTransaction';
                $success['currencyCode'] = $feesDetails->currency->code;
                $success['message']      = 'The currency' . ' ' . $feesDetails->currency->code . ' ' . 'fees limit is inactive';
                $success['status']       = '401';
                return response()->json(['success' => $success], $this->successStatus);
            }
            $checkAmount = $amount + $feesDetails->charge_fixed + $feesDetails->charge_percentage;
        }

        if (@$wallet)
        {
            if ((@$checkAmount) > (@$wallet->balance) || (@$wallet->balance < 0))
            {
                $success['reason']  = 'insufficientBalance';
                $success['message'] = "Sorry, not enough funds to perform the operation.";
                $success['status']  = '401';
                return response()->json(['success' => $success], $this->successStatus);
            }
        }

        //Code for Amount Limit starts here
        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['reason']          = 'minLimit';
                $success['minLimit']        = @$feesDetails->min_limit;
                $success['message']         = 'Minimum amount ' . formatNumber($feesDetails->min_limit);
                $success['wallet_currency'] = $wallet->currency->code;
                $success['status']          = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }
        else
        {
            if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
            {
                $success['reason']          = 'minMaxLimit';
                $success['minLimit']        = @$feesDetails->min_limit;
                $success['maxLimit']        = @$feesDetails->max_limit;
                $success['message']         = 'Minimum amount ' . formatNumber($feesDetails->min_limit) . ' and Maximum amount ' . formatNumber($feesDetails->max_limit);
                $success['wallet_currency'] = $wallet->currency->code;
                $success['status']          = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }

        return response()->json([
            'success' => $success,
        ]);
    }

    public function getCurrenciesExchangeRate(Request $request)
    {
        $toWalletCurrency = $this->helper->getCurrencyObject(['id' => request('toWallet')], ['exchange_from', 'code', 'rate', 'symbol']);
        if (!empty($toWalletCurrency)) {
            if ($toWalletCurrency->exchange_from == "api" && isEnabledExchangeApi()){
                $toWalletRate = getApiCurrencyRate(request('fromWalletCode'), $toWalletCurrency->code);
                if ($toWalletRate == 'error') {
                    $fromWalletCurrency = $this->helper->getCurrencyObject(['id' => request('fromWallet')], ['rate']);
                    $toWalletRate = getLocalCurrencyRate($fromWalletCurrency->rate, $toWalletCurrency->rate);
                }
            } else {
                $fromWalletCurrency = $this->helper->getCurrencyObject(['id' => request('fromWallet')], ['rate']);
                $toWalletRate = getLocalCurrencyRate($fromWalletCurrency->rate, $toWalletCurrency->rate);
            }

            $getAmountMoneyFormat             = $toWalletRate * request('amount');
            $formattedDestinationCurrencyRate = number_format($toWalletRate, 8, '.', '');
            $success['status']                = $this->successStatus;
            $success['toWalletRate']          = (float) $formattedDestinationCurrencyRate; // this was not necessary, but kept it as it creates confusion
            $success['toWalletRateHtml']      = (float) $formattedDestinationCurrencyRate; // this will not be shown as formatted as it creates confusion - when multiplying amount * currency rate
            $success['toWalletCode']          = $toWalletCurrency->code;
            $success['toWalletSymbol']        = $toWalletCurrency->symbol;
            $success['getAmountMoneyFormat']  = moneyFormat($toWalletCurrency->code, formatNumber($getAmountMoneyFormat)); //just for show, not taken for further processing
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            $success['status']         = $this->unauthorisedStatus;
            $success['toWalletRate']   = null;
            $success['toWalletCode']   = null;
            $success['toWalletSymbol'] = null;
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    public function reviewExchangeDetails()
    {
        $user_id                     = request('user_id');
        $amount                      = request('amount');
        $fromWalletValue             = request('fromWalletValue');
        $toWalletRate                = request('toWalletRate');
        $feesDetails                 = $this->helper->getFeesLimitObject([], Exchange_From, $fromWalletValue, null, null, ['charge_percentage', 'charge_fixed']);
        $feesChargePercentage        = $amount * (@$feesDetails->charge_percentage / 100);
        $totalFess                   = $feesChargePercentage + (@$feesDetails->charge_fixed);
        $getAmountMoneyFormat        = $toWalletRate * request('amount');
        $success['convertedAmnt']    = $getAmountMoneyFormat;
        $success['totalAmount']      = $amount + $totalFess;
        $success['totalFees']        = $totalFess;
        $success['totalFeesHtml']    = formatNumber($totalFess);
        $success['toWalletRateHtml'] = $toWalletRate;
        $fromCurrency                = $this->helper->getCurrencyObject(['id' => $fromWalletValue], ['code', 'symbol']);
        $success['fCurrencySymbol']  = $fromCurrency->symbol;
        $success['fCurrencyCode']    = $fromCurrency->code;
        $success['status']           = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function exchangeMoneyComplete()
    {
        $user_id              = request('user_id');
        $fromWalletValue      = request('fromWalletValue');
        $toWalletValue        = request('toWalletValue');
        $toWalletAmount       = request('toWalletAmount');
        $toWalletExchangeRate = request('toWalletExchangeRate');
        $fromWalletAmount     = request('fromWalletAmount');
        $totalFees            = request('totalFees');
        $uuid                 = unique_code();
        $fromWallet           = $this->helper->getUserWallet([], ['user_id' => $user_id, 'currency_id' => $fromWalletValue], ['id', 'currency_id', 'balance']);
        $toWallet             = $this->helper->getUserWallet([], ['user_id' => $user_id, 'currency_id' => $toWalletValue], ['id', 'balance']);
        $feesDetails          = $this->helper->getFeesLimitObject([], Exchange_From, $fromWalletValue, null, null, ['charge_percentage', 'charge_fixed']);

        $arr = [
            'unauthorisedStatus'        => $this->unauthorisedStatus,
            'user_id'                   => $user_id,
            'toWalletCurrencyId'        => $toWalletValue, //
            'fromWallet'                => $fromWallet,
            'toWallet'                  => $toWallet,
            'finalAmount'               => $toWalletAmount,
            'uuid'                      => $uuid,
            'destinationCurrencyExRate' => $toWalletExchangeRate,
            'amount'                    => $fromWalletAmount,
            'fee'                       => $totalFees,
            'charge_percentage'         => $feesDetails->charge_percentage,
            'charge_fixed'              => $feesDetails->charge_fixed,
            'formattedChargePercentage' => $fromWalletAmount * (@$feesDetails->charge_percentage / 100),
        ];

        //Get response
        $response = $this->exchange->processExchangeMoneyConfirmation($arr, 'mobile');
        if ($response['status'] != 200)
        {
            if (empty($response['exchangeCurrencyId']))
            {
                return response()->json([
                    'status'                              => false,
                    'exchangeMoneyValidationErrorMessage' => $response['ex']['message'],
                ]);
            }
            return response()->json([
                'status' => true,
            ]);
        }
        //
        return response()->json([
            'status' => true,
        ]);
    }
    //Exchange Money Ends here
}
