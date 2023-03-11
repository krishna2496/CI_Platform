<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Helpers\Helpers;
use App\Traits\RestExceptionHandlerTrait;
use App\Repositories\Language\LanguageRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Validator;
use App\Events\ActivityLogEvent;
use Illuminate\Validation\Rule;

//! Language controller
/*!
This controller is responsible for handling language store, update, listing, show and delete operations.
 */
class LanguageController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\Language\LanguageRepository
     */
    private $languageRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new language controller instance.
     *
     * @param  App\Repositories\Language\LanguageRepository $languageRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(
        LanguageRepository $languageRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers
    ) {
        $this->languageRepository = $languageRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
    }

    /**
     * Display listing of language
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $languages = $this->languageRepository->getLanguageList($request);
            $paginatedData = $this->helpers->paginationTransform(
                $languages,
                $request->except(['page','perPage']),
                $request->url()
            );

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiData = $paginatedData;
            $apiMessage = (count($apiData) > 0)  ?
            trans('messages.success.MESSAGE_LANGUAGE_LISTING') :
            trans('messages.custom_error_message.ERROR_LANGUAGE_NOT_FOUND');
            
            return $this->responseHelper->successWithPagination($apiData, $apiStatus, $apiMessage);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Store a newly created language into database
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse;
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->toArray(),
            [
                'name' => 'required|unique:language,name,NULL,language_id,deleted_at,NULL',
                'code'  => 'required|max:2|unique:language,code,NULL,language_id,deleted_at,NULL',
                'status'  => 'required|in:1,0'
            ]
        );

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_LANGUAGE_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }
      
        // Create new language
        $languageData = $this->languageRepository->store($request->toArray());
    
        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiData = ['language_id' => $languageData->language_id];
        $apiMessage =  trans('messages.success.MESSAGE_LANGUAGE_CREATED');
        
        // Make activity log
        event(new ActivityLogEvent(
            config('constants.activity_log_types.LANGUAGE'),
            config('constants.activity_log_actions.CREATED'),
            get_class($this),
            $request->toArray(),
            $languageData->language_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update language details in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $languageId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function update(Request $request, int $languageId): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->toArray(),
                [
                    "name" => [
                        "sometimes",
                        "required",
                        Rule::unique('language')->ignore($languageId, 'language_id,deleted_at,NULL')],
                    'code'  => 'sometimes|required|max:2|required|unique:language,code,'.
                    $languageId .',language_id,deleted_at,NULL',
                    'status'  => 'sometimes|required|in:1,0'
                ]
            );

            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_LANGUAGE_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            //Update language data
            $languageData = $this->languageRepository->update($request->toArray(), $languageId);
            
            $apiStatus = Response::HTTP_OK;
            $apiData = ['language_id' => $languageData->language_id];
            $apiMessage = trans('messages.success.MESSAGE_LANGUAGE_UPDATED');

            // Make activity log
            event(new ActivityLogEvent(
                config('constants.activity_log_types.LANGUAGE'),
                config('constants.activity_log_actions.UPDATED'),
                get_class($this),
                $request->toArray(),
                $languageData->language_id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_LANGUAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_LANGUAGE_NOT_FOUND')
            );
        }
    }

    /**
     * Get language detail
     *
     * @param int $languageId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function show(int $languageId): JsonResponse
    {
        try {
            $languageDetail = $this->languageRepository->find($languageId);
            
            // Set response message
            $apiStatus = Response::HTTP_OK;
            $apiData = $languageDetail->toArray();
            $apiMessage = trans('messages.success.MESSAGE_LANGUAGE_FOUND');
            
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_LANGUAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_LANGUAGE_NOT_FOUND')
            );
        }
    }
    
    /**
     * Remove language details from storage.
     *
     * @param int $languageId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $languageId): JsonResponse
    {
        try {
            if ($this->languageRepository->hasLanguage($languageId)) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_LANGUAGE_UNABLE_TO_DELETE'),
                    trans('messages.custom_error_message.ERROR_LANGUAGE_UNABLE_TO_DELETE')
                );
            }

            $status = $this->languageRepository->delete($languageId);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_NEWS_DELETED');

            // Make activity log
            event(new ActivityLogEvent(
                config('constants.activity_log_types.LANGUAGE'),
                config('constants.activity_log_actions.DELETED'),
                get_class($this),
                [],
                $languageId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_LANGUAGE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_LANGUAGE_NOT_FOUND')
            );
        }
    }
}
