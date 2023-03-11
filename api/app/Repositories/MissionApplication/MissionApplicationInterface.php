<?php
namespace App\Repositories\MissionApplication;

use App\Models\MissionApplication;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface MissionApplicationInterface
{
    /**
     * Check already applied for a mission or not.
     *
     * @param int $missionId
     * @param int $userId
     * @return int
     */
    public function checkApplyMission(int $missionId, int $userId): int;

    /**
     * Add mission application.
     *
     * @param array $request
     * @return App\Models\MissionApplication
     */
    public function storeApplication(array $request, int $userId): MissionApplication;

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $missionId
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function missionApplications(Request $request, int $missionId): LengthAwarePaginator;

    /**
     * Display specified resource.
     *
     * @param int $missionId
     * @param int $applicationId
     * @return array
     */
    public function missionApplication(int $missionId, int $applicationId): array;

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $missionId
     * @param int $applicationId
     * @return App\Models\MissionApplication
     */
    public function updateApplication(Request $request, int $missionId, int $applicationId): MissionApplication;

    /**
     * Get recent volunteers
     *
     * @param Illuminate\Http\Request $request
     * @param int $missionId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function missionVolunteerDetail(Request $request, int $missionId): LengthAwarePaginator;

    /**
     * Get mission application count.
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @param null|array $missionTypes
     * @return null|int
     */
    public function missionApplicationCount(int $userId, $year, $month, array $missionTypes = null): ?int;

    /**
     * Get organization count.
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @param null|array $missionTypes
     * @return null|array
     */
    public function organizationCount(int $userId, $year, $month, array $missionTypes = null): ?array;

    /**
     * Get pending application count.
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @param null|array $missionTypes
     * @return null|int
     */
    public function pendingApplicationCount(int $userId, $year, $month, array $missionTypes = null): ?int;
}
