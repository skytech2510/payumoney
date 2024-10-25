<?php

/**
 * @package InvalidGatewayException
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 21-12-2022
 */

namespace App\Services\Gateways\Gateway\Exceptions;

use App\Exceptions\Api\V2\ApiException;
use App\Traits\ApiResponse;

class InvalidGatewayException extends ApiException
{
    use ApiResponse;


    /**
     * Render custom exception
     *
     * @param mixed|null $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        return $this->unprocessableResponse(
            [],
            $this->getMessage()
        );
    }
}
