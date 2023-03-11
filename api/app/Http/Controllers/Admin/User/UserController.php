<?php
namespace App\Http\Controllers\Admin\User;

use App\Events\User\UserActivityLogEvent;
use App\Exceptions\MaximumUsersReachedException;
use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\Timezone\TimezoneRepository;
use App\Services\TimesheetService;
use App\Services\UserService;
use App\Traits\RestExceptionHandlerTrait;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Validator;

//!  User controller
/*!
This controller is responsible for handling user listing, show, store, update, delete,
link skill, unlink skill and user skill listing operations.
 */
class UserController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Services\UserService
     */
    private $timesheetService;

    /**
     * @var App\Services\TimesheetService
     */
    private $userService;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    private $notificationRepository;

    /**
     * @var App\Repositories\Timezone\TimezoneRepository
     */
    private $timezoneRepository;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\ResponseHelper $languageHelper
     * @param App\Services\UserService $userService
     * @param App\Helpers\Helpers $helpers
     * @param Illuminate\Http\Request $request
     * @param App\Repositories\Notification\NotificationRepository $notificationRepository
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        UserService $userService,
        TimesheetService $timesheetService,
        Helpers $helpers,
        Request $request,
        NotificationRepository $notificationRepository,
        TimezoneRepository $timezoneRepository
    ) {
        $this->userRepository = $userRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->userService = $userService;
        $this->timesheetService = $timesheetService;
        $this->helpers = $helpers;
        $this->userApiKey = $request->header('php-auth-user');
        $this->notificationRepository = $notificationRepository;
        $this->timezoneRepository = $timezoneRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $users = $this->userRepository->userList($request);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($users->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND')
             : trans('messages.success.MESSAGE_USER_LISTING');
            return $this->responseHelper->successWithPagination(Response::HTTP_OK, $apiMessage, $users);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Display specific user content statistics
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function contentStatistics(Request $request, $userId): JsonResponse
    {

        try {
            $user = $this->userService->findById($userId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }

        $statistics = $this->userService->statistics($user, $request->all());

        $data = $statistics;
        $status = Response::HTTP_OK;
        $message = trans('messages.success.MESSAGE_TENANT_USER_CONTENT_STATISTICS_SUCCESS');

        return $this->responseHelper->success($status, $message, $data);

    }

    /**
     * Get user's volunteer summary
     *
     * @param \Illuminate\Http\Request $request
     * @param String $userId
     *
     * @return JsonResponse
     */
    public function volunteerSummary(Request $request, $userId): JsonResponse
    {
        try {
            $user = $this->userService->findById($userId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }

        $data = $this->userService->volunteerSummary($user, $request->all());

        $status = Response::HTTP_OK;
        $message = trans('messages.success.MESSAGE_TENANT_USER_VOLUNTEER_SUMMARY_SUCCESS');
        return $this->responseHelper->success($status, $message, $data);
    }

    /**
     * Display specific user timesheet summary
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function timesheetSummary(Request $request, $userId): JsonResponse
    {

        try {
            $user = $this->userService->findById($userId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }

        $data = $this->timesheetService->summary($user, $request->all());
        $status = Response::HTTP_OK;
        $message = trans('messages.success.MESSAGE_TENANT_USER_TIMESHEET_SUMMARY_SUCCESS');

        return $this->responseHelper->success($status, $message, $data);

    }

    /**
     * Display specific user timesheet
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function timesheet(Request $request, $userId): JsonResponse
    {

        try {
            $user = $this->userRepository->find($userId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }

        $timesheets = $this->userRepository->getMissionTimesheet($request, $userId);

        $data = $timesheets->toArray();
        $status = $timesheets->isEmpty() ? Response::HTTP_NOT_FOUND : Response::HTTP_OK;
        $message = $timesheets->isEmpty() ? trans('messages.success.MESSAGE_TENANT_USER_TIMESHEET_EMPTY') : trans('messages.success.MESSAGE_TENANT_USER_TIMESHEET_SUCCESS');

        return $this->responseHelper->success($status, $message, $data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validation = $this->userService->validateFields($request->all());
        if ($validation !== true) {
            return $validation;
        }

        if (isset($request->language_id) && !$this->languageHelper->validateLanguageId($request)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                trans('messages.custom_error_message.ERROR_USER_INVALID_LANGUAGE')
            );
        }

        if (!isset($request->timezone_id)) {
            $defaultTimezone = env('DEFAULT_TIMEZONE', 'Europe/Paris');
            $timezone = $this->timezoneRepository->getTenantTimezoneByCode($defaultTimezone);
            $request->merge(['timezone_id' => $timezone->timezone_id]);
        }

        // If language is not on the request data set the tenant default language
        if (!isset($request->language_id)) {
            $defaultLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
            $request->merge(['language_id' => $defaultLanguage->language_id]);
        }

        $request->merge([
            'expiry' => (isset($request->expiry) && $request->expiry) ? $request->expiry : null,
            'pseudonymize_at' => null
        ]);
        try {
            $user = $this->userService->store($request->all());
        } catch (MaximumUsersReachedException $e) {
            return $this->responseHelper->error(
                Response::HTTP_BAD_REQUEST,
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                config('constants.error_codes.ERROR_MAXIMUM_USERS_REACHED'),
                trans('messages.custom_error_message.ERROR_MAXIMUM_USERS_REACHED')
            );
        }

        $this->userRepository->checkProfileCompleteStatus($user->user_id, $request);
        $data = $request->except(['password']); // Remove password before logging it
        if ($request->skills) {
            $this->userService->linkSkill($data, $user->user_id);
        }

        $this->createActivityLogForUser(
            config('constants.activity_log_actions.CREATED'),
            $data,
            $user->user_id
        );

        return $this->responseHelper->success(
            Response::HTTP_CREATED,
            trans('messages.success.MESSAGE_USER_CREATED'),
            ['user_id' => $user->user_id]
        );
    }

    /**
     * Display the specified user detail.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $userDetail = $this->userRepository->find($id);

            $apiData = $userDetail->toArray();
            $apiData['avatar'] = ((isset($apiData['avatar'])) && $apiData['avatar'] !="") ? $apiData['avatar'] : '';
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_USER_FOUND');

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $validation = $this->userService->validateFields($request->all(), $id);
        if ($validation !== true) {
            return $validation;
        }

        if (isset($request->language_id) && !$this->languageHelper->validateLanguageId($request)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                trans('messages.custom_error_message.ERROR_USER_INVALID_LANGUAGE')
            );
        }

        $data = [
            'avatar' => (isset($request->avatar) && !empty($request->avatar)) ? $request->avatar : null,
            'expiry' => (isset($request->expiry) && $request->expiry) ? $request->expiry : null
        ];
        if (isset($request->status)) {
            $data['status'] = ($request->status) ? config('constants.user_statuses.ACTIVE') : config('constants.user_statuses.INACTIVE');
        }
        $request->merge($data);

        try {
            $userDetail = $this->userService->findById($id);
            $data = $request->all();

            // Skip updating pseudonymize fields
            if ($userDetail->pseudonymize_at && $userDetail->pseudonymize_at !== '0000-00-00 00:00:00') {
                $data = $this->userService->unsetPseudonymizedFields($data);
            }
            // Set user status to inactive when pseudonymized
            if (($userDetail->pseudonymize_at === '0000-00-00 00:00:00' || $userDetail->pseudonymize_at === null) &&
                array_key_exists('pseudonymize_at', $data)
            ) {
                $data['status'] = config('constants.user_statuses.INACTIVE');
            }

            $user = $this->userService->update($data, $id); // Update user
            $this->userRepository->checkProfileCompleteStatus($user->user_id, $request); // Check profile complete status
            $data = $request->except(['password']); // Remove password before logging it

            if ($request->skills) {
                $this->userService->updateSkill($data, $id);
            }

            $this->createActivityLogForUser(
                config('constants.activity_log_actions.UPDATED'),
                $data,
                $user->user_id
            );
            return $this->responseHelper->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_UPDATED'),
                ['user_id' => $user->user_id]
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = $this->userRepository->delete($id);
            $this->notificationRepository->deleteAllNotifications($id);

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_USER_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.USERS'),
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
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function linkSkill(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->toArray(), [
                'skills' => 'required',
                'skills.*.skill_id' => 'required|exists:skill,skill_id,deleted_at,NULL',
            ]);

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_SKILL_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }
            $linkedSkills = $this->userRepository->linkSkill($request->toArray(), $id);

            foreach ($linkedSkills as $linkedSkill) {
                // Make activity log
                event(new UserActivityLogEvent(
                    config('constants.activity_log_types.USER_SKILL'),
                    config('constants.activity_log_actions.LINKED'),
                    config('constants.activity_log_user_types.API'),
                    $this->userApiKey,
                    get_class($this),
                    $request->toArray(),
                    null,
                    $linkedSkill['skill_id']
                ));
            }
            // Set response data
            $apiStatus = Response::HTTP_CREATED;
            $apiMessage = trans('messages.success.MESSAGE_USER_SKILLS_CREATED');
            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $userId
     * @return Illuminate\Http\JsonResponse
     */
    public function unlinkSkill(Request $request, int $userId): JsonResponse
    {
        try {
            // Server side validataions
            $validator = Validator::make($request->toArray(), [
                'skills' => 'required',
                'skills.*.skill_id' => 'required|exists:skill,skill_id',
            ]);

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_SKILL_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            $unlinkedIds = $this->userRepository->unlinkSkill($request->toArray(), $userId);

            foreach ($unlinkedIds as $unlinkedId) {
                // Make activity log
                event(new UserActivityLogEvent(
                    config('constants.activity_log_types.USER_SKILL'),
                    config('constants.activity_log_actions.UNLINKED'),
                    config('constants.activity_log_user_types.API'),
                    $this->userApiKey,
                    get_class($this),
                    $request->toArray(),
                    null,
                    $unlinkedId['skill_id']
                ));
            }
            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_USER_SKILLS_DELETED');

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $userId
     * @return Illuminate\Http\JsonResponse
     */
    public function userSkills(int $userId): JsonResponse
    {
        try {
            $skillList = $this->userRepository->userSkills($userId);

            // Set response data
            $apiData = (count($skillList) > 0) ? $skillList->toArray() : [];
            $responseMessage = (count($skillList) > 0) ? trans('messages.success.MESSAGE_SKILL_LISTING')
             : trans('messages.success.MESSAGE_NO_RECORD_FOUND');
            return $this->responseHelper->success(Response::HTTP_OK, $responseMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
    }

    private function createActivityLogForUser($activityLogAction, $request, $userId)
    {
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.USERS'),
            $activityLogAction,
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request,
            null,
            $userId
        ));
    }
}
