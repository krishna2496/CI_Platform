<?php
namespace App\Repositories\MissionApplication;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Models\MissionApplication;
use App\Models\TimeMission;
use App\Models\Mission;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MissionApplicationRepository implements MissionApplicationInterface
{
    /**
     * @var ResponseHelper
     */
    private $responseHelper;

    /**
     * @var MissionApplication
     */
    public $missionApplication;

    /**
     * @var TimeMission
     */
    public $timeMission;

    /**
     * @var Mission
     */
    public $mission;

    /**
     * Create a new MissionApplication repository instance.
     *
     * @param  Mission $mission
     * @param  TimeMission $timeMission
     * @param  ResponseHelper $responseHelper
     * @param  MissionApplication $missionApplication
     * @return void
     */
    public function __construct(
        Mission $mission,
        TimeMission $timeMission,
        ResponseHelper $responseHelper,
        MissionApplication $missionApplication
    ) {
        $this->mission = $mission;
        $this->timeMission = $timeMission;
        $this->responseHelper = $responseHelper;
        $this->missionApplication = $missionApplication;
    }

    /*
     * Check already applied for a mission or not.
     *
     * @param int $missionId
     * @param int $userId
     * @return int
     */
    public function checkApplyMission(int $missionId, int $userId): int
    {
        return $this->missionApplication->checkApplyMission($missionId, $userId);
    }

    /**
     * Add mission application.
     *
     * @param array $request
     * @param int $userId
     * @return MissionApplication
     */
    public function storeApplication(array $request, int $userId): MissionApplication
    {
        $application = array(
            'mission_id' => $request['mission_id'],
            'user_id' => $userId,
            'motivation' => $request['motivation'] ?? '',
            'availability_id' => $request['availability_id'],
            'approval_status' => config('constants.application_status.PENDING'),
            'applied_at' => Carbon::now()
        );
        return $this->missionApplication->create($application);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param int $missionId
     * @return LengthAwarePaginator
     */
    public function missionApplications(Request $request, int $missionId): LengthAwarePaginator
    {
        return $this->missionApplication->find($request, $missionId);
    }

    /**
     * Display specified resource.
     *
     * @param int $missionId
     * @param int $applicationId
     * @return array
     */
    public function missionApplication(int $missionId, int $applicationId): array
    {
        return $this->missionApplication->findDetail($missionId, $applicationId);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param int $missionId
     * @param int $applicationId
     * @return MissionApplication
     */
    public function updateApplication(Request $request, int $missionId, int $applicationId): MissionApplication
    {
        try {
            $this->mission->findOrFail($missionId);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }
        try {
            $missionApplication = $this->missionApplication->findOrFail($applicationId);
            $missionApplication->update($request->toArray());
            return $missionApplication;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(
                trans('messages.custom_error_message.ERROR_MISSION_APPLICATION_NOT_FOUND')
            );
        }
    }

    /**
     * Get recent volunteers
     *
     * @param Request $request
     * @param int $missionId
     * @return LengthAwarePaginator
     */
    public function missionVolunteerDetail(Request $request, int $missionId): LengthAwarePaginator
    {
        $this->mission->findOrFail($missionId);
        return $this->missionApplication->getVolunteers($request, $missionId);
    }

    /**
     * Get mission application count.
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @param null|array $missionTypes
     * @return null|int
     */
    public function missionApplicationCount(
        int $userId,
        $year,
        $month,
        array $missionTypes = null
    ): ?int {
        return $this->missionApplication->missionApplicationCount(
            $userId,
            $year,
            $month,
            $missionTypes
        );
    }

    /**
     * Get organization count.
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @param null|array $missionTypes
     * @return null|array
     */
    public function organizationCount(
        int $userId,
        $year,
        $month,
        array $missionTypes = null
    ): ?array {
        $countQuery = $this->mission
            ->leftJoin('mission_application', 'mission_application.mission_id', '=', 'mission.mission_id')
            ->where(['mission_application.user_id' => $userId])
            ->where('mission_application.approval_status', '<>', config('constants.application_status.REFUSED'))
            ->groupBy('mission.organization_id');

        if ($missionTypes !== null) {
            $countQuery->whereIn('mission_type', $missionTypes);
        }

        if (isset($year) && $year != '') {
            $countQuery->whereYear('applied_at', $year);
            if (isset($month) && $month != '') {
                $countQuery->whereMonth('applied_at', $month);
            }
        }
        return $countQuery->get()->toArray();
    }

    /**
     * Get pending application count.
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @param null|array $missionTypes
     * @return null|int
     */
    public function pendingApplicationCount(
        int $userId,
        $year,
        $month,
        array $missionTypes = null
    ): ?int {
        return $this->missionApplication->pendingApplicationCount(
            $userId,
            $year,
            $month,
            $missionTypes
        );
    }

    /**
     * Get mission id from application id
     *
     * @param int $applicationId
     * @return int
     */
    public function getMissionId(int $applicationId): int
    {
        return $this->missionApplication
            ->where('mission_application_id', $applicationId)
            ->first()
            ->mission_id;
    }
}
