<?php
namespace App\Repositories\TenantSetting;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TenantSetting;

interface TenantSettingInterface
{
    /**
     * Update setting value
     *
     * @param array $data
     * @param int $settingId
     * @return App\Models\TenantSetting
     */
    public function updateSetting(array $data, int $settingId): TenantSetting;

    /**
     * Get all tenant's settings data
     *
     * @param array $ids
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllTenantSettings(array $ids = []): Collection;
}
