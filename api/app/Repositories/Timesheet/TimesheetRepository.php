<?php

namespace App\Repositories\Timesheet;

use Bschmitt\Amqp\Amqp;
use DB;
use Carbon\Carbon;
use App\Models\Mission;
use App\Helpers\Helpers;
use App\Helpers\S3Helper;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use App\Helpers\LanguageHelper;
use App\Models\MissionLanguage;
use App\Models\TimesheetDocument;
use Illuminate\Support\Collection;
use App\Repositories\User\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Timesheet\TimesheetInterface;
use App\Repositories\TenantOption\TenantOptionRepository;
use Illuminate\Support\Facades\Log;

class TimesheetRepository implements TimesheetInterface
{
    /**
     * @var App\Models\Timesheet
     */
    private $timesheet;

    /**
     * @var App\Models\Mission
     */
    private $mission;

    /**
     * @var App\Models\MissionLanguage
     */
    private $missionLanguage;

    /**
     * @var App\Models\TimesheetDocument
     */
    private $timesheetDocument;

    /**
     * @var App\Helpers\S3Helper
     */
    private $s3helper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Repositories\TenantOption\TenantOptionRepository
     */
    private $tenantOptionRepository;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var Bschmitt\Amqp\Amqp
     */
    private $amqp;

    /**
     * Create a new Timesheet repository instance.
     *
     * @param  App\Models\Timesheet $timesheet
     * @param  App\Models\Mission $mission
     * @param  App\Models\MissionLanguage $missionLanguage
     * @param  App\Models\TimesheetDocument $timesheetDocument
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Helpers\S3Helper $s3helper
     * @param App\Repositories\TenantOption\TenantOptionRepository $tenantOptionRepository
     * @param App\Repositories\User\UserRepository $userRepository
     * @param Amqp $amqp
     * @return void
     */
    public function __construct(
        Timesheet $timesheet,
        Mission $mission,
        MissionLanguage $missionLanguage,
        TimesheetDocument $timesheetDocument,
        Helpers $helpers,
        LanguageHelper $languageHelper,
        S3Helper $s3helper,
        TenantOptionRepository $tenantOptionRepository,
        UserRepository $userRepository,
        Amqp $amqp
    ) {
        $this->timesheet = $timesheet;
        $this->mission = $mission;
        $this->missionLanguage = $missionLanguage;
        $this->timesheetDocument = $timesheetDocument;
        $this->helpers = $helpers;
        $this->languageHelper = $languageHelper;
        $this->s3helper = $s3helper;
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->userRepository = $userRepository;
        $this->amqp = $amqp;
    }

    /**
     * Store/Update timesheet
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\Timesheet
     */
    public function storeOrUpdateTimesheet(Request $request): Timesheet
    {
        $timesheet = $this->timesheet->updateOrCreate(['user_id' => $request->auth->user_id,
        'mission_id' => $request->mission_id,
        'date_volunteered' => $request->date_volunteered
        ], $request->toArray());

        if ($request->hasFile('documents')) {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $files = $request->file('documents');
            foreach ($files as $file) {
                $filePath = $this->s3helper
                ->uploadDocumentOnS3Bucket(
                    $file,
                    $tenantName,
                    $request->auth->user_id,
                    config('constants.folder_name.timesheet')
                );
                $timesheetDocument = array('timesheet_id' => $timesheet->timesheet_id,
                                        'document_name' => basename($filePath),
                                        'document_type' => pathinfo(basename($filePath), PATHINFO_EXTENSION),
                                        'document_path' => $filePath);
                $this->timesheetDocument->create($timesheetDocument);
            }
        }
        return $timesheet;
    }

