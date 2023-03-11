<?php
namespace App\Repositories\TenantSetting;

use App\Repositories\TenantSetting\TenantSettingInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TenantSetting;

class TenantSettingRepository implements TenantSettingInterface
{

    /**
     * The tenantSetting for the model.
     *
     * @var App\Models\TenantSetting
     */
    public $tenantSetting;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\TenantSetting $tenantSetting
     * @return void
     */
    public function __construct(TenantSetting $tenantSetting)
    {
        $this->tenantSetting = $tenantSetting;
    }

    /**
     * Update setting value
     *
     * @param array $data
     * @param int $settingId
     * @return App\Models\TenantSetting
     */
    public function updateSetting(array $data, int $settingId): TenantSetting
    {
        $setting = $this->tenantSetting->findOrFail($settingId);
        $setting->update($data);
        return $setting;
    }

    /**
     * Get all tenant's settings data.
     *
     * @param array $ids
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllTenantSettings(array $ids = []): Collection
    {
        return $this->tenantSetting->getAllTenantSettings($ids);
    }
}
