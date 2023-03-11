<?php

namespace App\Http\Controllers;

use Validator;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use App\Helpers\ResponseHelper;
use App\Events\ActivityLogEvent;
use Illuminate\Http\JsonResponse;
use App\Events\TenantLanguageAddedEvent;
use App\Traits\RestExceptionHandlerTrait;
use App\Repositories\Language\LanguageRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\TenantLanguage\TenantLanguageRepository;

//!  Tenant language controller
/*!
This controller is responsible for handling tenant language store/update, listing and delete operations.
 */
class TenantLanguageController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\TenantLanguage\TenantLanguageRepository
     */
    private $tenantLanguageRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Repositories\Language\LanguageRepository
     */
    private $languageRepository;

    /**
     * Create a new tenant language controller instance.
     *
     * @param  App\Repositories\TenantLanguage\TenantLanguageRepository $tenantLanguageRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Repositories\Language\LanguageRepository $languageRepository
     * @return void
     */
    public function __construct(
        TenantLanguageRepository $tenantLanguageRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers,
        LanguageRepository $languageRepository

    ) {
        $this->tenantLanguageRepository = $tenantLanguageRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->languageRepository = $languageRepository;
    }

    /**
     * Display listing of tenant language.
     *
     * @param Illuminate\Http\Request $request
     * @param int $tenantId
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request, int $tenantId): JsonResponse
    {
        try {
            $tenantLanguageLists = $this->tenantLanguageRepository->getTenantLanguageList($request, $tenantId);
            
            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiData = $tenantLanguageLists;
            $apiMessage = (count($apiData) > 0)  ?
            trans('messages.success.MESSAGE_TENANT_LANGUAGE_LISTING') :
            trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_NOT_FOUND');
            
            return $this->responseHelper->successWithPagination($apiData, $apiStatus, $apiMessage);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            );
        }
    }

    /**
     * Store/Update a newly created tenant language into database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->toArray(),
            [
                'tenant_id' => 'required|exists:tenant,tenant_id,deleted_at,NULL',
                'language_id'  => 'required|exists:language,language_id,deleted_at,NULL,status,1',
                'default'  => 'required|in:1,0'
            ]
        );

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        if ($request['default'] == config('constants.language_status.INACTIVE')) {
            $defaultLanguageStatus = $this->tenantLanguageRepository->checkDefaultLanguageSettings(
                $request->tenant_id,
                $request->language_id
            );
            if ($defaultLanguageStatus) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TENANT_DEFAULT_LANGUAGE_REQUIRED'),
                    trans('messages.custom_error_message.ERROR_TENANT_DEFAULT_LANGUAGE_REQUIRED')
                );
            }
        }

        // Copy language file from default_language S3 bucket to tenant bucket
        $tenantLanguageData = $this->tenantLanguageRepository->storeOrUpdate($request->toArray());
        $language = $this->languageRepository->find($request->language_id);
        $tenantName = $this->helpers->getTenantDetails($request->tenant_id)->name;
        event(new TenantLanguageAddedEvent($tenantName, $language->code));

        // Store or update tenant language details
        // Set response data
        $apiStatus = ($tenantLanguageData->wasRecentlyCreated) ? Response::HTTP_CREATED : Response::HTTP_OK;
        $apiMessage = ($tenantLanguageData->wasRecentlyCreated)
        ? trans('messages.success.MESSAGE_TENANT_LANGUAGE_ADDED')
        : trans('messages.success.MESSAGE_TENANT_LANGUAGE_UPDATED');
        $apiData = ['tenant_language_id' => $tenantLanguageData->tenant_language_id];

        $activityLogStatus = ($tenantLanguageData->wasRecentlyCreated)
            ? config('constants.activity_log_actions.CREATED') : config('constants.activity_log_actions.UPDATED');

        // Make activity log
        event(new ActivityLogEvent(
            config('constants.activity_log_types.TENANT_LANGUAGE'),
            $activityLogStatus,
            get_class($this),
            $request->toArray(),
            $tenantLanguageData->tenant_language_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
    
    /**
     * Remove tenant language details from storage.
     *
     * @param int $tenantLanguageId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $tenantLanguageId): JsonResponse
    {
        try {
            $tenantLanguage = $this->tenantLanguageRepository->find($tenantLanguageId);
            if (!is_null($tenantLanguage) && $tenantLanguage->default == '1') {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_DELETE_DEFAULT_TENANT_LANGUAGE'),
                    trans('messages.custom_error_message.ERROR_DELETE_DEFAULT_TENANT_LANGUAGE')
                );
            }
            $this->tenantLanguageRepository->delete($tenantLanguageId);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_TENANT_LANGUAGE_DELETED');

            // Make activity log
            event(new ActivityLogEvent(
                config('constants.activity_log_types.TENANT_LANGUAGE'),
                config('constants.activity_log_actions.DELETED'),
                get_class($this),
                [],
                $tenantLanguageId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TENANT_LANGUAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_NOT_FOUND')
            );
        }
    }
}
