<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class ResponseHelper
{
    /**
     * Prepare success response
     *
     * @param string $apiStatus
     * @param string $apiMessage
     * @param array $apiData
     * @return mixed
     */
    public function success(string $apiStatus = '', string $apiMessage = '', array $apiData = [])
    {
        $response['status'] = $apiStatus;

        if (!empty($apiData)) {
            $response['data'] = $apiData;
        }

        if ($apiMessage) {
            $response['message'] = $apiMessage;
        }

        return response()->json($response, $apiStatus, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Prepare success response
     *
     * @param string $apiStatus
     * @param string $apiMessage
     * @param Illuminate\Pagination\LengthAwarePaginator $apiData
     * @return mixed
     */
    public function successWithPagination(
        LengthAwarePaginator $apiData,
        string $apiStatus = '',
        string $apiMessage = ''
    ) {
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
        if ($apiMessage) {
            $response['message'] = $apiMessage;
        }

        return response()->json($response, $apiStatus, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Prepare error response
     *
     * @param string $statusCode
     * @param string $statusType
     * @param string $customErrorCode
     * @param string $customErrorMessage
     * @return mixed
     */
    public function error(
        string $statusCode = '',
        string $statusType = '',
        string $customErrorCode = '',
        string $customErrorMessage = ''
    ) {
        $response['status'] = $statusCode;
        $response['type'] = $statusType;
        if ($customErrorCode) {
            $response['code'] = $customErrorCode;
        }
        $response['message'] = $customErrorMessage;
        $data['errors'][] = $response;

        return response()->json($data, $statusCode, [], JSON_NUMERIC_CHECK);
    }
}
