<?php

require_once(__DIR__.'/../../../../bootstrap/app.php');

$db = app()->make('db');

$pdo = setAdminDatabaseConnection($db);

// Fetch all tenants
$tenants = $pdo->query('
        SELECT tenant_id
        FROM tenant
        WHERE deleted_at is NULL
    ')
    ->fetchAll();
$tenantSettingData = $pdo->query('
        SELECT tenant_setting_id
        FROM tenant_setting
        WHERE tenant_setting.key IN ("volunteering_time_mission", "volunteering_goal_mission")
            AND deleted_at is null
    ')
    ->fetchAll();

if (!count($tenantSettingData)) {
    echo 'Tenant setting not found.';
    exit;
}

foreach ($tenantSettingData as $key => $setting) {
    foreach ($tenants as $tenant) {
        $tenantSettingId = $setting['tenant_setting_id'];
        $tenantId = $tenant['tenant_id'];

        $params['settings'] = [
            'tenant_setting_id' => $tenantSettingId,
            'value' => 1,
        ];

        $pdo = setAdminDatabaseConnection($db);

        // Add settings into tenant_has_setting (master database)
        $tenantHasSettingSql = $pdo->prepare('
            SELECT * FROM tenant_has_setting
            WHERE tenant_id = ?
                AND tenant_setting_id = ?
                AND deleted_at is null
        ');
        $tenantHasSettingSql->execute([$tenantId, $tenantSettingId]);
        $tenantHasSettingData = $tenantHasSettingSql->fetchAll();

        if (count($tenantHasSettingData) === 0) {
            $pdo->prepare('
                    INSERT INTO tenant_has_setting
                    (tenant_id, tenant_setting_id, created_at)
                    VALUES (:tenant_id, :tenant_setting_id, :created_at)
                ')
                ->execute([
                    'tenant_id' => $tenantId,
                    'tenant_setting_id' => $tenantSettingId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
        }

        $db->purge('tenant');
        // Create connection to tenant
        \Illuminate\Support\Facades\Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => 'ci_tenant_'.$tenantId,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);
        // Create connection for the tenant database
        $pdo = $db->connection('tenant')->getPdo();

        // Set default database
        \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

        // Add settings into tenant_setting (tenant's database)
        $tenantSettingSql = $pdo->prepare('
            SELECT * FROM tenant_setting
            WHERE setting_id = ?
            AND deleted_at IS NULL
        ');
        $tenantSettingSql->execute([$tenantSettingId]);
        $tenantSettingData = $tenantSettingSql->fetchAll();

        if (count($tenantSettingData) === 0) {
            $pdo->prepare('
                    INSERT INTO tenant_setting
                    (setting_id, created_at)
                    VALUES (:setting_id, :created_at)
                ')
                ->execute([
                    'setting_id' => $tenantSettingId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

            $lastInsertTenantSettingId = $pdo->lastInsertId();

            //Activate setting in tenant_activated_setting (tenant's database)
            $pdo->prepare('
                    INSERT INTO tenant_activated_setting
                    (tenant_setting_id, created_at)
                    VALUES (:tenant_setting_id, :created_at)
                ')
                ->execute([
                    'tenant_setting_id' => $lastInsertTenantSettingId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
        }
    }
}

function setAdminDatabaseConnection($db)
{
    $db->purge('mysql');

    // Create connection to db
    \Illuminate\Support\Facades\Config::set('database.connections.tenant', [
        'driver' => 'mysql',
        'host' => env('DB_HOST'),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
    ]);

    // Create connection for the admin database
    $pdo = $db->connection('mysql')->getPdo();

    // Set default database
    \Illuminate\Support\Facades\Config::set('database.default', 'mysql');

    return $pdo;
}
