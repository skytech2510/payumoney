<?php
/**
 * @package CryptoReceiveController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md. Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 02-10-2023
 */

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\TatumIo\Class\TatumIoTransaction;

class CryptoReceiveController extends Controller
{
    protected $tatumIo;


    public function receiveCrypto(): JsonResponse
    {
        $network = strtoupper(request()->currencyCode);
        $user_id = auth()->id();

        $this->tatumIo = new TatumIoTransaction($network);
        $this->tatumIo->tatumIoAsset();
        $this->tatumIo->checkUserTatumWallet($user_id);
        $address = $this->tatumIo->getUserAddress();

        $data = [
            'network' => $network,
            'receiver_address' => $address
        ];

        try {
            return $this->successResponse($data);
        } catch (Exception $exception) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }


    }




}
