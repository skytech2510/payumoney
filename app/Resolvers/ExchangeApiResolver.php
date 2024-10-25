<?php

namespace App\Resolvers;

use Exception;

class ExchangeApiResolver
{
    /**
     * Resolve the exchange api provider
     *
     * @param string $exchangeApi
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * 
     * @return object
     *
     * @throws Excepton
     */
    public function resolveService($exchangeApi, $fromCurrencyCode, $toCurrencyCode)
    {
        $exchangeApi = array_map(function ($word) {
            return ucfirst($word);
        }, explode('_', str_replace('_key', '', $exchangeApi)));

        if ($exchangeApi) {
            return resolve("App\\Services\\ExchangeApi\\" . join($exchangeApi) . 'Service', ['fromCurrencyCode' => $fromCurrencyCode, 'toCurrencyCode' => $toCurrencyCode]);
        }

        throw new Exception(__('The selected exchange api is not configured.'));
    }
    
}