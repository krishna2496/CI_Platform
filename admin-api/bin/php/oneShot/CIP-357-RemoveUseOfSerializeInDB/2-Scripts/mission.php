<?php

require_once('bootstrap/app.php');

$db = app()->make('db');

$pdo = $db->connection('mysql')->getPdo();
$pdo->exec('SET NAMES utf8mb4');
$pdo->exec('SET CHARACTER SET utf8mb4');

\Illuminate\Support\Facades\Config::set('database.default', 'mysql');
$tenants = $pdo->query('select * from tenant where status=1 and deleted_at is null')->fetchAll();

if (count($tenants) > 0) {
    foreach ($tenants as $tenant) {
        $tenantId = $tenant['tenant_id'];
        $db->purge('tenant');
        // Create connection to tenant
        \Illuminate\Support\Facades\Config::set('database.connections.tenant', array(
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => 'ci_tenant_'.$tenantId,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ));
        // Create connection for the tenant database
        $pdo = $db->connection('tenant')->getPdo();
        $pdo->exec('SET NAMES utf8mb4');
        $pdo->exec('SET CHARACTER SET utf8mb4');

        // Set default database
        \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

        $missions = $pdo->query('select mission_id,organisation_detail from mission')->fetchAll();
        if (!empty($missions)) {
            foreach ($missions as $mission) {
                if ($mission['organisation_detail'] === null) {
                    continue;
                } else {
                    $data = @unserialize($mission['organisation_detail']);
                }

                if ($data !== false) {
                    $missionArray = unserialize($mission['organisation_detail']);
                    $jsonData  = json_encode($missionArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    $pdo->prepare('
                        UPDATE mission
                        SET `organisation_detail` = :organisation_detail
                        WHERE mission_id = :id
                    ')
                        ->execute([
                            'organisation_detail' => $jsonData,
                            'id' => $mission['mission_id']
                        ]);
                }  else {
                    var_dump(
                        'Needs manual verification for following context: ' . json_encode(
                            [
                                'tenantId' => $tenantId,
                                'table' => 'mission',
                                'column' => 'organisation_detail',
                                'id' => $mission['mission_id']
                            ])
                    );
                }
            }
        }
    }
}
