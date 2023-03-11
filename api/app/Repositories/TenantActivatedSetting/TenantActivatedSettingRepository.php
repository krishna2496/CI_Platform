<?php
namespace App\Repositories\TenantActivatedSetting;

use App\Helpers\Helpers;
use App\Models\TenantActivatedSetting;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Exceptions\VolunteeringTimeOrGoalSettingShouldBeActiveException;

class TenantActivatedSettingRepository implements TenantActivatedSettingInterface
{
    /**
     * The tenantActivatedSetting for the model.
     *
     * @var App\Models\TenantActivatedSetting
     */
    public $tenantActivatedSetting;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\TenantActivatedSetting $tenantActivatedSetting
     * @param  App\Helpers\Helpers $helpers
     * @return void
     */
    public function __construct(TenantActivatedSetting $tenantActivatedSetting, Helpers $helpers)
    {
        $this->tenantActivatedSetting = $tenantActivatedSetting;
        $this->helpers = $helpers;
    }

    /**
     * Fetch tenant settings with specified keys
     *
     * @param $ids
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getList(array $ids = []): Collection
    {
        return $this->tenantActivatedSetting
            ->select(
                'ts.setting_id',
                'ts.tenant_setting_id'
            )
            ->join('tenant_setting AS ts', 'ts.tenant_setting_id', '=', 'tenant_activated_setting.tenant_setting_id')
            ->when(!empty($ids), function ($query) use ($ids) {
                return $query->whereIn('ts.setting_id', $ids);
            })
            ->get();
    }

    /**
     * Create new activated settings
     *
     * @param array $data
     * @return bool
     */
    public function store(array $data): bool
    {
        foreach ($data['settings'] as $value) {
            $response = $this->checkVolunteeringTimeAndGoalSetting($value);
            if (!$response) {
                throw new VolunteeringTimeOrGoalSettingShouldBeActiveException();
            }
            $this->tenantActivatedSetting->storeSettings($value['tenant_setting_id'], $value['value']);
        }
        return true;
    }

