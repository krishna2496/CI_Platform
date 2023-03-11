<?php

require_once(__DIR__.'/../OneShot.php');


use Carbon\Carbon;
use Illuminate\Support\Collection;
use optimy\console\OneShot;


class TenantSettingsDedupe extends OneShot
{
    const TENANT_TABLE = 'tenant';
    const TENANT_SETTING_TABLE = 'tenant_setting';
    const TENANT_HAS_SETTING_TABLE = 'tenant_has_setting';
    const TENANT_ACTIVATED_SETTING_TABLE = 'tenant_activated_setting';

    /**
     * @var array
     */
    private $errorsFound = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function start()
    {
        $this->writeLn('Removing duplicate tenant settings for all existing tenants.');

        $tenants = $this->getTenants();
        if (!$tenants->count()) {
            return $this->warn('There are no available tenants to process.');
        }

        $redundantMasterSettings = $this->getRedundantMasterSettings();
        if (!$redundantMasterSettings->count()) {
            return $this->warn('There are no duplicate settings to process.');
        }

        $this->removeRedundantMasterSettings($tenants, $redundantMasterSettings);
        $this->removeRedundantTenantSettings($tenants, $redundantMasterSettings);
        $this->writeLn();

        if (count($this->errorsFound)) {
            $this->writeLn('Processing all tenants finished with errors!');
            $this->writeLn(str_repeat('-', 40));
            $this->warn(sprintf(implode("\n", $this->errorsFound)));
        } else {
            $this->writeLn('Processing all tenants finished successfully!');
        }
    }

    private function removeRedundantMasterSettings(Collection $tenants, Collection $redundantMasterSettings)
    {
        foreach ($tenants as $tenant) {
            try {
                foreach ($redundantMasterSettings as $setting) {
                    // extract the first ID used for tenant setting ID
                    $tenantSettingsFirstId = min($setting->tenant_setting_ids);

                    $tenantMasterSettings = $this->getTenantMasterSettings($setting->tenant_setting_ids);
                    $tenantHasSettings = $this->getTenantHasSettings($tenant->tenant_id, $setting->tenant_setting_ids);

                    // disable all redundant master tenant settings.
                    $redundantIds = array_diff($setting->tenant_setting_ids, [$tenantSettingsFirstId]);
                    if (count($redundantIds)) {
                        $this->disableMasterTenantSettings($redundantIds);
                    }

                    // disable all redundant activated tenant settings.
                    $this->disableTenantHasSettings($tenant->tenant_id, $setting->tenant_setting_ids);

                    // if at least 1 of the redundant master setting is active for a key,
                    // then check if it has a corresponding setting activated for this tenant.
                    $enabledSettings = $tenantMasterSettings->count();
                    $disabledSettings = $tenantMasterSettings->whereNotNull('deleted_at')->count();
                    if (($enabledSettings - $disabledSettings) > 0 && $tenantHasSettings->count()) {
                        // the available mmaster setting has an activated tenant setting.
                        // check if first ID exists, add if it is not, activate if it exists.
                        if ($tenantHasSettings->has($tenantSettingsFirstId)) {
                            $this->enableTenantHasSettings($tenant->tenant_id, $tenantSettingsFirstId);
                        } else {
                            $this->addTenantHasSettings($tenant->tenant_id, $tenantSettingsFirstId);
                        }
                    }
                }
            } catch (Exception $exception) {
                $this->errorsFound[] = $this->formatError($exception);
            }
        }
    }

