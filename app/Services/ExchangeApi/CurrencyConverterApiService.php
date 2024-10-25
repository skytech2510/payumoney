<?php

namespace App\Services\ExchangeApi;

class CurrencyConverterApiService
{
    const API_URL = 'https://free.currencyconverterapi.com/api/v6/';

    public function __construct(private $fromCurrencyCode, private $toCurrencyCode) 
    {
    }

    public function generatedUrl()
    {
        return self::API_URL . 'convert?q=' . $this->fromCurrencyCode . '_' . $this->toCurrencyCode . '&compact=ultra&apiKey=' . settings('currency_converter_api_key');
    }
        
    /**
     * Fetch the exchange rate
     * 
     * @return mixed
     */
    public function getCurrencyRate()
    {
        $response = \Illuminate\Support\Facades\Http::get($this->generatedUrl());

        if ($response->status() == 200) {
            $variable = $this->fromCurrencyCode . "_" . $this->toCurrencyCode;
            return json_decode($response)->$variable;
        }
    }
}