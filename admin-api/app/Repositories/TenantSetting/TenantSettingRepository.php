<?php
namespace App\Repositories\TenantSetting;

use App\Repositories\TenantSetting\TenantSettingInterface;
use Illuminate\Http\Request;
use App\Models\TenantSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use DB;

class TenantSettingRepository implements TenantSettingInterface
{
    /**
     * @var App\Models\TenantSetting
     */
    public $tenantSetting;

    /**
     * Create a new Tenant has setting repository instance.
     *
     * @param  App\Models\TenantSetting $TenantSetting
     * @param  App\Models\TenantSetting $tenantSetting
     * @return void
     */
    public function __construct(TenantSetting $tenantSetting)
    {
        $this->tenantSetting = $tenantSetting;
    }

    /**
     * Get Settings lists
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAllSettings(): Collection
    {
        $allSettings = $this->tenantSetting
        ->select(
            'tenant_setting.title',
            'tenant_setting.tenant_setting_id',
            'tenant_setting.description',
            'tenant_setting.key'
        )
        ->get();
        return $allSettings;
    }
}