    private function removeRedundantTenantSettings(Collection $tenants, Collection $redundantMasterSettings)
    {
        foreach ($tenants as $tenant) {
            try {
                $this->connectTenantDb($tenant->tenant_id);

                foreach ($redundantMasterSettings as $setting) {
                    // extract the first ID used for tenant setting ID
                    $masterSettingsFirstId = min($setting->tenant_setting_ids);

                    $tenantAvailableSettings = $this->getTenantAvailableSettings($setting->tenant_setting_ids);
                    $tenantAvailableSettingsIds = $tenantAvailableSettings->keys()->all();

                    if ($tenantAvailableSettings->count()) {
                        // disable all available tenant settings entries.
                        $this->disableTenantAvailableSettings($tenantAvailableSettingsIds);
                    }

                    $tenantActivatedSettings = $this->getTenantActivatedSettings($tenantAvailableSettingsIds);
                    $tenantActivatedSettingsIds = $tenantActivatedSettings->keys()->all();

                    if (!$tenantAvailableSettings->count() && !$tenantActivatedSettings->count()) {
                        continue;
                    }

                    if ($tenantActivatedSettings->count()) {
                        // disable all activated tenant settings entries.
                        $this->disableTenantActivatedSettings($tenantActivatedSettingsIds);
                    }

                    // reinstate the entry if tenant setting was made available to tenant, and if it was activated.
                    if ($tenantAvailableSettings->whereNull('deleted_at')->count()) {

                        // check if the master tenant setting's first used ID was made available to tenant.
                        $availableSettingsFirstIds = $tenantAvailableSettings->where('setting_id', $masterSettingsFirstId);
                        if ($availableSettingsFirstIds->count()) {
                            // find and enable the first ID of an existing available entry.
                            $newTenantSettingId = $availableSettingsFirstIds->min('tenant_setting_id');
                            $this->enableTenantAvailableSetting($newTenantSettingId);
                        } else {
                            // a different ID was made available, create a new one with correct first ID.
                            $newTenantSettingId = $this->addTenantAvailableSetting($masterSettingsFirstId);
                        }

                        // check if at least one available setting was activated and is enabled.
                        if ($tenantActivatedSettings->whereNull('deleted_at')->count()) {

                            $tenantActivatedSettingIds = $tenantActivatedSettings->where('tenant_setting_id', $newTenantSettingId);
                            if ($tenantActivatedSettingIds->count()) {
                                // find and enable the first ID of an existing activated entry.
                                $activatedTenantSettingId = $tenantActivatedSettingIds->min('tenant_activated_setting_id');
                                $this->enableTenantActivatedSetting($activatedTenantSettingId);
                            } else {
                                // a different ID was activated, create a new one with correct first ID.
                                $this->addTenantActivatedSetting($newTenantSettingId);
                            }
                        }
                    }
                }

                $this->disconnectTenantDb();
            } catch (Exception $exception) {
                $this->errorsFound[] = $this->formatError($exception);
            }
        }
    }

    private function getTenants(): Collection
    {
        return $this->getDbTable(self::TENANT_TABLE)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('tenant_id');
    }

    private function getRedundantMasterSettings(): Collection
    {
        $settings = $this->getDbTable(self::TENANT_SETTING_TABLE)
            ->select([
                'key',
                DB::raw('count(`key`) group_count'),
                DB::raw('group_concat(tenant_setting_id) tenant_setting_ids'),
            ])
            ->whereNull('deleted_at')
            ->groupBy('key')
            ->having('group_count', '>', 1)
            ->get()
            ->keyBy('key');
        $settings->map(function($setting) {
            // convert the comma separated IDs to sorted array
            $setting->tenant_setting_ids = array_unique(explode(',', $setting->tenant_setting_ids));
            sort($setting->tenant_setting_ids);
        });
        return $settings;
    }

    private function getTenantMasterSettings(array $tenantSettingIds): Collection
    {
        return $this->getDbTable(self::TENANT_SETTING_TABLE)
            ->select([
                'key',
                'tenant_setting_id',
                'deleted_at',
            ])
            ->whereIn('tenant_setting_id', $tenantSettingIds)
            ->orderBy('tenant_setting_id')
            ->get()
            ->keyBy('tenant_setting_id');
    }

    private function getTenantHasSettings(int $tenantId, array $tenantSettingIds): Collection
    {
        return $this->getDbTable(self::TENANT_HAS_SETTING_TABLE)
            ->select([
                'tenant_id',
                'tenant_setting_id',
                'deleted_at',
            ])
            ->where('tenant_id', $tenantId)
            ->whereIn('tenant_setting_id', $tenantSettingIds)
            ->orderBy('tenant_id')
            ->orderBy('tenant_setting_id')
            ->get()
            ->keyBy('tenant_setting_id');
    }

    private function disableMasterTenantSettings(array $tenantSettingIds): int
    {
        return $this->getDbTable(self::TENANT_SETTING_TABLE)
            ->whereNull('deleted_at')
            ->whereIn('tenant_setting_id', $tenantSettingIds)
            ->update(['deleted_at' => Carbon::now()]);
    }

