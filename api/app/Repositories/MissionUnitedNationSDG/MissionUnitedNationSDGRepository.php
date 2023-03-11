<?php
namespace App\Repositories\MissionUnitedNationSDG;

use App\Models\UnitedNationSDG;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\MissionUnSdg;

class MissionUnitedNationSDGRepository implements MissionUnitedNationSDGInterface
{
    /**
     * @var App\Models\MissionUnSdg;
     */
    private $missionUnSdg;

    /**
     * Create a new Mission United Nation SDG repository instance.
     *
     * @param  App\Models\MissionUnSdg $missionUnSdg
     * @return void
     */
    public function __construct(
        MissionUnSdg $missionUnSdg
    ) {
        $this->missionUnSdg = $missionUnSdg;
    }

    /**
     * Add UN SDG to mission.
     *
     * @param int $missionId
     * @param array $request
     */
    public function addUnSdg(int $missionId, array $request)
    {
        foreach ($request['un_sdg'] as $key => $value) {
            $this->missionUnSdg->create([
                "mission_id" => $missionId,
                "un_sdg_number" => $value
            ]);
        }
    }

    /**
     * Update UN SDG to mission.
     *
     * @param int $missionId
     * @param array $request
     */
    public function updateUnSdg(int $missionId, array $request)
    {
        // Delete UN SDG which is not associated with mission.
        $this->missionUnSdg->where('mission_id', $missionId)
        ->whereNotIn('un_sdg_number', $request['un_sdg'])->delete();
        // Update new UN SDG for mission
        foreach ($request['un_sdg'] as $key => $value) {
            $this->missionUnSdg->updateOrCreate(
            [
                "mission_id" => $missionId,
                "un_sdg_number" => $value
            ],
            [
                "mission_id" => $missionId,
                "un_sdg_number" => $value
            ]);
        }
    }
}
