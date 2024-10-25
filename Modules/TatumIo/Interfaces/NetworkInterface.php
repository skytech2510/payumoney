<?php

namespace Modules\TatumIo\Interfaces;

interface NetworkInterface
{
    public function generateWallet();
    public function generateAddress($xpub, $index);
    public function generateAddressPrivateKey($index, $mnemonic);
    public function getBalanceOfAddress($address);

}
