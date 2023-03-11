<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use App\Repositories\ApiUser\ApiUserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Tenant\TenantRepository;
use App\Events\ActivityLogEvent;

//! Api user controller
/*!
This controller is responsible for handling api user create, renew, listing and delete operations.
 */
class ApiUserController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\ApiUser\ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new api user controller instance.
     *
     * @param  App\Repositories\Tenant\TenantRepository $tenantRepository
     * @param  App\Repositories\ApiUser\ApiUserRepository $apiUserRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(
        TenantRepository $tenantRepository,
        ApiUserRepository $apiUserRepository,
        ResponseHelper $responseHelper
    ) {
        $this->tenantRepository = $tenantRepository;
        $this->apiUserRepository = $apiUserRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Create api user for tenant
     *
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
    */
    public function createApiUser(int $tenantId): JsonResponse
    {
        try {
            $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }
        
        $apiKeys['api_key'] = str_random(16);
        $apiKeys['api_secret'] = str_random(16);
        $apiUser = $this->apiUserRepository->store($tenantId, $apiKeys);
        
        $response['api_user_id'] = $apiUser->api_user_id;
        $response['api_key'] = $apiUser->api_key;
        $response['api_secret'] = $apiKeys['api_secret'];
        
        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_API_USER_CREATED_SUCCESSFULLY');

        // Make activity log
        event(new ActivityLogEvent(
            config('constants.activity_log_types.API_USER'),
            config('constants.activity_log_actions.CREATED'),
            get_class($this),
            [],
            $apiUser->api_user_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $response);
    }

    /**
     * Renew api_secret for api user
     *
     * @param int $tenantId
     * @param int $apiUserId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function renewApiUser(int $tenantId, int $apiUserId): JsonResponse
    {
        try {
            $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }

        try {
            $apiSecret = str_random(16);
            
            $apiUser = $this->apiUserRepository->update($tenantId, $apiUserId, $apiSecret);
            $apiUser->api_secret = $apiSecret;

            $response['api_user_id'] = $apiUser->api_user_id;
            $response['api_key'] = $apiUser->api_key;
            $response['api_secret'] = $apiSecret;

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_API_USER_UPDATED_SUCCESSFULLY');

            // Make activity log
            event(new ActivityLogEvent(
                config('constants.activity_log_types.API_USER_KEY_RENEW'),
                config('constants.activity_log_actions.UPDATED'),
                get_class($this),
                [],
                $apiUserId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $response);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_API_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_API_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Delete api user
     *
     * @param int $tenantId
     * @param int $apiUserId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function deleteApiUser(int $tenantId, int $apiUserId): JsonResponse
    {
        try {
            $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }

        try {
            $apiUser = $this->apiUserRepository->delete($tenantId, $apiUserId);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_API_USER_DELETED');

            // Make activity log
            event(new ActivityLogEvent(
                config('constants.activity_log_types.API_USER'),
                config('constants.activity_log_actions.DELETED'),
                get_class($this),
                [],
                $apiUserId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_API_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_API_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Get all api users
     *
     * @param int $tenantId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function getAllApiUser(int $tenantId): JsonResponse
    {
        try {
            $tenantDetail = $this->tenantRepository->find($tenantId);

            $apiUsers = $this->apiUserRepository->apiUserList($tenantId);

            $responseMessage = (count($apiUsers) > 0) ? trans('messages.success.MESSAGE_TENANT_API_USER_LISTING') :
            trans('messages.success.MESSAGE_NO_RECORD_FOUND');

            return $this->responseHelper->successWithPagination($apiUsers, Response::HTTP_OK, $responseMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }
    }

    /**
     * Get api user detail
     *
     * @param int $tenantId
     * @param int $apiUserId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function getApiUserDetail(int $tenantId, int $apiUserId): JsonResponse
    {
        try {
            $tenantDetail = $this->tenantRepository->find($tenantId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }
        try {
            $apiUser = $this->apiUserRepository->findApiUser($apiUserId);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_API_USER_FOUND');
            $apiData = $apiUser->toArray();

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_API_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_API_USER_NOT_FOUND')
            );
        }
    }
}
