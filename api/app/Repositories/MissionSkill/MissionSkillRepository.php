<?php
namespace App\Repositories\MissionSkill;

use App\Repositories\MissionSkill\MissionSkillInterface;
use App\Models\MissionSkill;
use Illuminate\Support\Collection;
use \Illuminate\Pagination\LengthAwarePaginator;

class MissionSkillRepository implements MissionSkillInterface
{
    /**
     * @var App\Models\MissionSkill
     */
    public $missionSkill;

    /**
     * Create a new MissionSkill repository instance.
     *
     * @param  App\Models\MissionSkill $missionSkill
     * @return void
     */
    public function __construct(MissionSkill $missionSkill)
    {
        $this->missionSkill = $missionSkill;
    }

    /**
     * Get all skill history with total minutes logged, based on year and all years.
     *
     * @param int $year
     * @param int $userId
     * @return Illuminate\Support\Collection
     */
    public function getHoursPerSkill(int $year = null, int $userId): Collection
    {
        $queryBuilder = $this->missionSkill->select([
            'mission_skill.skill_id',
            'skill.skill_name',
            'skill.translations',
            \DB::raw('sum(minute(timesheet.time) + (hour(timesheet.time)*60)) as total_minutes')
        ])
        ->leftjoin('mission', 'mission.mission_id', 'mission_skill.mission_id')
        ->leftjoin('skill', 'skill.skill_id', 'mission_skill.skill_id')
        ->leftjoin('timesheet', 'mission.mission_id', 'timesheet.mission_id')
        ->where('mission.mission_type', 'TIME');
        if (!empty($year)) {
            $queryBuilder = $queryBuilder->whereRaw(\DB::raw('year(timesheet.created_at) = "'.$year.'"'));
        }

        $statusArray = [
            config('constants.timesheet_status.AUTOMATICALLY_APPROVED'),
            config('constants.timesheet_status.APPROVED')
        ];
        $queryBuilder = $queryBuilder->where('mission.publication_status', 'APPROVED')
        ->where('timesheet.user_id', $userId)
        ->whereNotNull('mission.mission_id')
        ->whereIn('timesheet.status', $statusArray)
        ->whereNotNull('timesheet.timesheet_id')
        ->whereNull('timesheet.deleted_at')
        ->groupBy('mission_skill.skill_id');

        $hoursPerSkill = $queryBuilder->get();

        $languageCode = config('app.locale');
        foreach ($hoursPerSkill as $skill) {
            $translations = json_decode($skill->translations, true);
            if (array_key_exists($languageCode, $translations)) {
                $skill->skill_name = $translations[$languageCode];
            }
            unset($skill->translations);
        }
        return $hoursPerSkill;
    }
}
