<?php
namespace App\Repositories\TenantHasSetting;

use App\Repositories\TenantHasSetting\TenantHasSettingInterface;
use Illuminate\Http\Request;
use App\Models\TenantHasSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TenantSetting;
use DB;

class TenantHasSettingRepository implements TenantHasSettingInterface
{
    /**
     * @var App\Models\TenantHasSetting
     */
    private $tenantHasSetting;

    /**
     * Create a new Tenant has setting repository instance.
     *
     * @param  App\Models\TenantHasSetting $tenantHasSetting
     * @param  App\Models\TenantSetting $tenantSetting
     * @return void
     */
    public function __construct(TenantHasSetting $tenantHasSetting, TenantSetting $tenantSetting)
    {
        $this->tenantHasSetting = $tenantHasSetting;
        $this->tenantSetting = $tenantSetting;
    }

    /**
     * Get Settings lists
     *
     * @param int $tenantId
     * @param array $filters
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getSettingsList(int $tenantId, $filters = []): Collection
    {
        return $this->tenantSetting
            ->selectRaw('
                tenant_setting.title,
                tenant_setting.tenant_setting_id,
                tenant_setting.description,
                tenant_setting.key,
                CASE
                    WHEN tenant_has_setting.tenant_setting_id IS NULL THEN "0"
                    ELSE "1"
                END AS is_active
            ')
            ->leftJoin('tenant_has_setting', function ($join) use ($tenantId) {
                $join->on('tenant_setting.tenant_setting_id', '=', 'tenant_has_setting.tenant_setting_id')
                    ->whereNull('tenant_has_setting.deleted_at')
                    ->where('tenant_has_setting.tenant_id', $tenantId);
            })
            ->when(!empty($filters['keys'] ?? null), function ($query) use ($filters) {
                $keys = $filters['keys'];
                $query->whereIn('tenant_setting.key', $keys);
            })
            ->get();
    }

    /**
     * Create new setting
     *
     * @param array $data
     * @param int $tenantId
     * @return bool
     */
    public function store(array $data, int $tenantId): bool
    {
        foreach ($data['settings'] as $value) {
            if ($value['value'] == 1) {
                $this->tenantHasSetting->enableSetting($tenantId, $value['tenant_setting_id']);
            } else {
                $this->tenantHasSetting->disableSetting($tenantId, $value['tenant_setting_id']);
            }
        }
        return true;
    }

    /**
     * Check for Volunterring time or goal mission shoudld be enabled
     *
     * @param array $data
     * @param int $tenantId
     * @return bool
     */
    public function checkVolunteeringTimeAndGoalSetting(array $data, int $tenantId)
    {
        $volunteering = $this->tenantSetting->where('key', config('constants.tenant_settings.VOLUNTEERING'))->first();
        $volunteeringSetting = $this->tenantHasSetting->where(['tenant_setting_id' => $volunteering->tenant_setting_id, 'tenant_id' => $tenantId])->first();
        if ($volunteeringSetting) {
            foreach ($data['settings'] as $value) {
                $tenantSetting = $this->tenantSetting->where('tenant_setting_id', $value['tenant_setting_id'])->first();

                // Check volunteering goal should be active if we disable volunteering time mission
                if ($tenantSetting->key == config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION') && !$value['value']) {
                    $volunteeringGoal = $this->tenantSetting->where('key', config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION'))->first();
                    $volunteeringGoalSetting = $this->tenantHasSetting->where(['tenant_setting_id' => $volunteeringGoal->tenant_setting_id, 'tenant_id' => $tenantId])->first();
                    if (!$volunteeringGoalSetting) {
                        return false;
                    }
                }

                // Check volunteering time should be active if we disable volunteering goal mission
                if ($tenantSetting->key == config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION') && !$value['value']) {
                    $volunteeringTime = $this->tenantSetting->where('key', config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION'))->first();
                    $volunteeringTimeSetting = $this->tenantHasSetting->where(['tenant_setting_id' => $volunteeringTime->tenant_setting_id, 'tenant_id' => $tenantId])->first();

                    if (!$volunteeringTimeSetting) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check volunteering setting is disabled or not
     *
     * @param array $data
     * @param int $tenantId
     * @return bool
     */
    public function checkVolunteeringSettingDisabled(array $data, int $tenantId)
    {
        $volunteering = $this->tenantSetting->where('key', config('constants.tenant_settings.VOLUNTEERING'))->first();
        $volunteeringSetting = $this->tenantHasSetting->where(['tenant_setting_id' => $volunteering->tenant_setting_id, 'tenant_id' => $tenantId])->first();
        if (!$volunteeringSetting) {
            foreach ($data['settings'] as $value) {
                $tenantSetting = $this->tenantSetting->where('tenant_setting_id', $value['tenant_setting_id'])->first();
                if ($tenantSetting->key == config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION')
                    || $tenantSetting->key == config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')) {
                    return false;
                }
            }
        }
        return true;
    }
}
