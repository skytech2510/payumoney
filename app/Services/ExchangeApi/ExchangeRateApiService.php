<?php

namespace App\Services\ExchangeApi;

class ExchangeRateApiService
{
    const API_URL = 'https://v6.exchangerate-api.com/v6/';

    public function __construct(private $fromCurrencyCode,private $toCurrencyCode) 
    {
    }

    public function generatedUrl()
    {
        return self::API_URL. settings('exchange_rate_api_key') . '/pair/' . $this->fromCurrencyCode .'/' .  $this->toCurrencyCode;
    }
    
    /**
     * Fetch the exchange rate
     * 
     * @return mixed
     */
    public function getCurrencyRate()
    {
        $response = \Illuminate\Support\Facades\Http::get($this->generatedUrl());
        $responseData = json_decode($response->getBody());

        if ($response->status() == 200 && $responseData->result == 'success') {
            return $responseData->conversion_rate;
        } 

        return $responseData->result == 'error' ? 'error' : '';
    }
}