    /**
     * Get timesheet entries
     *
     * @param Illuminate\Http\Request $request
     * @param string $type
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllTimesheetEntries(Request $request, string $type): LengthAwarePaginator
    {
        $languageId = $this->languageHelper->getLanguageId($request);
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        $defaultTenantLanguageId = $defaultTenantLanguage->language_id;

        if ($type === 'hour') {
            $timeMissionEntries = $this->getTimesheetEntries($request, config('constants.mission_type.TIME'));
            $timezone = $this->userRepository->getUserTimezone($request->auth->user_id);
            foreach ($timeMissionEntries as $value) {
                if ($value->missionLanguage) {
                    $index = array_search($languageId, array_column($value->missionLanguage->toArray(), 'language_id'));
                    $language = ($index === false) ? $defaultTenantLanguageId : $languageId;
                    $missionLanguage = $value->missionLanguage->where('language_id', $language)->first();

                    // Set mission title
                    $missionTitle = $missionLanguage->title ?? '';
                    $value->setAttribute('title', $missionTitle);
                    unset($value->missionLanguage);
                }

                if ($value->timeMission) {
                    $applicationStartTime = isset($value->timeMission->application_start_time) ?
                    Carbon::parse(
                        $value->timeMission->application_start_time,
                        config('constants.TIMEZONE')
                    )->setTimezone($timezone)->toDateTimeString() : null;

                    $applicationEndTime = isset($value->timeMission->application_end_time) ?
                    Carbon::parse(
                        $value->timeMission->application_end_time,
                        config('constants.TIMEZONE')
                    )->setTimezone($timezone)->toDateTimeString() : null;

                    $value->setAttribute('application_start_time', $applicationStartTime);
                    $value->setAttribute('application_end_time', $applicationEndTime);
                    unset($value->timeMission);
                }
                $value->setAppends([]);
            }
            $timesheetEntries = $timeMissionEntries;
        }

        if ($type === 'goal') {
            $goalMissionEntries = $this->getTimesheetEntries($request, config('constants.mission_type.GOAL'));
            foreach ($goalMissionEntries as $value) {
                if ($value->missionLanguage) {
                    $index = array_search($languageId, array_column($value->missionLanguage->toArray(), 'language_id'));
                    $language = ($index === false) ? $defaultTenantLanguageId : $languageId;
                    $missionLanguage = $value->missionLanguage->where('language_id', $language)->first();

                    // Set mission title
                    $missionTitle = $missionLanguage->title ?? '';
                    $goalObjective = $missionLanguage->objective ?? '';
                    $labelGoalObjective = $missionLanguage->label_goal_objective ?? '';
                    $value->setAttribute('title', $missionTitle);
                    $value->setAttribute('objective', $goalObjective);
                    $value->setAttribute('label_goal_objective', $labelGoalObjective);
                    unset($value->missionLanguage);
                }
            }
            $timesheetEntries = $goalMissionEntries;
        }
        return $timesheetEntries;
    }

    /**
     * Fetch timesheet details
     *
     * @param int $timesheetId
     * @return null|Timesheet
     */
    public function find(int $timesheetId): ?Timesheet
    {
        return $this->timesheet->findOrFail($timesheetId);
    }

    /**
     * Fetch timesheet details
     *
     * @param int $timesheetId
     * @param int $userId
     * @return Timesheet
     */
    public function getTimesheetData(int $timesheetId, int $userId): Timesheet
    {
        return $this->timesheet->findTimesheet($timesheetId, $userId);
    }

    /**
    * Remove the timesheet document.
    *
    * @param  int  $id
    * @param  int  $timesheetId
    * @return bool
    */
    public function delete(int $id, int $timesheetId): bool
    {
        return $this->timesheetDocument->deleteTimesheetDocument($id, $timesheetId);
    }

    /**
     * Display a listing of specified resources.
     *
     * @param int $userId
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getUserTimesheet(int $userId, Request $request): Collection
    {
        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;

        $timesheetQuery = $this->mission->select('mission.mission_id')
        ->where(['publication_status' => config("constants.publication_status")["APPROVED"]])
        ->whereHas('missionApplication', function ($query) use ($userId) {
            $query->where('user_id', $userId)
            ->whereIn('approval_status', [config("constants.application_status")["AUTOMATICALLY_APPROVED"]]);
        })
        ->with(['missionLanguage' => function ($query) use ($languageId) {
            $query->select('mission_language_id', 'mission_id', 'title')
            ->where('language_id', $languageId);
        }])
        ->with(['timesheet' => function ($query) use ($userId, $request) {
            $query->where('user_id', $userId);
            if ($request->has('status') && $request->input('status') !== '') {
                $query->where('status', strtoupper($request->status));
            }
        }]);

        if ($request->has('type') && $request->input('type') !== '' &&
        in_array(strtoupper($request->input('type')), config('constants.mission_type'))) {
            $timesheetQuery->where('mission_type', strtoupper($request->input('type')));
        }

        return $timesheetQuery->get();
    }

    /**
     * Update timesheet field value, based on timesheet_id condition
     *
     * @param Request $request
     * @param int $timesheetId
     * @return bool
     */
    public function updateTimesheet(Request $request, int $timesheetId): bool
    {
        $valueToUpdate = [];
        $timesheet = $this->timesheet->where('timesheet_id', $timesheetId);
        if (isset($request['status'])) {
            $valueToUpdate['status'] = $request['status'];
        }
        if (isset($request['notes'])) {
            $valueToUpdate['notes'] = $request['notes'];
        }
        if (isset($request['dateVolunteered'])) {
            $valueToUpdate['date_volunteered'] = $request['dateVolunteered'];
        }
        if (isset($request['dayVolunteered'])) {
            $valueToUpdate['day_volunteered'] = $request['dayVolunteered'];
        }
        if (isset($request['time'])) {
            $valueToUpdate['time'] = $request['time'];
        }
        if (isset($request['action'])) {
            $valueToUpdate['action'] = $request['action'];
        }

        return $timesheet->update($valueToUpdate);
    }

