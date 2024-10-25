<?php

/**
 * @package DepositMoneyService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 21-12-2022
 */



namespace App\Services;

use App\Exceptions\Api\V2\{
    PaymentFailedException,
    DepositMoneyException
};
use App\Http\Helpers\Common;
use App\Http\Resources\V2\FeesResource;
use App\Models\{
    CurrencyPaymentMethod,
    PaymentMethod,
    FeesLimit,
    Deposit,
    Wallet,
    Bank,
    Currency
};
use Illuminate\Support\Facades\DB;
use App\Services\Mail\Deposit\NotifyAdminOnDepositMailService;

class DepositMoneyService
{
    /**
     * @var Common
     */
    private $helper;

    public function __construct(Common $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get the currencies list for deposit
     *
     * @return void
     */
    public function getSelfCurrencies()
    {
        $result = [
            "currencies" => []
        ];

        $defaultWallet = Wallet::where(
            [
                'user_id' => auth()->id(),
                'is_default' => 'Yes'
            ]
        )->value('currency_id');

        Currency::join("fees_limits", "fees_limits.currency_id", "currencies.id")
            ->where("fees_limits.has_transaction", "Yes")
            ->where("fees_limits.transaction_type_id", Deposit)
            ->get()
            ->map(function ($item) use (&$result) {
                $result["currencies"][$item->currency_id] = [
                    "id" => $item->currency_id,
                    "code" => $item->code,
                    "type" => $item->type
                ];
            });
        $result["currencies"] = array_values($result["currencies"]);
        $result["default"] = $defaultWallet;
        return $result;
    }



    public function getBanklist($currencyId)
    {
        $banks = Bank::where(['currency_id' => $currencyId])->get(['id', 'bank_name', 'is_default', 'account_name', 'account_number']);
        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $currencyId)
            ->where('activated_for', 'like', "%deposit%")
            ->where('method_data', 'like', "%bank_id%")
            ->get(['method_data']);

        $bankList = $this->bankList($banks, $currencyPaymentMethods);

        if (count($bankList) == 0) {
            throw new DepositMoneyException(__("Banks does not exist for selected currency."));
        }
        return $bankList;
    }

    public function bankList($banks, $currencyPaymentMethods)
    {
        $selectedBanks = [];
        foreach ($banks as $bank) {
            foreach ($currencyPaymentMethods as $cpm) {
                if (!empty($cpm->method_data)) {
                    $methodData = json_decode($cpm->method_data);
                }
                if (isset($methodData->bank_id) && $bank->id == $methodData->bank_id) {
                    $selectedBanks[] = [
                        'id' => $bank->id,
                        'bank_name' => $bank->bank_name,
                        'is_default' => $bank->is_default,
                        'account_name' => $bank->account_name,
                        'account_number' => $bank->account_number,
                    ];
                }
            }
        }
        return $selectedBanks;
    }


