<?php

/**
 * @package ExchangeMoneyService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 30-11-2022
 */

namespace App\Services;

use App\Exceptions\Api\V2\ExchangeMoneyException;
use App\Http\Helpers\Common;
use App\Models\{
    CurrencyExchange,
    Currency,
    Wallet
};
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\Mail\ExchangeMoneyMailService;

class ExchangeMoneyService
{
    /**
     * Common
     *
     * @var Common
     */
    protected $helper;

    /**
     * Currencies needed in the lifecycle
     *
     * @var Currency
     */
    private $currencies = null;

     /**
     * CurrencyExchange needed to store database
     *
     * @var CurrencyExchange
     */
    private $exchange;


    /**
     * Construct the service class
     *
     * @param Common $helper
     *
     * @return void
     */
    public function __construct(Common $helper)
    {
        $this->helper = $helper;
        $this->exchange = new CurrencyExchange();
    }


    /**
     * Get available currencies of the user
     *
     * @return array
     */
    public function getSelfCurrencies()
    {
        $result = [
            "currencies" => []
        ];

        Wallet::with("currency:id,code,type")
            ->where("user_id", auth()->id())
            ->whereHas("active_currency")
            ->join("fees_limits", "fees_limits.currency_id", "wallets.currency_id")
            ->where("fees_limits.has_transaction", "Yes")
            ->where("fees_limits.transaction_type_id", Exchange_From)
            ->get()
            ->map(function ($item) use (&$result) {
                if ($item->is_default == "Yes") {
                    $result["default"] = $item->currency_id;
                }
                $result["currencies"][$item->currency_id] = [
                    "id" => $item->currency_id,
                    "code" => optional($item->currency)->code,
                    "balance" => $item->balance
                ];
                return $item;
            });
        $result["currencies"] = array_values($result["currencies"]);
        return $result;
    }


    /**
     * Exchange amount limit check
     *
     * @param double $amount
     * @param int $currencyId
     *
     * @return array
     *
     * @throws Exception
     */
    public function amountLimitCheck($amount, $currencyId)
    {
        $wallet = $this->helper->getWallet(auth()->id(), $currencyId);

        $feesDetails = $this->helper->transactionFees($currencyId, $amount, Exchange_From);
        // Check if the wallet is in required limit
        $this->helper->amountIsInLimit($feesDetails, $amount);

        $this->helper->checkWalletAmount(auth()->id(), $currencyId, $feesDetails->total_amount);

        return $feesDetails;

    }


    /**
     * Returns from source and destination wallets
     *
     * @param int $fromCurrrency
     * @param int $toCurrency
     *
     * @return array
     *
     * @throws ExchangeMoneyException
     */
    public function getWallets($fromCurrency, $toCurrency)
    {
        $wallets = Wallet::with("currency")->whereIn("currency_id", [$fromCurrency, $toCurrency])->where("user_id", auth()->id())->get();

        if (is_null($wallets)) {
            throw new ExchangeMoneyException(__("Wallets connected to the currencies are not found."));
        }

        $fromWallet = $wallets->where("currency_id", $fromCurrency)->first();

        $toWallet = $wallets->where("currency_id", $toCurrency)->first();

        if (is_null($fromWallet)) {
            throw new ExchangeMoneyException(__("Source wallet is not found."), [
                "destination" => [
                    "balance" => formatNumber($toWallet->balance, $toCurrency),
                    "currency" => optional($toWallet->currency)->code
                ]
            ]);
        }

        if (is_null($toWallet)) {
            throw new ExchangeMoneyException(__("Destination wallet is not found."), [
                "source" => [
                    "balance" => formatNumber($fromWallet->balance, $fromCurrency),
                    "currency" => optional($fromWallet->currency)->code
                ]
            ]);
        }

        return [
            "source" => [
                "balance" => formatNumber($fromWallet->balance, $fromCurrency),
                "currency" => optional($fromWallet->currency)->code
            ],
            "destination" => [
                "balance" => formatNumber($toWallet->balance, $toCurrency),
                "currency" => optional($toWallet->currency)->code
            ]
        ];
    }