    /** Update timesheet status on submit
    *
    * @param \Illuminate\Http\Request $request
    * @param int $userId
    * @return bool
    */
    public function submitTimesheet(Request $request, int $userId): bool
    {
        $status = false;
        if ($request->timesheet_entries) {
            foreach ($request->timesheet_entries as $data) {
                $timesheetData = $this->timesheet->where(['user_id' => $userId,
                'timesheet_id' => $data['timesheet_id']])
                ->firstOrFail();
                $timesheetDetails = $timesheetData->toArray();
                if ($timesheetDetails["status"] === config('constants.timesheet_status.PENDING')) {
                    $timesheetData->status = config('constants.timesheet_status.SUBMIT_FOR_APPROVAL');
                    $status = $timesheetData->update();

                    $tenantIdAndSponsorId = $this->helpers->getTenantIdAndSponsorIdFromRequest($request);
                    $timesheetForOptimy = [
                        'activity_type' => 'timesheet',
                        'sponsor_frontend_id' => $tenantIdAndSponsorId->sponsor_id,
                        'tenant_id' => $tenantIdAndSponsorId->tenant_id,
                        'timesheet_id' => $timesheetDetails['timesheet_id'],
                        'user_id' => $timesheetDetails['user_id'],
                        'mission_id' => $timesheetDetails['mission_id'],
                    ];
                    // Send data of the timesheet to Optimy app using "ciSynchronizer" queue from RabbitMQ
                    $this->amqp->publish(
                        'ciSynchronizer',
                        json_encode($timesheetForOptimy), ['queue' => 'ciSynchronizer']
                    );
                }
            }
        }
        return $status;
    }

    /**
     * Get time request details.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $statusArray
     * @param bool $withPagination
     * @return Object
     */
    public function timeRequestList(Request $request, array $statusArray, bool $withPagination = true) : Object
    {
        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        $defaultTenantLanguageId = $defaultTenantLanguage->language_id;

        $timeRequests = $this->mission->query()
        ->leftjoin('organization', 'organization.organization_id', '=', 'mission.organization_id')
        ->select('mission.mission_id', 'organization.name as organization_name');
        $timeRequests->where(['publication_status' => config("constants.publication_status")["APPROVED"],
        'mission_type'=> config('constants.mission_type.TIME')])
        ->with(['missionLanguage' => function ($query) use ($languageId) {
            $query->select('mission_language_id', 'mission_id', 'title', 'language_id');
        }])
        ->whereHas('timesheet', function ($query) use ($request, $statusArray) {
            $query->whereIn('status', $statusArray);
            $query->where('user_id', $request->auth->user_id);
        })
        ->withCount([
        'timesheet AS total_hours' => function ($query) use ($request, $statusArray) {
            $query->select(DB::raw("sum(((hour(time) * 60) + minute(time))) as 'total_minutes'"));
            $query->where('user_id', $request->auth->user_id);
            $query->whereIn('status', $statusArray);
        }]);
        if ($withPagination) {
            $timeRequestsList = $timeRequests->paginate($request->perPage);
        } else {
            $timeRequestsList = $timeRequests->get();
        }
        foreach ($timeRequestsList as $value) {
            if ($value->missionLanguage) {
                $key = array_search($languageId, array_column($value->missionLanguage->toArray(), 'language_id'));
                $language = ($key === false) ? $defaultTenantLanguageId : $languageId;
                $missionLanguage = $value->missionLanguage->where('language_id', $language)->first();

                // Set title
                $missionTitle = $missionLanguage->title ?? '';
                $value->setAttribute('title', $missionTitle);
                unset($value->missionLanguage);
            }

            $value->time = $this->helpers->convertInReportTimeFormat($value->total_hours);
            $value->hours = $this->helpers->convertInReportHoursFormat($value->total_hours);

            unset($value->total_hours);
            $value->setAppends([]);
        }
        return $timeRequestsList;
    }

