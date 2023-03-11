<?php

namespace App\Services;

use App\Repositories\Timesheet\TimesheetRepository;
use App\Repositories\TenantOption\TenantOptionRepository;
use Carbon\Carbon;
use App\User;

class TimesheetService
{
    /**
     * @var App\Repositories\Timesheet\TimesheetRepository
     */
    private $timesheetRepository;

    /**
     * @var App\Repositories\TenantOption\TenantOptionRepository;
     */
    private $tenantOptionRepository;
    
    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\User\UserRepository $timesheetRepository
     * @return void
     */
    public function __construct(
        TimesheetRepository $timesheetRepository,
        TenantOptionRepository $tenantOptionRepository
    ) {
        $this->timesheetRepository = $timesheetRepository;
        $this->tenantOptionRepository = $tenantOptionRepository;
    }

    /**
     * Get specific user timesheet summary
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function summary($user, $params = null): Array
    {
        // get all the timesheet stasts
        $timesheets = $this->timesheetRepository->summary($user, $params);

        $data = $timesheets
            ->first()
            ->toArray();

        // add the user goal hours to the data
        $data['total_hours_goal'] = $this->goalHours($user->hours_goal);
        $data['total_remaining_hours'] = $data['total_hours_goal'] - number_format($data['total_time_seconds'] / (60 * 60), 2, '.', '');
        $data['total_completed_hours'] = number_format($data['total_hours_goal'] - $data['total_remaining_hours'], 2, '.', '');
        $data['volunteering_rank'] = $this->volunteeringRank($user->user_id, $params);
        $data['average_volunteering_days'] = $this->daysVolunteerAverage($user);

        return $data;
    }

    /**
     * Get volunteering rank
     *
     * @param int $userId
     * @param Array $params all get parameteres
     *
     * @return Integer
     */
    public function volunteeringRank($userId, $params = null)
    {
        $ranks = [];
        $minutes = 0;
        $year = $params['year'] ?? null;
        $month = $params['month'] ?? null;

        $timesheets = $this->timesheetRepository->getUsersTotalHours($year, $month);

        foreach ($timesheets as $timesheet) {
            array_push($ranks, $timesheet['total_minutes']);
            if ($userId == $timesheet['user_id']) {
                $minutes = $timesheet['total_minutes'];
            }
        }

        $userRank = array_values(array_unique($ranks));
        $userRankIndex = array_search($minutes, $userRank);
        $volunteeringRank = (count($timesheets) !== 0) ? (100/count($timesheets)) * ($userRankIndex+1) : 0;

        return $volunteeringRank;
    }

    /**
     * Get user total goal hours
     *
     * @param int|null $goal current user hours goal
     *
     * @return Int
     */
    public function goalHours($goal = null)
    {
        $defaultHourGoal = config('constants.DEFAULT_USER_HOURS_GOAL');
        // Get the option / default hour goals
        $hourGoal = $this->tenantOptionRepository->getOptionValueFromOptionName('default_user_hours_goal');
        $hourGoal = $hourGoal->option_value ?? $defaultHourGoal;

        return $goal ?? $hourGoal;
    }

    /**
     * Get user timesheet average days between volunteering
     *
     * @param App\User $user
     *
     * @return Int
     */
    public function daysVolunteerAverage($user)
    {
        $timesheets = $this->timesheetRepository->findByUser($user);
        $timesheets = $timesheets->map(function($item) {
            return $item->date_volunteered;
        })->toArray();

        $difference = [];

        foreach ($timesheets as $key => $timesheet) {
            $date = Carbon::createFromFormat('m-d-Y', $timesheet);
            $nextDate = next($timesheets);
            if ($nextDate) {
                $nextDate = Carbon::createFromFormat('m-d-Y', $nextDate);
                $difference[] = $date->diffInDays($nextDate);
            }
        }

        if (count($difference) === 0) {
            return 0;
        }

        // Get the average date difference
        return round(array_sum($difference) / count($difference));
    }

}
