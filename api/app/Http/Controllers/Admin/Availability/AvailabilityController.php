<?php
namespace App\Http\Controllers\Admin\Availability;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Availability\AvailabilityRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Illuminate\Validation\Rule;
use App\Events\User\UserActivityLogEvent;

//!  Availability controller
/*!
This controller is responsible for handling availability store, update, listing, show and delete operations.
 */
class AvailabilityController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\Availability\AvailabilityRepository;
     */
    private $availabilityRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * Create a new availability controller instance
     *
     * @param App\Repositories\Availability\AvailabilityRepository;
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        AvailabilityRepository $availabilityRepository,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        $this->availabilityRepository = $availabilityRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey = $request->header('php-auth-user');
    }

    /**
     * Display a listing of availability.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get availability lists
            $availabilityLists = $this->availabilityRepository->getAvailabilityList($request);

            // Set response data
            $apiData = $availabilityLists;
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($availabilityLists->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND')
                : trans('messages.success.MESSAGE_AVAILABILITY_LISTING');
            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $apiData);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Store a newly created availability.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validations
        $validator = Validator::make(
            $request->all(),
            [
                "type" => "required|max:64|unique:availability,type,NULL,availability_id,deleted_at,NULL",
                "translations" => "required",
                "translations.*.lang" => "required_with:translations|max:2",
                "translations.*.title" => "required_with:translations|max:255"
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_AVAILABILITY_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        // Create new availability
        $availability = $this->availabilityRepository->store($request->all());

        // Set response data
        $apiData = ['availability_id' => $availability->availability_id];
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_AVAILABILITY_CREATED');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.AVAILABILITY'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $availability->availability_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update availability details.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $availabilityId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $availabilityId): JsonResponse
    {
        try {
            $this->availabilityRepository->find($availabilityId);
            // Server side validations
            $validator = Validator::make(
                $request->all(),
                [
                    "type" => [
                        "sometimes",
                        "required",
                        Rule::unique('availability')->ignore($availabilityId, 'availability_id,deleted_at,NULL')
                    ],
                    "translations" => "sometimes|required",
                    "translations.*.lang" => "required_with:translations|max:2",
                    "translations.*.title" => "required_with:translations|max:255"
                ]
            );

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_AVAILABILITY_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            // Update availability details
            $availability = $this->availabilityRepository->update($request->toArray(), $availabilityId);

            // Set response data
            $apiData = ['availability_id' => $availability->availability_id];
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_AVAILABILITY_UPDATED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.AVAILABILITY'),
                config('constants.activity_log_actions.UPDATED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $availability->availability_id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_AVAILABILITY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_AVAILABILITY_NOT_FOUND')
            );
        }
    }

    /**
     * Display availability detail.
     *
     * @param int $availabilityId
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $availabilityId): JsonResponse
    {
        try {
            $availability = $this->availabilityRepository->find($availabilityId);

            $apiData = $availability->toArray();
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_AVAILABILITY_FOUND');

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_AVAILABILITY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_AVAILABILITY_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $availabilityId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $availabilityId): JsonResponse
    {
        try {

            if ($this->availabilityRepository->hasMission($availabilityId) || $this->availabilityRepository->hasUser($availabilityId)) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_AVAILABILITY_UNABLE_TO_DELETE'),
                    trans('messages.custom_error_message.ERROR_AVAILABILITY_UNABLE_TO_DELETE')
                );
            }

            $availability = $this->availabilityRepository->delete($availabilityId);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_AVAILABILITY_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.AVAILABILITY'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                [],
                null,
                $availabilityId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_AVAILABILITY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_AVAILABILITY_NOT_FOUND')
            );
        }
    }
}
