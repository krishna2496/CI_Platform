<?php
namespace App\Services\Dashboard;

class DashboardService
{
    /**
     * Get volunteering rank
     *
     * @param array $allUsersTimesheetData
     * @return int
     */
    public function getvolunteeringRank(array $allUsersTimesheetData, int $userId)
    {
        $userRankArray = array();
        $userTimesheetMinutes = 0;
        
        foreach ($allUsersTimesheetData as $allUsersTimesheet) {
            array_push($userRankArray, $allUsersTimesheet['total_minutes']);
            if ($userId == $allUsersTimesheet['user_id']) {
                $userTimesheetMinutes = $allUsersTimesheet['total_minutes'];
            }
        }
        $userRank = array_values(array_unique($userRankArray));
        $userRankIndex = array_search($userTimesheetMinutes, $userRank);
        $volunteeringRank = (count($allUsersTimesheetData) !== 0) ?
        (100/count($allUsersTimesheetData)) * ($userRankIndex+1) : 0;
        $key = array_search($userId, array_column($allUsersTimesheetData, 'user_id'));
        if ($key === false) {
            $volunteeringRank = 0;
        }
        
        return $volunteeringRank;

    }
}
