<?php

namespace App\Http\Controllers\Admin\Timesheet;

use App\Helpers\LanguageHelper;
use App\Repositories\MissionApplication\MissionApplicationQuery;
use App\Repositories\Timesheet\TimesheetQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Traits\RestExceptionHandlerTrait;
use App\Helpers\ResponseHelper;
use App\Repositories\User\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Timesheet\TimesheetRepository;
use Illuminate\Support\Facades\Log;
use Validator;
use Illuminate\Http\JsonResponse;
use App\Events\User\UserNotificationEvent;
use App\Events\User\UserActivityLogEvent;
use Illuminate\Validation\Rule;

//!  Timesheet controller
/*!
This controller is responsible for handling timesheet listing and update operations.
 */
class TimesheetController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Repositories\Timesheet\TimesheetRepository
     */
    private $timesheetRepository;

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
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Repositories\Timesheet\TimesheetRepository $timesheetRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        TimesheetRepository $timesheetRepository,
        ResponseHelper $responseHelper,
        Request $request
    ) {
        $this->userRepository = $userRepository;
        $this->timesheetRepository = $timesheetRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey =$request->header('php-auth-user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $userId
     * @return Illuminate\Http\JsonResponse
     */
    public function index(int $userId, Request $request): JsonResponse
    {
        try {
            $user = $this->userRepository->find($userId);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }

        $userTimesheetData = $this->timesheetRepository->getUserTimesheet($userId, $request);
        foreach ($userTimesheetData as $userTimesheet) {
            if ($userTimesheet->missionLanguage) {
                $userTimesheet->setAttribute('title', $userTimesheet->missionLanguage[0]->title);
                unset($userTimesheet->missionLanguage);
            }
            $userTimesheet->setAppends([]);
        }

        $apiData = $userTimesheetData->toArray();
        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!empty($apiData)) ?
        trans('messages.success.MESSAGE_TIMESHEET_ENTRIES_LISTING') :
        trans('messages.success.MESSAGE_NO_TIMESHEET_ENTRIES_FOUND');
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param TimesheetQuery $timesheetQuery
     * @return JsonResponse
     */
    public function getTimesheetsDetails(
        Request $request,
        TimesheetQuery $timesheetQuery,
        LanguageHelper $languageHelper
    ): JsonResponse
    {
        $filters = $request->get('filters', []);
        $search = $request->get('search');
        $andSearch = $request->get('andSearch', false);
        $order = $request->get('order', []);
        $limit = $request->get('limit', []);
        $tenantLanguages = $languageHelper->getTenantLanguages($request);

        $timesheetList = $timesheetQuery->run([
            'filters' => $filters,
            'andSearch' => $andSearch,
            'search' => $search,
            'order' => $order,
            'limit' => $limit,
            'tenantLanguages' => $tenantLanguages
        ]);

        return $this->responseHelper->successWithPagination(
            Response::HTTP_OK,
            '',
            $timesheetList
        );
    }

    /**
     * Update a timesheet entry
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $timesheetId
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $timesheetId): JsonResponse
    {
        try {
            $validationRules = [
                "status" => ["required", Rule::in(config('constants.timesheet_status'))],
                "notes" => "sometimes",
                "dateVolunteered" => "sometimes|date_format:Y-m-d",
                "dayVolunteered" => ["sometimes", Rule::in(config('constants.day_volunteered'))],
                'hours' => 'required_if:mission_type,TIME|integer|between:0,23',
                'minutes' => 'required_if:mission_type,TIME|integer|between:0,59',
            ];

            if ($request->mission_type === 'GOAL') {
                $validationRules['action'] = 'required|integer|min:1';
            }

            // Server side validations
            $validator = Validator::make(
                $request->all(),
                $validationRules
            );

            // If request parameter have any error
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                    $validator->errors()->first()
                );
            }

            $oldTimesheet = $this->timesheetRepository->find($timesheetId);

            if (isset($request->hours) || isset($request->minutes)) {
                $time = $request->hours . ":" . $request->minutes;
                $request->request->add(['time' => $time]);
                // Remove extra params
                $request->request->remove('hours');
                $request->request->remove('minutes');
            }

            $this->timesheetRepository->updateTimesheet($request, $timesheetId);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_TIMESETTING_STATUS_UPDATED');
            $apiData = ['timesheet_id' => $timesheetId];

            // Send notification to user
            $timesheetDetails = $this->timesheetRepository->getDetailsOfTimesheetEntry($timesheetId);
            if ($timesheetDetails->mission->mission_type === config('constants.mission_type.TIME')) {
                $notificationType = config('constants.notification_type_keys.VOLUNTEERING_HOURS');
            } else {
                $notificationType = config('constants.notification_type_keys.VOLUNTEERING_GOALS');
            }

            if ($oldTimesheet->status !== $request->status) {
                switch ($request->status) {
                    case config('constants.timesheet_status.APPROVED'):
                        $activityLogStatus = config('constants.activity_log_actions.APPROVED');
                        break;
                    case config('constants.timesheet_status.DECLINED'):
                        $activityLogStatus = config('constants.activity_log_actions.DECLINED');
                        break;
                    default:
                        $activityLogStatus = config('constants.activity_log_actions.UPDATED');
                }

                $action = config('constants.notification_actions.' . $timesheetDetails->status);
            } else {
                $activityLogStatus = config('constants.activity_log_actions.UPDATED');
                $action = config('constants.notification_actions.UPDATED');
            }

            event(new UserNotificationEvent($notificationType, $timesheetId, $action, $timesheetDetails->user_id));

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.VOLUNTEERING_TIMESHEET'),
                $activityLogStatus,
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                $request->toArray(),
                null,
                $timesheetId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_TIMESHEET_ENTRY_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TIMESHEET_ENTRY_NOT_FOUND')
            );
        }
    }

    public function getSumOfUsersTotalMinutes(
        TimesheetRepository $timesheetRepository
    ): JsonResponse
    {
        $totalMinutes = $timesheetRepository->getSumOfUsersTotalMinutes();
        $apiStatus = Response::HTTP_OK;
        return $this->responseHelper->success($apiStatus, '', ['total_minutes' => $totalMinutes]);
    }
}