    public function getPaymentMethods($currencyId, $currencyType, $transactionType, $platform = 'mobile')
    {
        $condition = ($currencyType == 'fiat') ? getPaymoneySettings('payment_methods')[$platform]['fiat']['deposit'] : getPaymoneySettings('payment_methods')[$platform]['crypto']['deposit'];

        $feesLimits = FeesLimit::whereHas('currency', function ($q) {
            $q->where('status', '=', 'Active');
        })
            ->whereHas('payment_method', function ($q) use ($condition) {
                $q->whereIn('id', $condition)->where('status', '=', 'Active');
            })
            ->where(['transaction_type_id' => $transactionType, 'has_transaction' => 'Yes', 'currency_id' => $currencyId])
            ->get(['payment_method_id']);

        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $currencyId)->where('activated_for', 'like', "%deposit%")->get(['method_id']);
        $currencyPaymentMethodFeesLimitCurrenciesList = $this->currencyPaymentMethodFeesLimitCurrencies($feesLimits, $currencyPaymentMethods);
        return $currencyPaymentMethodFeesLimitCurrenciesList;
    }

    public function currencyPaymentMethodFeesLimitCurrencies($feesLimits, $currencyPaymentMethods)
    {
        $selectedCurrencies = [];
        foreach ($feesLimits as $feesLimit) {
            foreach ($currencyPaymentMethods as $currencyPaymentMethod) {
                if ($feesLimit->payment_method_id == $currencyPaymentMethod->method_id) {
                    $selectedCurrencies[$feesLimit->payment_method_id]['id']   = $feesLimit->payment_method_id;
                    $selectedCurrencies[$feesLimit->payment_method_id]['name'] = optional($feesLimit->payment_method)->name;
                    $selectedCurrencies[$feesLimit->payment_method_id]['alias'] = strtolower(preg_replace("/\s+/", "", optional($feesLimit->payment_method)->name));
                }
            }
        }
        return $selectedCurrencies;
    }


    /**
     * Validate deposit amount data
     *
     * @param int $currencyId
     * @param float $amount
     * @param int $paymentMethodId
     *
     * @return void
     *
     * @throws DepositMoneyException
     */
    public function validateDepositable($currencyId, $amount, $paymentMethodId)
    {
        $paymentMethodName = PaymentMethod::where('id', $paymentMethodId)->value('name');
        $paymentMethodAlias = strtolower(preg_replace("/\s+/", "", $paymentMethodName));

        $feesDetails = $this->helper->transactionFees($currencyId, $amount, Deposit, $paymentMethodId);
        $this->helper->amountIsInLimit($feesDetails, $amount);

        $feesArray = [
            'paymentMethodName' => $paymentMethodName,
            'paymentMethodAlias' => $paymentMethodAlias,
            'min' => $feesDetails->min_limit,
            'max' => $feesDetails->max_limit,
            'payment_method' => $paymentMethodId
        ];

        return array_merge((new FeesResource($feesDetails))->toArray(request()), $feesArray) ;

    }


    /**
     * Calculate total amount
     *
     * @param int $currencyId
     * @param float $amount
     * @param int $paymentMethodId
     *
     * @return float
     */
    public function getTotalAmount($amount, $currencyId, $paymentMethodId): float
    {
        $feesDetails = $this->helper->transactionFees($currencyId, $amount, Deposit, $paymentMethodId);
        $this->helper->amountIsInLimit($feesDetails, $amount);
        return $feesDetails->total_amount;
    }

    /**
     * Process after payment has been done
     *
     * @param int $currencyId
     * @param int $paymentMethodId
     * @param float $totalAmount
     * @param float $amount
     * @param array $response
     *
     * @return array
     *
     * @throws PaymentFailedException
     */
    public function processPaymentConfirmation(
        $currencyId,
        $paymentMethodId,
        $totalAmount,
        $amount,
        $user_id,
        $uuid,
        $response
    ) {
        try {
            DB::beginTransaction();
            if ($paymentMethodId == Bank) {
                $deposit = Deposit::success(
                    $currencyId,
                    $paymentMethodId,
                    $user_id,
                    ["totalAmount" => $totalAmount, "amount" => $amount, 'uuid' => $uuid],
                    "Pending",
                    "bank",
                    $response["attachment"] ?? null,
                    $response["bank"] ?? null
                );
                $response = miniCollection($response)->only(["action", "message"]);
            } elseif ($paymentMethodId == Coinbase || $paymentMethodId == Payeer || $paymentMethodId == Coinpayments) {

                $deposit = Deposit::success(
                    $currencyId,
                    $paymentMethodId,
                    $user_id,
                    ["totalAmount" => $totalAmount, "amount" => $amount, 'uuid' => $uuid],
                    "Pending",
                );
                $response = miniCollection($response)->only(["action", "message"]);

            } else {
                $deposit = Deposit::success(
                    $currencyId,
                    $paymentMethodId,
                    $user_id,
                    ["totalAmount" => $totalAmount, "amount" => $amount, 'uuid' => $uuid],
                );
            }

            DB::commit();

            (new NotifyAdminOnDepositMailService)->send($deposit["deposit"], ['type' => 'deposit', 'medium' => 'email']);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw new PaymentFailedException($th->getMessage());
        }
        return array_merge(["message" => __("The deposit has been successfully completed.")], $deposit);
    }


    /**
     * Get palpal credentials
     *
     * @param int $bankId
     *
     * @return Bank
     *
     * @throws DepositMoneyException
     */
    public function getBankDetails($bankId)
    {
        $bank = Bank::with("file:id,filename")->select("account_name", "account_number", "bank_name", "file_id")->firstWhere("id", $bankId);
        if (is_null($bank)) {
            throw new DepositMoneyException(__("Bank details not found."));
        }
        if ($bank->file_id && optional($bank->file)->filename && file_exists(public_path('uploads/files/bank_logos/' . $bank->file->filename))) {
            $bank->logo = $bank->file->filename;
        }
        return $bank;
    }


    /**
     * Get palpal credentials
     *
     * @param int $currencyId
     * @param string $type
     *
     * @return array
     *
     * @throws DepositMoneyException
     */
    public function getPaypalInfo($currencyId, $type)
    {
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => Paypal])
            ->where('activated_for', 'like', "%" . $type . "%")
            ->first(['method_data']);

        if (is_null($currencyPaymentMethod)) {
            throw new DepositMoneyException(__("Paypal payment method not found."));
        }

        return json_decode($currencyPaymentMethod->method_data);
    }

    public function processGatewayPayment($currencyId, $paymentMethodId, $amount)
    {
        $totalAmount = $this->getTotalAmount($amount, $currencyId, $paymentMethodId);

        $paymentMethodName = PaymentMethod::where('id', $paymentMethodId)->value('name');

        $paymentMethodAlias = strtolower(preg_replace("/\s+/", "", $paymentMethodName));

        $currency = $this->helper->getCurrencyObject(['id' => $currencyId], ['symbol', 'code', 'type']);

        $paymentData = [
            'currency_id' => $currencyId,
            'currencySymbol' => $currency->symbol,
            'currencyType' => $currency->type,
            'currencyCode' => $currency->code,
            'totalAmount' => $totalAmount,
            'total' => $totalAmount,
            'amount' => $amount,
            'user_id' => auth()->id(),
            'transaction_type' => Deposit,
            'payment_type' => 'deposit',
            'payment_method' => $paymentMethodId,
            'redirectUrl' => route('deposit.complete'),
            'cancel_url' => url('deposit'),
            'gateway' => $paymentMethodAlias,
            'uuid' => unique_code()
        ];

        if ($paymentMethodId == Bank) {
            $paymentData['banks'] = getBankList($currencyId, 'deposit');
        }

        return [
            'url' => gatewayPaymentUrl($paymentData)
        ];

    }


}
