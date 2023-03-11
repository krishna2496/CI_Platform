<?php
namespace App\Repositories\MissionSkill;

use Illuminate\Http\Request;
use App\Models\MissionSkill;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MissionSkillInterface
{
    /**
     * Get all skill history with total minutes logged, based on year and all years.
     *
     * @param int $year
     * @param int $userId
     * @return Illuminate\Support\Collection
     */
    public function getHoursPerSkill(int $year = null, int $userId): Collection;
}
