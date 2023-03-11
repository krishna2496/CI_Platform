<?php
namespace App\Repositories\MissionUnitedNationSDG;

use Illuminate\Http\Request;

interface MissionUnitedNationSDGInterface
{
    /**
     * Add UN SDG to mission.
     *
     * @param int $missionId
     * @param array $request
     * @return Illuminate\Support\Collection
     */
    public function addUnSdg(int $missionId, array $request);

    /**
     * Update UN SDG to mission.
     *
     * @param int $missionId
     * @param array $request
     * @return Illuminate\Support\Collection
     */
    public function updateUnSdg(int $missionId, array $request);
}
