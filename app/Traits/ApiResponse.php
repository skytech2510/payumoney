<?php
/**
 * @package ApiResponse
 * @author techvillage <support@techvill.org>
 * @contributor Millat <[abdur.techvill@gmail.com]>
 * @created 08-11-2022
 */

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Prepare response.
     *
     * @param string $message
     * @param  int  $statusCode
     * @return array
     */
    public function response($data = [], int $statusCode = Response::HTTP_OK, string $message = ''): JsonResponse
    {
        if (empty($message)) {
            $message = Response::$statusTexts[$statusCode];
        }

        return response()->json([
            'response' => [
                'status' => [
                    'code' => $statusCode,
                    'message' => $message
                ],
                'records' => $data
            ]], $statusCode);
    }

    /**
     * Success Response
     *
     * @param array $data
     * @param  int  $statusCode
     * @param string $message
     * @return JsonResponse
     */
    public function successResponse($data = [], int $statusCode = Response::HTTP_OK, string $message = ''): JsonResponse
    {
        return $this->response($data, $statusCode, $message);
    }

    /**
     * Error Response
     *
     * @param  array  $errors
     * @param  int  $statusCode
     * @param string $message
     * @return JsonResponse
     */
    public function errorResponse($errors = [], int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, string $message = ''): JsonResponse
    {
        return $this->response($errors, $statusCode, $message);
    }

    /**
     * Response with status code 200.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function okResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->successResponse($data, Response::HTTP_OK, $message);
    }

    /**
     * Response with status code 201.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function createdResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->successResponse($data, Response::HTTP_CREATED, $message);
    }

    /**
     * Response with status code 400.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function badRequestResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_BAD_REQUEST, $message);
    }

    /**
     * Response with status code 401.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function unauthorizedResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_UNAUTHORIZED, $message);
    }

    /**
     * Response with status code 403.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function forbiddenResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_FORBIDDEN, $message);
    }

    /**
     * Response with status code 404.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function notFoundResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_NOT_FOUND, $message);
    }

    /**
     * Response with status code 409.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function conflictResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_CONFLICT, $message);
    }

    /**
     * Response with status code 422.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function unprocessableResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_UNPROCESSABLE_ENTITY, $message);
    }

    /**
     * Response with status code 405.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function methodNotAllowedResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_METHOD_NOT_ALLOWED, $message);
    }

    /**
     * Response with status code 503.
     *
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function serviceUnavailableResponse($data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, Response::HTTP_SERVICE_UNAVAILABLE, $message);
    }
    
}
