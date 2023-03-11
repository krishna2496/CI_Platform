<?php
namespace App\Repositories\Timesheet;

use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TimesheetInterface
{
    /**
     * Store/Update timesheet
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\Timesheet
     */
    public function storeOrUpdateTimesheet(Request $request): Timesheet;

    /**
     * get submitted action count
     *
     * @param int $missionId
     * @return int
     */
    public function getSubmittedActions(int $missionId): int;

    /**
     * Fetch timesheet details
     *
     * @param int $timesheetId
     * @return null|Timesheet
     */
    public function find(int $timesheetId): ?Timesheet;

    /**
     * Fetch timesheet details
     *
     * @param int $timesheetId
     * @param int $userId
     * @return Timesheet
     */
    public function getTimesheetData(int $timesheetId, int $userId): Timesheet;

    /**
    * Remove the timesheet document.
    *
    * @param  int  $id
    * @param  int  $timesheetId
    * @return bool
    */
    public function delete(int $id, int $timesheetId): bool;

    /**
     * Update timesheet status
     *
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     * @return bool
     */
    public function submitTimesheet(Request $request, int $userId): bool;

    /**
     * Get time request details.
     *
     *
     * @param \Illuminate\Http\Request $request
     * @param array $statusArray
     * @return Object
     */
    public function timeRequestList(Request $request, array $statusArray) : Object;

    /**
     * Fetch goal requests list
     *
     * @param Illuminate\Http\Request $request
     * @param array $statusArray
     * @return Object
     */
    public function goalRequestList(Request $request, array $statusArray): Object;

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
    public function getTimesheetDetails(int $missionId, int $userId, string $date, array $statusArray): ?Collection;

    /**
     * Update timesheet field value, based on timesheet_id condition
     *
     * @param Request $request
     * @param int $timesheetId
     * @return bool
     */
    public function updateTimesheet(Request $request, int $timesheet): bool;

    /**
     * Get timesheet entries
     *
     * @param Illuminate\Http\Request $request
     * @param string $type
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllTimesheetEntries(Request $request, string $type): LengthAwarePaginator;

    /**
     * Get user timesheet total hours data
     *
     * @param int $userId
     * @param $year
     * @param $month
     * @return null|array
     */
    public function getTotalHours(int $userId, $year, $month): ?array;

    /**
     * Get user timesheet total hours data
     *
     * @param int $userId
     * @param int $year
     * @return null|array
     */
    public function getTotalHoursForYear(int $userId, int $year): ?array;

    /**
     * Get user timesheet total hours data
     *
     * @param int $userId
     * @param $year
     * @return null|array
     */
    public function getTotalHoursbyMonth(int $userId, $year, $missionId): ?array;

    /**
     * Get all user's timesheet total hours data
     *
     * @param $year
     * @param $month
     * @return null|array
     */
    public function getUsersTotalHours($year, $month): ?array;

    /**
     * Get details of timesheet from timesheetId
     *
     * @param int $timesheetId
     * @return App\Models\Timesheet
     */
    public function getDetailOfTimesheetEntry(int $timesheetId): Timesheet;
}