    /**
     * Returns available destination wallets
     *
     * @param int $sourceCurrency
     *
     * @return array
     *
     * @throws ExchangeMoneyException
     */
    public function getAvailableDestinationWallets($sourceCurrency)
    {
        $wallets = Currency::select("currencies.id", "currencies.code", "wallets.balance")
            ->where("currencies.id", "!=", $sourceCurrency)
            ->where("currencies.status", "Active")
            ->leftJoin("fees_limits", "fees_limits.currency_id", "currencies.id")
            ->leftJoin("wallets", "wallets.currency_id", "currencies.id")
            ->where("fees_limits.transaction_type_id", Exchange_From)
            ->where("fees_limits.has_transaction", "Yes")
            ->where("wallets.user_id", auth()->id())
            ->get();

        if (count($wallets) == 0) {
            throw new ExchangeMoneyException(__("No destination wallet available."));
        }
        return $wallets;
    }


    /**
     * Get source currency to exchange currency exchange rates
     *
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @param double $amount
     *
     * @return array
     *
     * @throws ExchangeMoneyException
     */
    public function getCurrenciesExchangeRates($fromCurrencyId, $toCurrencyId, $amount)
    {
        $currencies = $this->getCurrencies([$fromCurrencyId, $toCurrencyId]);;

        $fromCurrency = $currencies->where("id", $fromCurrencyId)->first();

        $toCurrency = $currencies->where("id", $toCurrencyId)->first();

        if (is_null($fromCurrency)) {
            throw new ExchangeMoneyException(__("Source currency not found."));
        }

        if (is_null($toCurrency)) {
            throw new ExchangeMoneyException(__("Destination wallet currency not found."));
        }

        if ($toCurrency->exchange_from == "api" && settings("exchange_enabled_api") != "Disabled" && ((settings("exchange_enabled_api") == "currency_converter_api_key" && !empty(settings("currency_converter_api_key")))  || (settings("exchange_enabled_api") == "exchange_rate_api_key" && !empty(settings("exchange_rate_api_key"))))) {
            $conversionRate = getCurrencyRate($fromCurrency->code, $toCurrency->code);
        } else {
            $defaultCurrency = Currency::where("default", 1)->first();
            $conversionRate = ($defaultCurrency->rate / $fromCurrency->rate) * $toCurrency->rate;
        }

        $totalAmount = $conversionRate * $amount;
        $formattedAmount = number_format($conversionRate, 8, ".", "");

        return [
            "rate" => (float) $formattedAmount,
            "code" => $toCurrency->code,
            "symbol" => $toCurrency->symbol,
            "total_amount" => formatNumber($totalAmount),
            "formatted_amount" => moneyFormat($toCurrency->code, formatNumber($totalAmount))
        ];
    }


    /**
     * Review exchange request
     *
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @param double $amount
     *
     * @return array
     *
     * @throws ExchangeMoneyException
     */
    public function reviewExchangeRequest($fromCurrencyId, $toCurrencyId, $amount)
    {
        $rateDetails = $this->getCurrenciesExchangeRates($fromCurrencyId, $toCurrencyId, $amount);

        $rate = $rateDetails["rate"];

        $userId = auth()->id();

        $feesDetails = $this->helper->transactionFees($fromCurrencyId, $amount, Exchange_From);

        $this->helper->amountIsInLimit($feesDetails, $amount);

        $this->helper->checkWalletAmount($userId, $fromCurrencyId, $feesDetails->total_amount);

        $toCurrency = $this->getCurrencies($toCurrencyId);
        $destinationAmount = $amount * $rate;

        return [
            "destination_amount" => $destinationAmount,
            "destination_amount_formatted" => moneyFormat($toCurrency->code, formatNumber($destinationAmount)),
            "total_amount" => $feesDetails->total_amount,
            "total_amount_formatted" => moneyFormat(optional($feesDetails->currency)->code, formatNumber($feesDetails->total_amount)),
            "total_fees" => $feesDetails->total_fees,
            "total_fees_formatted" => moneyFormat(optional($feesDetails->currency)->code, formatNumber($feesDetails->total_fees)),
            "exchange_rate" => $rate,
            "exchange_rate_formatted" => formatNumber($rate)
        ];
    }


