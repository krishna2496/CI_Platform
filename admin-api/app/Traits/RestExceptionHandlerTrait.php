<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

trait RestExceptionHandlerTrait
{
    private $responseHelper;

    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }

    /**
     * Returns json response for generic Internal Server Error.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function internalServerError(string $message = 'Internal Server Error')
    {
        return $this->jsonResponse(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
            '',
            $message
        );
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function modelNotFound(string $customErrorCode = '', string $message = 'Record not found')
    {
        return $this->jsonResponse(
            Response::HTTP_NOT_FOUND,
            Response::$statusTexts[Response::HTTP_NOT_FOUND],
            $customErrorCode,
            $message
        );
    }
    
    /**
     * Returns json response for Invalid argument exception.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidArgument(string $customErrorCode = '', string $message = 'Invalid argument')
    {
        return $this->jsonResponse(
            Response::HTTP_BAD_REQUEST,
            Response::$statusTexts[Response::HTTP_BAD_REQUEST],
            $customErrorCode,
            $message
        );
    }
    
    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse(
        string $statusCode = '404',
        string $statusType = '',
        string $customErrorCode = '',
        string $message = ''
    ) {
        return $this->responseHelper->error($statusCode, $statusType, $customErrorCode, $message);
    }
}
