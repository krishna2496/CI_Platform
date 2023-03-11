<?php
namespace App\Repositories\MissionTab;

use Illuminate\Http\Request;

interface MissionTabInterface
{
    /**
     * Store a newly created resource into database
     *
     * @param array $missionTabValue
     * @param int $missionId
     * @return array
     */
    public function store(array $missionTabValue, int $missionId);

    /**
     * Store a newly created resource into database
     *
     * @param array $missionTabValue
     * @param int $missionId
     * @return array
     */
    public function update(array $missionTabValue, int $missionId);

    /**
     * Check sort key is already exist or not
     *
     * @param int $missionId
     * @param array $missionTabs
     *
     * @return bool
     */
    public function checkSortKeyExist(int $missionId, array $missionTabs): bool;
}
