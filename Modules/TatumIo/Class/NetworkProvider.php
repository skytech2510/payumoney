<?php

namespace Modules\TatumIo\Class;

use Exception;


class NetworkProvider
{

    public function getCryptoNetwork($name, $apiKey)
    {
        $networkClass = ucfirst(strtolower($name)) . 'Network';
        $class = 'Modules\\TatumIo\\Class\\CryptoAsset\\' . $networkClass;

        if (!class_exists($class)) {
            throw new Exception(__(":x Network processor not found.", ["x" => ucfirst($name)]));
        }
        $networkClass = new $class($apiKey, $name);
        return $networkClass;
    }
}
