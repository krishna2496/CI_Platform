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

        $tenantOptions = $pdo->query('select activity_log_id,object_value from activity_log')->fetchAll();

        if (!empty($tenantOptions)) {
            foreach ($tenantOptions as $tenantOption) {
                if ($tenantOption['object_value'] === null) {
                    continue;
                } else {
                    $data = @json_decode($tenantOption['object_value'], true);
                }

                if ($data !== null) {
                    $tenantOptionArray = json_decode($tenantOption['object_value'], true);
                    $jsonData  = serialize($tenantOptionArray);

                    $pdo->prepare('
                        UPDATE activity_log
                        SET `object_value` = :object_value
                        WHERE activity_log_id = :id
                    ')
                        ->execute([
                            'object_value' => $jsonData,
                            'id' => $tenantOption['activity_log_id']
                        ]);
                }
            }
        }
    }
}