    public function exchangeMoney($fromCurrencyId, $toCurrencyId, $amount)
    {
        $user_id = auth()->id();

        $feesDetails = $this->helper->transactionFees($fromCurrencyId, $amount, Exchange_From);

        $this->helper->amountIsInLimit($feesDetails, $amount);

        $this->helper->checkWalletAmount($user_id, $fromCurrencyId, $feesDetails->total_amount);

        $fromWallet = $this->helper->getWallet($user_id, $fromCurrencyId, ['id', 'currency_id', 'balance']);

        $rate = $this->getRate($fromCurrencyId, $toCurrencyId, $amount);

        $destinationAmount = $amount * $rate;

        $toWallet = $this->firstOrCreateWallet($user_id, $toCurrencyId);

        $arr = [
            "user_id" => $user_id,
            "toWalletCurrencyId" => $toCurrencyId, //
            "fromWallet" => $fromWallet,
            "toWallet" => $toWallet,
            "finalAmount" => $destinationAmount,
            "uuid" => unique_code(),
            "destinationCurrencyExRate" => $rate,
            "amount" => $amount,
            "fee" => $feesDetails->total_fees,
            "charge_percentage" => $feesDetails->charge_percentage,
            "charge_fixed" => $feesDetails->charge_fixed,
            "formattedChargePercentage" => $feesDetails->fees_percentage,
        ];

        try {

            DB::beginTransaction();

            $currencyExchange = $this->createExchange($arr, $toWallet->id);

            $this->creatFromTransaction($arr, $currencyExchange->id);

            $this->creatToTransaction($arr, $currencyExchange->id);

            $this->balanceUpdate($arr);

            DB::commit();

            $this->notifyToAdmin($currencyExchange);

        } catch (Exception $exception) {
            DB::rollBack();
            throw new ExchangeMoneyException($exception->getMessage());
        }

    }

    /**
     * Get the currencies
     *
     * @param array|int $ids
     *
     * @return Collection|Currency|null
     */
    private function getCurrencies($ids = [])
    {
        if (is_null($this->currencies)) {
            $this->currencies = Currency::whereIn("id", (array) $ids)->get();
        }
        if (!is_array($ids)) {
            return $this->currencies->where("id", $ids)->first();
        }
        return $this->currencies;
    }

    public function firstOrCreateWallet($user_id, $currencyId)
    {
        $wallet = $this->getWallet($currencyId, $user_id);
        if (is_null($wallet)) {
            $wallet = Wallet::createWallet($user_id, $currencyId);
        }
        return $wallet;
    }

    /**
     * Returns Wallet of corresponding currency
     *
     * @param int $currencyId
     *
     * @return Wallet
     */
    public function getWallet($currencyId, $user_id)
    {
        return Wallet::where(['currency_id' => $currencyId, 'user_id' => $user_id])->first();
    }

    public function getRate($fromCurrencyId, $toCurrencyId, $amount)
    {
        $rateDetails = $this->getCurrenciesExchangeRates($fromCurrencyId, $toCurrencyId, $amount);

        return $rateDetails["rate"];
    }

    public function createExchange($arr, $toWallet)
    {
       return $this->exchange->createCurrencyExchange($arr, $toWallet);
    }

    //create Exchange From Transaction
    public function creatFromTransaction($arr, $currencyExchangeid)
    {
        $this->exchange->createExchangeFromTransaction($arr, $currencyExchangeid);
        return true;
    }

    //create Exchange To Transaction

    public function creatToTransaction($arr, $currencyExchangeid)
    {
        $this->exchange->createExchangeToTransaction($arr, $currencyExchangeid);
        return true;
    }

    //Update From Wallet
    public function balanceUpdate($arr)
    {
        $this->exchange->updateFromWallet($arr);
        $this->updateToWallet($arr);
        return true;
    }

    public function updateToWallet($arr)
    {
        $arr['toWallet']->balance = ($arr['toWallet']->balance + $arr['finalAmount']);
        $arr['toWallet']->save();
        return true;
    }



    public function notifyToAdmin($currencyExchange)
    {
        (new ExchangeMoneyMailService)->send($currencyExchange, ['type' => 'exchange', 'medium' => 'email']);

        return true;
    }
}
