<?php

namespace Modules\TatumIo\Class;

use Exception;
use Illuminate\Support\Str;


class Network
{

    public function getCryptoNetwork($name, $apiKey)
    {

        $environment = (Str::endsWith($name, 'test')) ? 'testnet' : 'mainnet';

        $networkClass = $name.'Network';
        $class = 'Modules\\TatumIo\\Class\\'.$networkClass;

        if (!class_exists($class)) {
            throw new Exception(__(":x Network processor not found.", ["x" => ucfirst($name)]));
        }

        $network = new $class($apiKey, $environment);

        return $network;

    }

}
