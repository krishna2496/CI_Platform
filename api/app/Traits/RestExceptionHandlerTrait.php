<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper;

trait RestExceptionHandlerTrait
{
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new trait instance.
     *
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }

    /**
     * Returns json response for Eloquent model not found exception.
     *
     * @param string $customErrorCode
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
     * @param string $customErrorCode
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
     * Returns json response for Methos not allowed http exception
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function methodNotAllowedHttp(string $message = 'Method not allowed')
    {
        return $this->jsonResponse(
            Response::HTTP_METHOD_NOT_ALLOWED,
            Response::$statusTexts[Response::HTTP_METHOD_NOT_ALLOWED],
            '',
            $message
        );
    }

    /**
     * Returns json response for internal server error.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function internalServerError(string $message = 'Internal server error')
    {
        return $this->jsonResponse(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
            '',
            $message
        );
    }

    /**
     * Returns json response for bucket not found on s3
     *
     * @param string $customErrorCode
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function bucketNotFound(string $customErrorCode = '', string $message = 'Assets bucket not found on S3')
    {
        return $this->jsonResponse(
            Response::HTTP_NOT_FOUND,
            Response::$statusTexts[Response::HTTP_NOT_FOUND],
            $customErrorCode,
            $message
        );
    }

    /**
     * Returns json response for files not found on s3 for bucket folder
     *
     * @param string $customErrorCode
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function fileNotFound(string $customErrorCode = '', string $message = 'File not found on S3')
    {
        return $this->jsonResponse(
            Response::HTTP_NOT_FOUND,
            Response::$statusTexts[Response::HTTP_NOT_FOUND],
            $customErrorCode,
            $message
        );
    }

    /**
     * Returns json response for tenant's domain not found
     *
     * @param string $customErrorCode
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tenantDomainNotFound(string $customErrorCode = '', string $message = 'Tenant Domain not found')
    {
        return $this->jsonResponse(
            Response::HTTP_NOT_FOUND,
            Response::$statusTexts[Response::HTTP_NOT_FOUND],
            $customErrorCode,
            $message
        );
    }

    /**
     * Returns json response for SAML errors
     *
     * @param string $customErrorCode
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function samlError(string $customErrorCode, string $message)
    {
        $statusCode = $customErrorCode === config('constants.error_codes.ERROR_INVALID_SAML_IDENTITY_PROVIDER') ?
            Response::HTTP_BAD_REQUEST :
            Response::HTTP_FORBIDDEN;

        return $this->jsonResponse(
            $statusCode,
            Response::$statusTexts[$statusCode],
            $customErrorCode,
            $message
        );
    }

    /**
     * Returns a HTTP 403 Forbidden response
     * @param string $customErrorCode
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbidden($errorCode = '', $message = '')
    {
        return $this->jsonResponse(
            Response::HTTP_FORBIDDEN,
            Response::$statusTexts[Response::HTTP_FORBIDDEN],
            $errorCode,
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