    /**
     * Fetch goal time details.
     *
     * @param Illuminate\Http\Request $request
     * @param array $statusArray
     * @param bool $withPagination
     * @return Object
     */
    public function goalRequestList(Request $request, array $statusArray, bool $withPagination = true): Object
    {
        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        $defaultTenantLanguageId = $defaultTenantLanguage->language_id;

        $goalRequests = $this->mission->query()
        ->leftjoin('organization', 'organization.organization_id', '=', 'mission.organization_id')
        ->select('mission.mission_id', 'organization.name as organization_name');
        $goalRequests->where(['publication_status' => config("constants.publication_status")["APPROVED"],
        'mission_type'=> config('constants.mission_type.GOAL')])
        ->with(['missionLanguage' => function ($query) use ($languageId) {
            $query->select('mission_language_id', 'mission_id', 'title', 'language_id');
        }])
        ->whereHas('timesheet', function ($query) use ($request, $statusArray) {
            $query->where('user_id', $request->auth->user_id);
            $query->whereIn('status', $statusArray);
        })
        ->withCount([
        'timesheet AS action' => function ($query) use ($request, $statusArray) {
            $query->select(DB::raw("SUM(action) as action"));
            $query->where('user_id', $request->auth->user_id);
            $query->whereIn('status', $statusArray);
        }]);
        if ($withPagination) {
            $goalRequestList = $goalRequests->paginate($request->perPage);
        } else {
            $goalRequestList = $goalRequests->get();
        }
        foreach ($goalRequestList as $value) {
            if ($value->missionLanguage) {
                $key = array_search($languageId, array_column($value->missionLanguage->toArray(), 'language_id'));
                $language = ($key === false) ? $defaultTenantLanguageId : $languageId;
                $missionLanguage = $value->missionLanguage->where('language_id', $language)->first();

                // Set title
                $missionTitle = $missionLanguage->title ?? '';
                $value->setAttribute('title', $missionTitle);
                unset($value->missionLanguage);
            }
            $value->setAppends([]);
        }
        return $goalRequestList;
    }

    /**
     * Get timesheet entries
     *
     * @param Illuminate\Http\Request $request
     * @param string $missionType
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTimesheetEntries(Request $request, string $missionType): LengthAwarePaginator
    {
        $userId = $request->auth->user_id;

        // Fetch tenant options value
        $tenantOptionData = $this->tenantOptionRepository->getOptionValue('ALLOW_TIMESHEET_ENTRY');
        $extraWeeks = isset($tenantOptionData[0]['option_value'])
        ? intval($tenantOptionData[0]['option_value']) : config('constants.ALLOW_TIMESHEET_ENTRY');

        $timesheet = $this->mission->select('mission.mission_id', 'mission.start_date', 'mission.end_date')
        ->where([
            'publication_status' => config("constants.publication_status")["APPROVED"],
            'mission_type'=> $missionType])
        ->whereHas('missionApplication', function ($query) use ($userId) {
            $query->where('user_id', $userId)
            ->whereIn('approval_status', [config("constants.application_status")["AUTOMATICALLY_APPROVED"]]);
        })
        ->with(['missionLanguage' => function ($query) {
            $query->select(
                'mission_language_id',
                'mission_id',
                'objective',
                'title',
                'language_id',
                'label_goal_objective'
            );
        }]);

        if ($missionType === config('constants.mission_type.TIME')) {
            $timesheet->with('timeMission');
        }
        $timesheet->with(['timesheet' => function ($query) use ($missionType, $userId) {
            $type = ($missionType === config('constants.mission_type.TIME')) ? 'time' : 'action';
            $query->select(
                'timesheet_id',
                'mission_id',
                'date_volunteered',
                'day_volunteered',
                'notes',
                'status',
                $type
            )
            ->where('user_id', $userId);
        }]);

        return $timesheet->paginate($request->perPage);
    }

    /**
     * Fetch timesheet details
     *
     * @param int $missionId
     * @param int $userId
     * @param string $date
     * @param array $statusArray
     *
     * @return null|Illuminate\Support\Collection
     */
    public function getTimesheetDetails(int $missionId, int $userId, string $date, array $statusArray): ?Collection
    {
        return $this->timesheet->with('timesheetDocument')
        ->whereIn('status', $statusArray)
        ->where(['mission_id' => $missionId,
            'user_id' => $userId, 'date_volunteered' => $date])
            ->get();
    }

