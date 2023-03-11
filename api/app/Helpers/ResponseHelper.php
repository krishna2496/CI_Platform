<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Prepare success response
     *
     * @param string $apiStatus
     * @param string $apiMessage
     * @param array $apiData
     * @param bool $convertNumeric - To specify whether to transform number strings into int type
     * @return Illuminate\Http\JsonResponse
     */
    public function success(
        string $apiStatus = '',
        string $apiMessage = '',
        array $apiData = [],
        bool $convertNumeric = true
    ): JsonResponse {
        $response['status'] = $apiStatus;

        if (!empty($apiData)) {
            $response['data'] = $apiData;
        }

        if ($apiMessage) {
            $response['message'] = $apiMessage;
        }

        return response()->json($response, $apiStatus, [], $convertNumeric ? JSON_NUMERIC_CHECK: null);
    }

    /**
     * Prepare success response
     *
     * @param string $apiStatus
     * @param string $apiMessage
     * @param Illuminate\Pagination\LengthAwarePaginator $apiData
     * @param array $metaData
     * @return Illuminate\Http\JsonResponse
     */
    public function successWithPagination(
        string $apiStatus = '',
        string $apiMessage = '',
        LengthAwarePaginator $apiData = null,
        array $metaData = [],
        bool $convertNumeric = true
    ): JsonResponse {
        $response['status'] = $apiStatus;
        $response['data'] = [];

        // Check response data have pagination or not? Pagination response parameter sets
        if ($apiData->count()) {
            $apiData->appends(['perPage' => $apiData->perPage()]);

            $response['data'] = $apiData->toArray()['data'];
            $response['pagination'] = [
                "total" => $apiData->total(),
                "per_page" => $apiData->perPage(),
                "current_page" => $apiData->currentPage(),
                "total_pages" => $apiData->lastPage(),
                "next_url" => $apiData->nextPageUrl()
            ];
        }

        if (!empty($metaData)) {
            $response['meta_data'] = $metaData;
        }

        if ($apiMessage) {
            $response['message'] = $apiMessage;
        }

        return response()->json($response, $apiStatus, [], $convertNumeric ? JSON_NUMERIC_CHECK: null);
    }

    /**
     * Prepare error response
     *
     * @param string $statusCode
     * @param string $statusType
     * @param string $customErrorCode
     * @param string $customErrorMessage
     * @return Illuminate\Http\JsonResponse
     */
    public function error(
        string $statusCode = '',
        string $statusType = '',
        string $customErrorCode = '',
        string $customErrorMessage = '',
        string $externalErrorCode = null,
        string $externalErrorMessage = null
    ): JsonResponse {
        $response['status'] = $statusCode;
        $response['type'] = $statusType;
        if ($customErrorCode) {
            $response['code'] = $customErrorCode;
        }
        $response['message'] = $customErrorMessage;
        if ($externalErrorCode) {
            $response['external_code'] = $externalErrorCode;
        }
        if ($externalErrorMessage) {
            $response['external_message'] = $externalErrorMessage;
        }
        $data["errors"][] = $response;

        return response()->json($data, $statusCode, [], JSON_NUMERIC_CHECK);
    }
}