    /**
     * Fetch all tenant settings
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllTenantSettings(): Collection
    {
        return $this->tenantActivatedSetting->whereHas('settings')->get();
    }

    /**
     * Get fetch all activated tenant settings
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getAllTenantActivatedSetting(Request $request): array
    {
        // Fetch tenant all settings details - From super admin
        $getTenantSettings = $this->helpers->getAllTenantSetting($request);

        // Get data from tenant database
        $tenantActivatedSettings = $this->tenantActivatedSetting->whereHas('settings')->get();

        $tenantSettingData = array();
        if ($tenantActivatedSettings->count() &&  $getTenantSettings->count()) {
            foreach ($tenantActivatedSettings as $settingKey => $tenantSetting) {
                $index = $getTenantSettings->search(function ($value, $key) use ($tenantSetting) {
                    return $value->tenant_setting_id === $tenantSetting->settings->setting_id;
                });
                $tenantSettingData[] = $getTenantSettings[$index]->key;
            }
        }
        return $tenantSettingData;
    }

    /**
     * Get fetch all activated tenant settings
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function checkTenantSettingStatus(string $settingKeyName, Request $request): bool
    {
        // Fetch tenant all settings details - From super admin
        $getTenantSettings =  $this->helpers->getAllTenantSetting($request);

        // Get data from tenant database
        $tenantActivatedSettings = $this->tenantActivatedSetting->whereHas('settings')->get();

        $tenantSettingData = array();
        if ($tenantActivatedSettings->count() &&  $getTenantSettings->count()) {
            foreach ($tenantActivatedSettings as $settingKey => $tenantSetting) {
                $index = $getTenantSettings->search(function ($value, $key) use ($tenantSetting) {
                    return $value->tenant_setting_id === $tenantSetting->settings->setting_id;
                });
                $tenantSettingData[] = $getTenantSettings[$index]->key;
            }
        }

        return in_array($settingKeyName, $tenantSettingData);
    }

    /**
    * Check for Volunterring time or goal mission shoudld be enabled
    *
    * @param array $value
    * @return bool
    */
    public function checkVolunteeringTimeAndGoalSetting(array $value)
    {
        $this->helpers->switchDatabaseConnection('mysql');
        $volunteering = \DB::table('tenant_setting')->where('key', config('constants.tenant_settings.VOLUNTEERING'))->first();
        $this->helpers->switchDatabaseConnection('tenant');
        $tenantVolunteeringSetting = \DB::table('tenant_setting')->where('setting_id', $volunteering->tenant_setting_id)->first();
        if (!$tenantVolunteeringSetting) {
            return false;
        }
        $volunteeringSetting = $this->tenantActivatedSetting->where('tenant_setting_id', $tenantVolunteeringSetting->tenant_setting_id)->first();

        if ($volunteeringSetting) {
            $this->helpers->switchDatabaseConnection('tenant');

            $tenantSetting = \DB::table('tenant_setting')->where('tenant_setting_id', $value['tenant_setting_id'])->first();
            $this->helpers->switchDatabaseConnection('mysql');
            $masterSetting = \DB::table('tenant_setting')->where('tenant_setting_id', $tenantSetting->setting_id)->first();

            // Check volunteering goal should be active if we disable volunteering time mission
            if ($masterSetting->key == config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION') && !$value['value']) {
                $this->helpers->switchDatabaseConnection('mysql');
                $volunteeringGoal = \DB::table('tenant_setting')->where('key', config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION'))->first();
                $this->helpers->switchDatabaseConnection('tenant');
                $volunteeringGoalSetting = \DB::table('tenant_setting')->where('setting_id', $volunteeringGoal->tenant_setting_id)->first();
                $volunteeringGoalSetting = $this->tenantActivatedSetting->where(['tenant_setting_id' => $volunteeringGoalSetting->tenant_setting_id])->first();

                if (!$volunteeringGoalSetting) {
                    return false;
                }
            }
            // Check volunteering time should be active if we disable volunteering goal mission
            if ($masterSetting->key == config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION') && !$value['value']) {
                $this->helpers->switchDatabaseConnection('mysql');
                $volunteeringTime = \DB::table('tenant_setting')->where('key', config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION'))->first();
                $this->helpers->switchDatabaseConnection('tenant');
                $volunteeringTimeSetting = \DB::table('tenant_setting')->where('setting_id', $volunteeringTime->tenant_setting_id)->first();
                $volunteeringTimeSetting = $this->tenantActivatedSetting->where(['tenant_setting_id' => $volunteeringTimeSetting->tenant_setting_id])->first();

                if (!$volunteeringTimeSetting) {
                    return false;
                }
            }
            $this->helpers->switchDatabaseConnection('tenant');
        }
        $this->helpers->switchDatabaseConnection('tenant');
        return true;
    }

    /**
     * Check volunteering setting is disabled or not
     *
     * @param array $data
     * @return bool
     */
    public function checkVolunteeringSettingDisabled(array $data)
    {
        $this->helpers->switchDatabaseConnection('mysql');
        $volunteering = \DB::table('tenant_setting')->where('key', config('constants.tenant_settings.VOLUNTEERING'))->first();
        $this->helpers->switchDatabaseConnection('tenant');
        $tenantVolunteeringSetting = \DB::table('tenant_setting')->where('setting_id', $volunteering->tenant_setting_id)->first();
        if (!$tenantVolunteeringSetting) {
            return false;
        }
        $volunteeringSetting = $this->tenantActivatedSetting->where('tenant_setting_id', $tenantVolunteeringSetting->tenant_setting_id)->first();

        if (!$volunteeringSetting) {
            foreach ($data['settings'] as $value) {
                $this->helpers->switchDatabaseConnection('tenant');
                $tenantSetting = \DB::table('tenant_setting')->where('tenant_setting_id', $value['tenant_setting_id'])->first();
                $this->helpers->switchDatabaseConnection('mysql');
                $masterSetting = \DB::table('tenant_setting')->where('tenant_setting_id', $tenantSetting->setting_id)->first();

                if ($masterSetting->key == config('constants.tenant_settings.VOLUNTEERING_TIME_MISSION')
                    || $masterSetting->key == config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION')) {
                    return false;
                }
            }
        }
        $this->helpers->switchDatabaseConnection('tenant');
        return true;
    }
}