    /**
     * get submitted action count
     *
     * @param int $missionId
     * @return int
     */
    public function getSubmittedActions(int $missionId): int
    {
        return ($this->timesheet->where('mission_id', $missionId)
        ->whereIn('status', array(config('constants.timesheet_status.APPROVED'),
        config('constants.timesheet_status.AUTOMATICALLY_APPROVED')))
        ->sum('action')) ?? 0;
    }

    /**
     * Get user timesheet total hours data
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @return null|array
     */
    public function getTotalHours(int $userId, $year, $month): ?array
    {
        $statusArray = [config('constants.timesheet_status.APPROVED'),
        config('constants.timesheet_status.AUTOMATICALLY_APPROVED')];

        $missionQuery = $this->mission->select('mission.*');
        $missionQuery->leftjoin('time_mission', 'mission.mission_id', '=', 'time_mission.mission_id');
        $missionQuery->where('publication_status', config("constants.publication_status")["APPROVED"]);
        $missionQuery->withCount([
            'timesheet AS total_minutes' => function ($query) use ($userId, $statusArray, $year, $month) {
                $query->select(DB::raw("sum(((hour(time) * 60) + minute(time))) as 'total_minutes'"));
                $query->where('user_id', $userId);
                if (isset($year) && $year != '') {
                    $query->whereYear('date_volunteered', $year);
                    if (isset($month) && $month != '') {
                        $query->whereMonth('date_volunteered', $month);
                    }
                }
                $query->whereIn('status', $statusArray);
            }
        ]);
        return $missionQuery->get()->toArray();
    }

    /**
     * Get user timesheet total hours data
     *
     * @param int $userId
     * @param $year
     * @return null|array
     */
    public function getTotalHoursForYear(int $userId, $year): ?array
    {
        $statusArray = [config('constants.timesheet_status.APPROVED'),
        config('constants.timesheet_status.AUTOMATICALLY_APPROVED')];

        $missionQuery = $this->mission->select('mission.*');
        $missionQuery->leftjoin('time_mission', 'mission.mission_id', '=', 'time_mission.mission_id');
        $missionQuery->where('publication_status', config("constants.publication_status")["APPROVED"]);
        $missionQuery->withCount([
            'timesheet AS total_minutes' => function ($query) use ($userId, $statusArray, $year) {
                $query->select(DB::raw("sum(((hour(time) * 60) + minute(time))) as 'total_minutes'"));
                $query->where('user_id', $userId);
                if (isset($year) && $year != '') {
                    $query->whereYear('date_volunteered', $year);
                }
                $query->whereIn('status', $statusArray);
            }
        ]);
        return $missionQuery->get()->toArray();
    }

