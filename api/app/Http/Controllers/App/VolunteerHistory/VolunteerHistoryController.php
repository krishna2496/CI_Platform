<?php

namespace App\Http\Controllers\App\VolunteerHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Traits\RestExceptionHandlerTrait;
use App\Repositories\MissionTheme\MissionThemeRepository;
use App\Repositories\MissionSkill\MissionSkillRepository;
use App\Helpers\LanguageHelper;
use App\Helpers\ExportCSV;
use App\Helpers\Helpers;
use App\Events\User\UserActivityLogEvent;

//!  Volunteerhistory controller
/*!
This controller is responsible for handling volunteerhistory theme, goal, time history and export operations.
 */
class VolunteerHistoryController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Repositories\Timesheet\TimesheetRepository
     */
    private $timesheetRepository;

    /**
     * @var App\Repositories\MissionTheme\MissionThemeRepository
     */
    private $missionThemeRepository;

    /**
     * @var App\Repositories\MissionSkill\MissionSkillRepository
     */
    private $missionSkillRepository;

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
     * Create a new controller instance.
     *
     * @param App\Repositories\Timesheet\TimesheetRepository $timesheetRepository
     * @param App\Repositories\MissionTheme\MissionThemeRepository $missionThemeRepository
     * @param App\Repositories\MissionSkill\MissionSkillRepository $missionSkillRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers $helpers
     *
     * @return void
     */
    public function __construct(
        TimesheetRepository $timesheetRepository,
        MissionThemeRepository $missionThemeRepository,
        MissionSkillRepository $missionSkillRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Helpers $helpers
    ) {
        $this->timesheetRepository = $timesheetRepository;
        $this->missionThemeRepository = $missionThemeRepository;
        $this->missionSkillRepository = $missionSkillRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
    }

    /**
     * Get all themes history with total minutes logged, based on year and all years.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function themeHistory(Request $request): JsonResponse
    {
        $userId = $request->auth->user_id;
        $themeTimeHistory = $this->missionThemeRepository->getHoursPerTheme($request->year, $userId);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = (!empty($themeTimeHistory->toArray())) ?
        trans('messages.success.MESSAGE_THEME_HISTORY_PER_HOUR_LISTED'):
        trans('messages.success.MESSAGE_THEME_HISTORY_NOT_FOUND');
        $apiData = $themeTimeHistory->toArray();

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Get all skill history with total minutes logged, based on year and all years.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function skillHistory(Request $request): JsonResponse
    {
        $userId = $request->auth->user_id;
        $skillTimeHistory = $this->missionSkillRepository->getHoursPerSkill($request->year, $userId);

        $apiStatus = Response::HTTP_OK;
        $apiMessage =  (!empty($skillTimeHistory->toArray())) ?
        trans('messages.success.MESSAGE_SKILL_HISTORY_PER_HOUR_LISTED'):
        trans('messages.success.MESSAGE_SKILL_HISTORY_NOT_FOUND');
        $apiData = $skillTimeHistory->toArray();

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Get all user mission with total time entry for each mission
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function timeMissionHistory(Request $request): JsonResponse
    {
        $statusArray = [
            config('constants.timesheet_status.AUTOMATICALLY_APPROVED'),
            config('constants.timesheet_status.APPROVED')
        ];

        $timeMissionList = $this->timesheetRepository->timeRequestList($request, $statusArray);

        $apiMessage = (count($timeMissionList) > 0) ?
        trans('messages.success.MESSAGE_TIME_MISSION_TIME_ENTRY_LISTED') :
        trans('messages.success.MESSAGE_NO_TIME_MISSION_TIME_ENTRY_FOUND');

        return $this->responseHelper->successWithPagination(Response::HTTP_OK, $apiMessage, $timeMissionList);
    }

    /**
     * Get all skill history with total minutes logged, based on year and all years.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function goalMissionHistory(Request $request): JsonResponse
    {
        $statusArray = [
            config('constants.timesheet_status.AUTOMATICALLY_APPROVED'),
            config('constants.timesheet_status.APPROVED')
        ];

        $goalMissionList = $this->timesheetRepository->goalRequestList($request, $statusArray);

        $apiMessage = (count($goalMissionList) > 0) ?
        trans('messages.success.MESSAGE_GOAL_MISSION_TIME_ENTRY_LISTED') :
        trans('messages.success.MESSAGE_NO_GOAL_MISSION_TIME_ENTRY_FOUND');

        return $this->responseHelper->successWithPagination(Response::HTTP_OK, $apiMessage, $goalMissionList);
    }

    /**
     * Export user's goal mission history
     *
     * @param \Illuminate\Http\Request $request
     * @return Object
     */
    public function exportGoalMissionHistory(Request $request): Object
    {
        $statusArray = [
            config('constants.timesheet_status.AUTOMATICALLY_APPROVED'),
            config('constants.timesheet_status.APPROVED')
        ];

        $goalMissionList = $this->timesheetRepository->goalRequestList($request, $statusArray, false);

        if ($goalMissionList->count()) {
            $fileName = config('constants.export_timesheet_file_names.GOAL_MISSION_HISTORY_XLSX');

            $excel = new ExportCSV($fileName);

            $headings = [
                trans('general.export_sheet_headings.MISSION_NAME'),
                trans('general.export_sheet_headings.ORGANIZATION_NAME'),
                trans('general.export_sheet_headings.ACTIONS')
            ];

            $excel->setHeadlines($headings);

            foreach ($goalMissionList as $mission) {
                $excel->appendRow([
                    strip_tags(preg_replace('~[\r\n]+~', '', $mission->title)),
                    strip_tags(preg_replace('~[\r\n]+~', '', $mission->organization_name)),
                    $mission->action
                ]);
            }

            $tenantName = $this->helpers->getSubDomainFromRequest($request);

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.GOAL_MISSION_TIMESHEET'),
                config('constants.activity_log_actions.EXPORT'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                $goalMissionList->toArray(),
                null,
                $request->auth->user_id
            ));

            $path = $excel->export('app/'.$tenantName.'/timesheet/'.$request->auth->user_id.'/exports');
            return response()->download($path, $fileName);
        }

        $apiStatus = Response::HTTP_OK;
        $apiMessage =  trans('messages.success.MESSAGE_ENABLE_TO_EXPORT_USER_GOAL_MISSION_HISTORY');
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Export user's time mission history
     *
     * @param \Illuminate\Http\Request $request
     * @return Object
     */
    public function exportTimeMissionHistory(Request $request): Object
    {
        $statusArray = [
            config('constants.timesheet_status.AUTOMATICALLY_APPROVED'),
            config('constants.timesheet_status.APPROVED')
        ];

        $timeRequestList = $this->timesheetRepository->timeRequestList($request, $statusArray, false);

        if ($timeRequestList->count()) {
            $fileName = config('constants.export_timesheet_file_names.TIME_MISSION_HISTORY_XLSX');

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
                config('constants.activity_log_types.TIME_MISSION_TIMESHEET'),
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
        $apiMessage =  trans('messages.success.MESSAGE_ENABLE_TO_EXPORT_USER_TIME_MISSION_HISTORY');
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
