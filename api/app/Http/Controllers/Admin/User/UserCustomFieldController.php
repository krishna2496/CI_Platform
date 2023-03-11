<?php

namespace App\Http\Controllers\Admin\User;

use App\Events\User\UserActivityLogEvent;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\UserCustomField\UserCustomFieldRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Validator;

//!  User custom field controller
/*!
This controller is responsible for handling user custom field listing, show, store, update and delete operations.
 */

class UserCustomFieldController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * User custom field
     *
     * @var App\Repositories\UserCustomField\UserCustomFieldRepository
     */
    private $userCustomFieldRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\UserCustomField\UserCustomFieldRepository $userCustomFieldRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        UserCustomFieldRepository $userCustomFieldRepository,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        $this->userCustomFieldRepository = $userCustomFieldRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey = $request->header('php-auth-user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customFields = $this->userCustomFieldRepository->userCustomFieldList($request);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($customFields->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND')
            : trans('messages.success.MESSAGE_CUSTOM_FIELD_LISTING');
            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $customFields);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Store user custom field
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make(
            $request->toArray(),
            [
                "name" => "required|max:100|unique:user_custom_field,name,NULL,field_id,deleted_at,NULL",
                "type" => [
                    'required',
                    Rule::in(config('constants.custom_field_types'))
                ],
                "is_mandatory" => "required|boolean",
                "translations" => "required",
                "translations.*.lang" => "max:2",
                "translations.*.values" => Rule::requiredIf(
                    $request->type === config('constants.custom_field_types.DROP-DOWN') ||
                    $request->type === config('constants.custom_field_types.RADIO')
                ),
                "internal_note" => "sometimes|nullable|string|max:500"
            ]
        );
        // If post parameter have any missing parameter
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        $lastOrderInDb = $this->userCustomFieldRepository->findMaxOrder();
        $order = ($lastOrderInDb === null) ? 1 : $lastOrderInDb + 1;
        $request->merge(['order' => $order]);

        // Create new user custom field record
        $customField = $this->userCustomFieldRepository->store($request->toArray());

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_CUSTOM_FIELD_ADDED');
        $apiData = ['field_id' => $customField['field_id']];

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.USERS_CUSTOM_FIELD'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $customField['field_id']
        ));
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update user custom field
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Server side validations
            $validator = Validator::make($request->toArray(), [
                "name" => [
                    "sometimes",
                    "required",
                    "max:100",
                    Rule::unique('user_custom_field')->ignore($id, 'field_id,deleted_at,NULL')
                ],
                "order" => "sometimes|required|numeric|min:1",
                "is_mandatory" => "sometimes|required|boolean",
                "type" => [
                    "sometimes",
                    "required",
                    Rule::in(config('constants.custom_field_types'))
                ],
                "translations.*.lang" => "max:2",
                "translations.*.values" => Rule::requiredIf($request->type === config('constants.custom_field_types.DROP-DOWN')
                    || $request->type === config('constants.custom_field_types.RADIO')),
                "internal_note" => "sometimes|nullable|max:500"
            ]);

            // If post parameter have any missing parameter
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            $data = $request;

            $fieldDetail = $this->userCustomFieldRepository->find($id);
            $currentOrder = $fieldDetail->order;
            $requestOrder = $request->order;

            if ($currentOrder !== $requestOrder && $request->has('order')) {
                $maxOrder = $this->userCustomFieldRepository->findMaxOrder();

                $validator = Validator::make($request->toArray(), [
                    "order" => "numeric|max:$maxOrder"
                ]);

                if ($validator->fails()) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_INVALID_DATA'),
                        $validator->errors()->first()
                    );
                }

                $newOrder = $currentOrder < $requestOrder ? $currentOrder : $requestOrder + 1;

                $records = $this->userCustomFieldRepository->findBetweenOrder($currentOrder, $requestOrder);
                foreach ($records as $record) {
                    $record->order = $newOrder;
                    $this->userCustomFieldRepository->update($record->toArray(), $record->field_id);
                    $newOrder++;
                }

                $fieldDetail->order = $requestOrder;
                $data = $fieldDetail;
            }

            $customField = $this->userCustomFieldRepository->update($data->toArray(), $id);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_CUSTOM_FIELD_UPDATED');
            $apiData = ['field_id' => $customField['field_id']];

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.USERS_CUSTOM_FIELD'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $customField['field_id']
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_CUSTOM_FIELD_NOT_FOUND')
            );
        }
    }

    /**
     * Display the specified user custom field detail.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $fieldDetail = $this->userCustomFieldRepository->find($id);

            $apiData = $fieldDetail->toArray();
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_CUSTOM_FIELD_FOUND');

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_CUSTOM_FIELD_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $payload = $request->toArray();

            if ($request->has('id') && !is_array($payload['id'])) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_INVALID_DATA'),
                    'Field id must be an array'
                );
            }

            $customMessage['integer'] = 'The custom field ID must be an integer.';
            $validator = Validator::make($payload, [
                'id' => 'sometimes|required|array',
                'id.*' => 'sometimes|required|integer'
            ], $customMessage);
            $payload['id'][] = $id;

            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            if ((!empty($request->all()) && !$request->has('id')) || count(['id']) < count($request->all())) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_INVALID_DATA'),
                    'Field id is the only required parameter'
                );
            }

            $method = 'deleteMultiple';
            $payload = $payload['id'];
            if (count($payload) === 1) {
                $method = 'delete';
                $payload = $id;
            }

            $minOrder = $this->userCustomFieldRepository->findMinOrder($payload);
            $minOrder = is_array($payload) ? $minOrder : $minOrder->order;

            $this->userCustomFieldRepository->{$method}($payload);

            if (!empty($minOrder)) {
                $records = $this->userCustomFieldRepository->findAfterMinOrder($minOrder);

                foreach ($records as $record) {
                    $record->order = $minOrder;
                    $this->userCustomFieldRepository->update($record->toArray(), $record->field_id);
                    $minOrder++;
                }
            }

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_CUSTOM_FIELD_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.USERS_CUSTOM_FIELD'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                [],
                null,
                $id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_CUSTOM_FIELD_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_CUSTOM_FIELD_NOT_FOUND')
            );
        }
    }
}