    /**
     * Get user timesheet total hours data
     *
     * @param int $userId
     * @param $year
     * @return null|array
     */
    public function getTotalHoursbyMonth(int $userId, $year, $missionId): ?array
    {
        $statusArray = [config('constants.timesheet_status.APPROVED'),
        config('constants.timesheet_status.AUTOMATICALLY_APPROVED')];

        $missionQuery = $this->timesheet
        ->select(DB::raw("MONTH(date_volunteered) as month,
        sum(((hour(time) * 60) + minute(time))) as 'total_minutes'"));
        $missionQuery->whereHas('mission');
        $missionQuery->leftjoin('mission', 'timesheet.mission_id', '=', 'mission.mission_id')
        ->where('mission.publication_status', config("constants.publication_status")["APPROVED"]);
        $missionQuery->where('user_id', $userId);
        $missionQuery->whereYear('date_volunteered', $year);
        $missionQuery->whereIn('status', $statusArray);
        if (!is_null($missionId) && ($missionId != "")) {
            $missionQuery->where('timesheet.mission_id', $missionId);
        }
        $missionQuery->groupBy(DB::raw("MONTH(date_volunteered)"));
        $chartData = $missionQuery->get();

        $months = array();
        foreach ($chartData as $chart) {
            array_push($months, $chart['month']);
            $chart['total_hours'] = (int)($chart['total_minutes'] / 60);
        }

        $chart = $chartData->toArray();
        $lastMonth = ($year == (int) date('Y')) ? date('m') : 12;
        for ($i = 1; $i <= $lastMonth; $i++) {
            if (!in_array($i, $months)) {
                $chartArray['month'] = $i;
                $chartArray['total_hours'] = '';
                $chart[] = $chartArray;
            }
        }
        return $chart;
    }

    /**
     * Get all user's timesheet total hours data
     *
     * @param $year
     * @param $month
     * @return null|array
     */
    public function getUsersTotalHours($year, $month): ?array
    {
        $timesheetQuery = $this->getTimesheetQuery($year, $month);
        $timesheetQuery->orderBy(DB::raw("total_minutes"), "DESC");
        $timesheetQuery->groupBy(DB::raw("user_id"));
        return $timesheetQuery->get()->toArray();
    }

    /**
    * Get sum of total hours of all users timesheet
    *
    * @return int
    */
    public function getSumOfUsersTotalMinutes(): int
    {
        $timesheetQuery = $this->getTimesheetQuery();
        $result = $timesheetQuery->get()->first()->toArray();
        return $result['total_minutes'] ?: 0;
    }

    /**
     * Get details of timesheet from timesheetId
     *
     * @param int $timesheetId
     * @return App\Models\Timesheet
     */
    public function getDetailsOfTimesheetEntry(int $timesheetId): Timesheet
    {
        return $this->timesheet->with(['mission','user'])->where('timesheet_id', $timesheetId)->first();
    }

    /**
     * Get Time sheet Documents
     * @param int $timesheetId
     * @return Illuminate\Support\Collection;
     */
    public function getUploadedTimesheetDocuments(int $timesheetId, $documentCount): Collection
    {
        return $this->timesheetDocument->where('timesheet_id', $timesheetId)
                ->orderBy('timesheet_document_id', 'DESC')->take($documentCount)->get();
    }

    /**
     * Get details of timesheet from timesheetId
     *
     * @param int $timesheetId
     * @return App\Models\Timesheet
     */
    public function getDetailOfTimesheetEntry(int $timesheetId): Timesheet
    {
        return $this->timesheet->withTrashed()->where('timesheet_id', $timesheetId)->first();
    }

    /**
     * Get missionId from timesheetId
     * @param int $timesheetId
     * @return int
     */
    public function getMissionIdFromTimesheetId(int $timesheetId) : int
    {
        return $this->timesheet->where('timesheet_id', $timesheetId)->value('mission_id');
    }

    /**
     * Prepare a timesheet query
     * @param  int|null $year
     * @param  int|null $month
     * @return Object
     */
    private function getTimesheetQuery($year = null, $month = null)
    {
        $statusArray = [config('constants.timesheet_status.APPROVED'),
        config('constants.timesheet_status.AUTOMATICALLY_APPROVED')];

        $timesheetQuery = $this->timesheet
        ->select(DB::raw("user_id, MONTH(date_volunteered) as month,
        sum(((hour(time) * 60) + minute(time))) as 'total_minutes'"));
        $timesheetQuery->whereHas('mission');
        $timesheetQuery->leftjoin('mission', 'timesheet.mission_id', '=', 'mission.mission_id')
        ->where('mission.publication_status', config("constants.publication_status")["APPROVED"]);

        if (isset($year) && $year != '') {
            $timesheetQuery->whereYear('date_volunteered', $year);
            if (isset($month) && $month != '') {
                $timesheetQuery->whereMonth('date_volunteered', $month);
            }
        }
        $timesheetQuery->whereIn('status', $statusArray);

        return $timesheetQuery;
    }

    /**
     * Get specific user timesheets stats
     *
     * @param App\User $user
     * @param Array|null $params
     *
     * @return Illuminate\Support\Collection
     */
    public function summary($user, $params = null): Collection
    {
        return $user->timesheets()
            ->selectRaw('
                COUNT(*) as total_timesheet,
                MIN(date_volunteered) as first_volunteered_date,
                SUM(timesheet.action) as total_timesheet_action,
                SEC_TO_TIME(SUM(
                    TIME_TO_SEC(timesheet.time)
                )) as total_timesheet_time,
                SUM(
                    TIME_TO_SEC(timesheet.time)
                ) as total_time_seconds
            ')
            ->isApproved()
            ->isYear($params['year'] ?? null)
            ->isMonth($params['month'] ?? null)
            ->get();
    }

    /**
     * Get specific user timesheets stats
     *
     * @param App\User $user
     *
     * @return Illuminate\Support\Collection
     */
    public function findByUser($user): Collection
    {
        return $user->timesheets()
            ->isApproved()
            ->orderBy('date_volunteered', 'ASC')
            ->get();
    }

}
