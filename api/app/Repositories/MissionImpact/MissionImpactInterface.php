<?php
namespace App\Repositories\MissionImpact;

interface MissionImpactInterface
{
    /**
    * Store a newly created resource into database
    *
    * @param array $missionImpact
    * @param int $missionId
    * @param int $defaultTenantLanguageId
    * @param string $tenantName
    * @return void
    */
    public function store(array $missionImpact, int $missionId, int $defaultTenantLanguageId, string $tenantName);

    /**
     * Update a resource into database
     *
     * @param array $missionImpact
     * @param int $missionId
     * @param int $defaultTenantLanguageId
     * @param string $tenantName
     * @return void
     */
    public function update(array $missionImpact, int $missionId, int $defaultTenantLanguageId, string $tenantName);

    /**
     * Delete mission impact and s3bucket data
     *
     * @param string $missionImpactId
     * @return bool
     */
    public function deleteMissionImpactAndS3bucketData(string $missionImpactId): bool;

    /**
     * Check sort key is already exist or not
     *
     * @param int $missionId
     * @param array $missionImpact
     * @return bool
     */
    public function checkImpactSortKeyExist(int $missionId, array $missionImpact): bool;
}