    private function addTenantHasSettings(int $tenantId, int $tenantSettingId): int
    {
        return $this->getDbTable(self::TENANT_HAS_SETTING_TABLE)
            ->insert([
                'tenant_id' => $tenantId,
                'tenant_setting_id' => $tenantSettingId,
            ]);
    }

    private function enableTenantHasSettings(int $tenantId, int $tenantSettingId): int
    {
        return $this->getDbTable(self::TENANT_HAS_SETTING_TABLE)
            ->whereNotNull('deleted_at')
            ->where('tenant_id', $tenantId)
            ->where('tenant_setting_id', $tenantSettingId)
            ->take(1)
            ->update(['deleted_at' => null]);
    }

    private function disableTenantHasSettings(int $tenantId, array $tenantSettingIds): int
    {
        return $this->getDbTable(self::TENANT_HAS_SETTING_TABLE)
            ->whereNull('deleted_at')
            ->where('tenant_id', $tenantId)
            ->whereIn('tenant_setting_id', $tenantSettingIds)
            ->update(['deleted_at' => Carbon::now()]);
    }

    private function getTenantAvailableSettings(array $settingIds): Collection
    {
        return $this->getDbTable(self::TENANT_SETTING_TABLE)
            ->select([
                'setting_id',
                'tenant_setting_id',
                'deleted_at',
            ])
            ->whereIn(
                'setting_id',
                $settingIds
            )
            ->orderBy('setting_id')
            ->orderBy('tenant_setting_id')
            ->get()
            ->keyBy('tenant_setting_id');
    }

    private function addTenantAvailableSetting(int $settingId): int
    {
        return $this->getDbTable(self::TENANT_SETTING_TABLE)
            ->insertGetId(['setting_id' => $settingId]);
    }

    private function enableTenantAvailableSetting(int $tenantSettingId): int
    {
        return $this->getDbTable(self::TENANT_SETTING_TABLE)
            ->whereNotNull('deleted_at')
            ->where(
                'tenant_setting_id',
                $tenantSettingId
            )
            ->update(['deleted_at' => null]);
    }

    private function disableTenantAvailableSettings(array $tenantSettingIds): int
    {
        return $this->getDbTable(self::TENANT_SETTING_TABLE)
            ->whereNull('deleted_at')
            ->whereIn(
                'tenant_setting_id',
                $tenantSettingIds
            )
            ->update(['deleted_at' => Carbon::now()]);
    }

    private function getTenantActivatedSettings(array $tenantSettingIds): Collection
    {
        return $this->getDbTable(self::TENANT_ACTIVATED_SETTING_TABLE)
            ->select([
                'tenant_activated_setting_id',
                'tenant_setting_id',
                'deleted_at',
            ])
            ->whereIn(
                'tenant_setting_id',
                $tenantSettingIds
            )
            ->get()
            ->keyBy('tenant_activated_setting_id');
    }

    private function addTenantActivatedSetting(int $tenantSettingId): int
    {
        return $this->getDbTable(self::TENANT_ACTIVATED_SETTING_TABLE)
            ->insertGetId(['tenant_setting_id' => $tenantSettingId]);
    }

    private function enableTenantActivatedSetting(int $tenantActivatedSettingId): int
    {
        return $this->getDbTable(self::TENANT_ACTIVATED_SETTING_TABLE)
            ->whereNotNull('deleted_at')
            ->where(
                'tenant_activated_setting_id',
                $tenantActivatedSettingId
            )
            ->update(['deleted_at' => null]);
    }

    private function disableTenantActivatedSettings(array $tenantActivatedSettingIds): int
    {
        return $this->getDbTable(self::TENANT_ACTIVATED_SETTING_TABLE)
            ->whereNull('deleted_at')
            ->whereIn(
                'tenant_activated_setting_id',
                $tenantActivatedSettingIds
            )
            ->update(['deleted_at' => Carbon::now()]);
    }
}


// instantiate TenantSettingsDedupe class with the application
// container and then make a call to the entry point method.
$cli = $app->make(TenantSettingsDedupe::class);
$cli->start();
