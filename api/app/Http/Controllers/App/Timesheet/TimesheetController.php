<?php

namespace App\Http\Controllers\App\Timesheet;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Traits\RestExceptionHandlerTrait;
use Bschmitt\Amqp\Amqp;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Validator;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Helpers\ExportCSV;
use App\Helpers\Helpers;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\Mission;
use App\Events\User\UserActivityLogEvent;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;

//!  Timesheet controller
/*!
This controller is responsible for handling timesheet store/update, export, submit and show operations.
 */
class TimesheetController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\Timesheet\TimesheetRepository
     */
    private $timesheetRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * @var App\Repositories\TenantOption\TenantOptionRepository
     */
    private $tenantOptionRepository;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * Create a new controller instance.
     *
     * @param TimesheetRepository $timesheetRepository
     * @param ResponseHelper $responseHelper
     * @param MissionRepository $missionRepository
     * @param TenantOptionRepository $tenantOptionRepository
     * @param Helpers $helpers
     * @param Amqp $amqp
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     *
     * @return void
     */
    public function __construct(
        TimesheetRepository $timesheetRepository,
        ResponseHelper $responseHelper,
        MissionRepository $missionRepository,
        TenantOptionRepository $tenantOptionRepository,
        Helpers $helpers,
        Amqp $amqp,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository
    ) {
        $this->timesheetRepository = $timesheetRepository;
        $this->responseHelper = $responseHelper;
        $this->missionRepository = $missionRepository;
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->helpers = $helpers;
        $this->amqp = $amqp;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
    }

    /**
     * Get all timesheet entries
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->toArray(), [
            'type' => 'required|in:hour,goal'
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TIMESHEET_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        $isRequiredSettingEnabled = $this->missionTypeTenantSettingStatus(
            $request,
            $request->type
        );
        if (!$isRequiredSettingEnabled) {
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_TENANT_SETTING_DISABLED'),
                trans('messages.custom_error_message.ERROR_TENANT_SETTING_DISABLED')
            );
        }

        $timesheetEntries = $this->timesheetRepository->getAllTimesheetEntries($request, $request->type);
        $apiData = $timesheetEntries;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = ($timesheetEntries->total() > 0)  ?
        trans('messages.success.MESSAGE_TIMESHEET_ENTRIES_LISTING') :
        trans('messages.success.MESSAGE_NO_TIMESHEET_ENTRIES_FOUND');
        return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Store/Update timesheet
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse;
     */
    public function store(Request $request): JsonResponse
    {
        if (!empty($request->mission_id)) {
            //Fetch mission type
            $getmissionType = $this->missionRepository->getMissionType($request->mission_id);
            $getmissionType->count() > 0 ?
            $request->request->add(['mission_type' => $getmissionType[0]['mission_type']]) : null;
        }

        $documentSizeLimit = config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT');
        $validator = Validator::make(
            $request->toArray(),
            [
                'mission_id' => 'required|exists:mission,mission_id,deleted_at,NULL',
                'date_volunteered' => 'required|date_format:Y-m-d|before:tomorrow',
                'day_volunteered' => ['required', Rule::in(config('constants.day_volunteered'))],
                'documents.*' => 'max:' . $documentSizeLimit . '|valid_timesheet_document_type',
                'action' => 'required_if:mission_type,GOAL|integer|min:1',
                'hours' => 'required_if:mission_type,TIME|integer|between:0,23',
                'minutes' => 'required_if:mission_type,TIME|integer|between:0,59',
            ]
        );

        // If validator fails
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TIMESHEET_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }

        try {
            // Fetch mission application data
            $missionApplicationData = $this->missionRepository->getMissionApplication(
                $request->mission_id,
                $request->auth->user_id,
                config('constants.application_status.AUTOMATICALLY_APPROVED')
            );
        } catch (ModelNotFoundException $e) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_DATA_FOR_TIMESHEET_ENTRY'),
                trans('messages.custom_error_message.ERROR_INVALID_DATA_FOR_TIMESHEET_ENTRY')
            );
        }

        $dateVolunteered = $this->helpers->changeDateFormat(
            $request->date_volunteered,
            config('constants.TIMESHEET_DATE_FORMAT')
        );

        $dateVolunteeredCurrentTime = $this->helpers->changeDateFormat(
            $request->date_volunteered." ".date("H:i:s"),
            config('constants.TIMESHEET_DATE_TIME_FORMAT')
        );

        $dateVolunteeredCurrentTime = Carbon::parse($dateVolunteeredCurrentTime)
        ->setTimezone(config('constants.TIMEZONE'))
        ->format(config('constants.DB_DATE_TIME_FORMAT'));

        $statusArray = array(config('constants.timesheet_status.APPROVED'),
        config('constants.timesheet_status.AUTOMATICALLY_APPROVED'));

        // Get timesheet details
        $timesheetData = $this->timesheetRepository->getTimesheetDetails(
            $request->mission_id,
            $request->auth->user_id,
            $dateVolunteered,
            $statusArray
        );
        if ($timesheetData->count() > 0) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TIMESHEET_ALREADY_APPROVED'),
                trans('messages.custom_error_message.ERROR_TIMESHEET_ALREADY_APPROVED')
            );
        } else {
            $request->request->add(['status' => config('constants.timesheet_status.PENDING')]);
        }

        // Fetch mission data from missionid
        $timesheetMissionData = $this->missionRepository->getTimesheetMissionData($request->mission_id);

        // Check mission type
        switch ($timesheetMissionData->mission_type) {
            case config('constants.mission_type.GOAL'):
                // Remove extra params
                $request->request->remove('hours');
                $request->request->remove('minutes');

                $missionDetail = $timesheetMissionData->toArray();

                // Fetch all submitted goal actions from database
                $totalSubmittedGoalActions = $this->timesheetRepository->getSubmittedActions($request->mission_id);

                // Add total actions
                $totalGoalActions = $totalSubmittedGoalActions + $request->action;

                // Check total goals should not exceed goal objective
                if ($totalGoalActions > $missionDetail["goal_mission"]["goal_objective"]) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_INVALID_ACTION'),
                        trans('messages.custom_error_message.ERROR_INVALID_ACTION')
                    );
                }
                break;

            case config('constants.mission_type.TIME'):
                $time = $request->hours . ":" . $request->minutes;
                $request->request->add(['time' => $time]);
                // Remove extra params
                $request->request->remove('action');
                break;
        }

        // Check start dates and end dates of mission
        if ($timesheetMissionData->start_date) {
            $missionStartDate = $this->helpers->changeDateFormat(
                $timesheetMissionData->start_date,
                config('constants.TIMESHEET_DATE_FORMAT')
            );
            if ($dateVolunteered < $missionStartDate) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_MISSION_STARTDATE'),
                    trans('messages.custom_error_message.ERROR_MISSION_STARTDATE')
                );
            } else {
                if ($timesheetMissionData->end_date) {
                    $missionEndDate = $this->helpers->changeDateFormat(
                        $timesheetMissionData->end_date,
                        config('constants.TIMESHEET_DATE_TIME_FORMAT')
                    );

                    if ($dateVolunteeredCurrentTime > $missionEndDate) {
                        $endDate = Carbon::createFromFormat(
                            config('constants.TIMESHEET_DATE_TIME_FORMAT'),
                            $missionEndDate
                        );

                        // Fetch tenant options value
                        $tenantOptionData = $this->tenantOptionRepository
                        ->getOptionValue('ALLOW_TIMESHEET_ENTRY');

                        $extraWeeks = isset($tenantOptionData[0]['option_value'])
                        ? intval($tenantOptionData[0]['option_value']) : config('constants.ALLOW_TIMESHEET_ENTRY');

                        // Count records
                        if (count($tenantOptionData) > 0 || $extraWeeks > 0) {
                            // Add weeks to mission end date
                            $timeentryEndDate = $endDate->addWeeks($extraWeeks);
                            if ($dateVolunteeredCurrentTime > $timeentryEndDate) {
                                return $this->responseHelper->error(
                                    Response::HTTP_UNPROCESSABLE_ENTITY,
                                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                                    config('constants.error_codes.ERROR_MISSION_ENDDATE'),
                                    trans('messages.custom_error_message.ERROR_MISSION_ENDDATE')
                                );
                            }
                        }
                    }
                }
            }
        }

        // Store timesheet
        $request->request->add(['user_id' => $request->auth->user_id]);
        $timesheet = $this->timesheetRepository->storeOrUpdateTimesheet($request);

        // Set response data
        $apiStatus = ($timesheet->wasRecentlyCreated) ? Response::HTTP_CREATED : Response::HTTP_OK;
        $apiMessage = ($timesheet->wasRecentlyCreated) ? trans('messages.success.TIMESHEET_ENTRY_ADDED_SUCCESSFULLY')
        : trans('messages.success.TIMESHEET_ENTRY_UPDATED_SUCCESSFULLY');
        $apiData = ['timesheet_id' => $timesheet->timesheet_id];

        $requestArray = $request->toArray();
        $activityLogStatus = ($timesheet->wasRecentlyCreated) ?
            config('constants.activity_log_actions.CREATED') : config('constants.activity_log_actions.UPDATED');

        // get the uplaoded file data
        if ($request->hasFile('documents')) {
            //get documents data related to Timesheet that is uploded
            $documentsPath = $this->timesheetRepository->getUploadedTimesheetDocuments(
                $timesheet->timesheet_id,
                count($request->documents)
            );
            $documents = $documentsPath->map->only(['document_path'])->toArray();
            $requestArray ['documents'] = $documents;
        }

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.VOLUNTEERING_TIMESHEET'),
            $activityLogStatus,
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $requestArray,
            $request->auth->user_id,
            $timesheet->timesheet_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Show timesheet data
     *
     * @param \Illuminate\Http\Request $request
     * @param int $timesheetId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $timesheetId): JsonResponse
    {
        try {
            // Fetch timesheet data
            $timesheetData = $this->timesheetRepository->getTimesheetData($timesheetId, $request->auth->user_id);
            $timesheetDetail = $timesheetData->toArray();
            if ($timesheetData->time !== null) {
                $time = explode(":", $timesheetData->time);
                $timesheetDetail += ["hours" => $time[0]];
                $timesheetDetail += ["minutes" => $time[1]];
                unset($timesheetDetail["time"]);
            }
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_TIMESHEET_LISTING');
            return $this->responseHelper->success($apiStatus, $apiMessage, $timesheetDetail);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.TIMESHEET_NOT_FOUND'),
                trans('messages.custom_error_message.TIMESHEET_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the timesheet documents.
     *
     * @param \Illuminate\Http\Request $request
     * @param int  $timesheetId
     * @param int  $documentId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $timesheetId, int $documentId): JsonResponse
    {
        try {
            // Fetch timesheet data
            $timesheetData = $this->timesheetRepository->getTimesheetData($timesheetId, $request->auth->user_id);

            $statusArray = [
                config('constants.timesheet_status.AUTOMATICALLY_APPROVED'),
                config('constants.timesheet_status.APPROVED')
            ];
            if (in_array($timesheetData['status'], $statusArray)) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_APPROVED_TIMESHEET_DOCUMENTS'),
                    trans('messages.custom_error_message.ERROR_APPROVED_TIMESHEET_DOCUMENTS')
                );
            }
            // Delete timesheet document
            try {
                $timesheetDocument = $this->timesheetRepository->delete($documentId, $timesheetId);
            } catch (ModelNotFoundException $e) {
                return $this->modelNotFound(
                    config('constants.error_codes.TIMESHEET_DOCUMENT_NOT_FOUND'),
                    trans('messages.custom_error_message.TIMESHEET_DOCUMENT_NOT_FOUND')
                );
            }

            // Set response data
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_TIMESHEET_DOCUMENT_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.VOLUNTEERING_TIMESHEET_DOCUMENT'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                [],
                $request->auth->user_id,
                $documentId
            ));


            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.TIMESHEET_NOT_FOUND'),
                trans('messages.custom_error_message.TIMESHEET_NOT_FOUND')
            );
        }
    }

    /**
     * Submit timesheet for approval
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitTimesheet(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->toArray(),
                [
                    'timesheet_entries' => 'required',
                    'timesheet_entries.*.timesheet_id' => 'required|exists:timesheet,timesheet_id,deleted_at,NULL',
                ]
            );

            // If validator fails
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TIMESHEET_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            $timesheet = $this->timesheetRepository->submitTimesheet($request, $request->auth->user_id);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = (!$timesheet) ? trans('messages.success.TIMESHEET_ALREADY_SUBMITTED_FOR_APPROVAL') :
            trans('messages.success.TIMESHEET_SUBMITTED_SUCCESSFULLY');

            // Make activity log and send data to the worker
            foreach ($request->timesheet_entries as $data) {
                $timesheetId = $data['timesheet_id'];

                event(new UserActivityLogEvent(
                    config('constants.activity_log_types.VOLUNTEERING_TIMESHEET'),
                    config('constants.activity_log_actions.SUBMIT_FOR_APPROVAL'),
                    config('constants.activity_log_user_types.REGULAR'),
                    $request->auth->email,
                    get_class($this),
                    $request->toArray(),
                    $request->auth->user_id,
                    $timesheetId
                ));
            }
            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.TIMESHEET_NOT_FOUND'),
                trans('messages.custom_error_message.TIMESHEET_NOT_FOUND')
            );
        }
    }

    /**
     * Get Request timesheet
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingTimeRequests(Request $request): JsonResponse
    {
        $statusArray = [config('constants.timesheet_status.SUBMIT_FOR_APPROVAL')];
        $timeRequestList = $this->timesheetRepository->timeRequestList($request, $statusArray);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = (count($timeRequestList) > 0) ? trans('messages.success.MESSAGE_TIME_REQUEST_LISTING') :
        trans('messages.success.MESSAGE_TIME_REQUEST_NOT_FOUND');

        return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $timeRequestList);
    }

    /**
     * Fetch pending goal requests
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function getPendingGoalRequests(Request $request): JsonResponse
    {
        $statusArray = [config('constants.timesheet_status.SUBMIT_FOR_APPROVAL')];
        $goalRequestList = $this->timesheetRepository->goalRequestList($request, $statusArray);

        $apiMessage = (count($goalRequestList) > 0) ? trans('messages.success.MESSAGE_GOAL_REQUEST_LISTING')
        : trans('messages.success.MESSAGE_NO_GOAL_REQUEST_FOUND');
        return $this->responseHelper->successWithPagination(Response::HTTP_OK, $apiMessage, $goalRequestList);
    }

    /**
     * Export all pending time mission time entries.
     *
     * @param Illuminate\Http\Request $request
     * @return Object
     */
    public function exportPendingTimeRequests(Request $request): Object
    {
        $statusArray = [config('constants.timesheet_status.SUBMIT_FOR_APPROVAL')];

        $timeRequestList = $this->timesheetRepository->timeRequestList($request, $statusArray, false);

        if ($timeRequestList->count()) {
            $fileName = config('constants.export_timesheet_file_names.PENDING_TIME_MISSION_ENTRIES_XLSX');

            $excel = new ExportCSV($fileName);

            $headings = [
                trans('general.export_sheet_headings.MISSION_NAME'),
                trans('general.export_sheet_headings.ORGANIZATION_NAME'),
                trans('general.export_sheet_headings.TIME'),
                trans('general.export_sheet_headings.HOURS')
            ];

            $excel->setHeadlines($headings);

            foreach ($timeRequestList as $mission) {
                $excel->appendRow([
                    strip_tags(preg_replace('~[\r\n]+~', '', $mission->title)),
                    strip_tags(preg_replace('~[\r\n]+~', '', $mission->organization_name)),
                    $mission->time,
                    $mission->hours
                ]);
            }

            $tenantName = $this->helpers->getSubDomainFromRequest($request);

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.TIME_TIMESHEET'),
                config('constants.activity_log_actions.EXPORT'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                $timeRequestList->toArray(),
                null,
                $request->auth->user_id
            ));

            $path = $excel->export('app/'.$tenantName.'/timesheet/'.$request->auth->user_id.'/exports');
            return response()->download($path, $fileName);
        }

        $apiStatus = Response::HTTP_OK;
        $apiMessage =  trans('messages.success.MESSAGE_ENABLE_TO_EXPORT_USER_PENDING_TIME_MISSION_ENTRIES');
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Export user's goal mission history
     *
     * @param \Illuminate\Http\Request $request
     * @return Object
     */
    public function exportPendingGoalRequests(Request $request): Object
    {
        $statusArray = [config('constants.timesheet_status.SUBMIT_FOR_APPROVAL')];
        $goalRequestList = $this->timesheetRepository->goalRequestList($request, $statusArray, false);

        if ($goalRequestList->count()) {
            $fileName = config('constants.export_timesheet_file_names.PENTIND_GOAL_MISSION_ENTRIES_XLSX');

            $excel = new ExportCSV($fileName);

            $headings = [
                trans('general.export_sheet_headings.MISSION_NAME'),
                trans('general.export_sheet_headings.ORGANIZATION_NAME'),
                trans('general.export_sheet_headings.ACTIONS')
            ];

            $excel->setHeadlines($headings);

            foreach ($goalRequestList as $mission) {
                $excel->appendRow([
                    strip_tags(preg_replace('~[\r\n]+~', '', $mission->title)),
                    strip_tags(preg_replace('~[\r\n]+~', '', $mission->organization_name)),
                    $mission->action
                ]);
            }

            $tenantName = $this->helpers->getSubDomainFromRequest($request);

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.GOAL_TIMESHEET'),
                config('constants.activity_log_actions.EXPORT'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                $goalRequestList->toArray(),
                null,
                $request->auth->user_id
            ));

            $path = $excel->export('app/'.$tenantName.'/timesheet/'.$request->auth->user_id.'/exports');
            return response()->download($path, $fileName);
        }

        $apiStatus = Response::HTTP_OK;
        $apiMessage =  trans('messages.success.MESSAGE_ENABLE_TO_EXPORT_USER_PENDING_GOAL_MISSION_ENTRIES');
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Check if required tenant setting based on mission type is enabled
     *
     * @param Request $request
     * @param string $missionType
     * @return bool
     */
    private function missionTypeTenantSettingStatus(
        Request $request,
        string $missionType
    ) : bool {

        $tenantSetting = null;
        switch ($missionType) {
            case 'goal':
                $tenantSetting = config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION');
                break;
            case 'hour':
                $tenantSetting = config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION');
                break;
        }

        return $this->tenantActivatedSettingRepository->checkTenantSettingStatus(
            $tenantSetting,
            $request
        );
    }
}
