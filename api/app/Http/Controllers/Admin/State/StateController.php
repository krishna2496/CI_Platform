<?php

namespace App\Http\Controllers\Admin\State;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\LanguageHelper;
use Illuminate\Http\Request;
use Validator;
use App\Events\User\UserActivityLogEvent;
use App\Repositories\State\StateRepository;

//!  State controller
/*!
This controller is responsible for handling state listing, store, update, show and delete operations.
 */
class StateController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\State\StateRepository
     */
    private $stateRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\State\StateRepository $stateRepository
     * @param App\Helpers\ResponseHelper             $responseHelper
     * @param App\Helpers\LanguageHelper             $languageHelper
     * @param \Illuminate\Http\Request               $request
     *
     * @return void
     */
    public function __construct(
        StateRepository $stateRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Request $request
    ) {
        $this->stateRepository = $stateRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->userApiKey = $request->header('php-auth-user');
    }

    /**
     * Fetch all state.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $stateList = $this->stateRepository->stateLists($request);
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!$stateList->isEmpty()) ? trans('messages.success.MESSAGE_STATE_LISTING')
        : trans('messages.success.MESSAGE_NO_STATE_FOUND');

        return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $stateList);
    }

    /**
     * Fetch state by country id.
     *
     * @param Illuminate\Http\Request $request
     * @param int                     $countryId
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function fetchState(Request $request, int $countryId): JsonResponse
    {
        try {
            $stateList = $this->stateRepository->getStateList($request, $countryId);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($stateList->count() > 0) ? trans('messages.success.MESSAGE_STATE_LISTING')
            : trans('messages.success.MESSAGE_NO_STATE_FOUND');

            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $stateList);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STATE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STATE_NOT_FOUND')
            );
        }
    }

    /**
     * Store a newly created states.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validations
        $validator = Validator::make(
            $request->all(),
            [
                'country_id' => 'required|exists:country,country_id,deleted_at,NULL',
                'states' => 'required',
                'states.*.translations' => 'required|array',
                'states.*.translations.*.lang' => 'required|min:2|max:2',
                'states.*.translations.*.name' => 'required|max:255',
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_STATE_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        if (!empty($request->states)) {
            foreach ($request->states[0]['translations'] as $key => $value) {
                $languageCode = $value['lang'];
                // Check for valid language code inside ci admin
                if (!$this->languageHelper->isValidAdminLanguageCode($languageCode)) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID_CODE'),
                        trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_INVALID_CODE')
                    );
                }
            }
        }
        $countryId = $request->country_id;

        // Add state one by one
        $createdState = [];
        foreach ($request->states as $key => $state) {
            // Add country id into state table
            $stateDetails = $this->stateRepository->store($countryId);

            // Add all translations add into state_translation table
            $createdState[$key]['state_id'] = $state['state_id'] = $stateDetails->state_id;
            $this->stateRepository->storeStateLanguage($state);
        }

        // Set response data
        $apiData = ['state_ids' => $createdState];
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_STATE_CREATED');

        event(new UserActivityLogEvent(
            config('constants.activity_log_types.STATE'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            null
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update state data resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $this->stateRepository->find($id);
            // Server side validations
            $validator = Validator::make(
                $request->all(),
                [
                    'country_id' => 'sometimes|required|exists:country,country_id,deleted_at,NULL',
                    'translations' => 'sometimes|required|array',
                    'translations.*.lang' => 'required|min:2|max:2',
                    'translations.*.name' => 'required|max:255',
                ]
            );
            if (!empty($request->translations)) {
                foreach ($request->translations as $key => $value) {
                    $languageCode = $value['lang'];
                    // Check for valid language code
                    if (!$this->languageHelper->isValidAdminLanguageCode($languageCode) ||
                    !$this->languageHelper->isValidTenantLanguageCode($request, $languageCode)) {
                        return $this->responseHelper->error(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                            config('constants.error_codes.ERROR_TENANT_LANGUAGE_INVALID_CODE'),
                            trans('messages.custom_error_message.ERROR_TENANT_LANGUAGE_INVALID_CODE')
                        );
                    }
                }
            }

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_STATE_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            $this->stateRepository->update($request, $id);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_STATE_UPDATED');

            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STATE'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                null
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STATE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STATE_NOT_FOUND')
            );
        }
    }

    /**
     * Fetch state by state Id.
     *
     * @param Illuminate\Http\Request $request
     * @param int                     $stateId
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $stateId): JsonResponse
    {
        try {
            $stateList = $this->stateRepository->getStateDetails($stateId);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_STATE_FOUND');

            return $this->responseHelper->success($apiStatus, $apiMessage, $stateList->toArray());
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STATE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STATE_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the state from storage.
     *
     * @param int $id
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if ($this->stateRepository->hasMission($id)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_STATE_UNABLE_TO_DELETE'),
                trans('messages.custom_error_message.ERROR_STATE_UNABLE_TO_DELETE')
            );
        }
        try {
            $this->stateRepository->delete($id);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_STATE_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.STATE'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                null,
                null,
                $id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_STATE_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_STATE_NOT_FOUND')
            );
        }
    }
}
