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

        $tenantOptions = $pdo->query('select tenant_option_id,option_value from tenant_option')->fetchAll();
        if (!empty($tenantOptions)) {
            foreach ($tenantOptions as $tenantOption) {
                if ($tenantOption['option_value'] === null) {
                    continue;
                } else {
                    $data = @json_decode($tenantOption['option_value'], true);
                }

                if ($data !== null) {
                    $tenantOptionArray = json_decode($tenantOption['option_value'], true);
                    $jsonData  = serialize($tenantOptionArray);

                    $pdo->prepare('
                        UPDATE tenant_option
                        SET `option_value` = :option_value
                        WHERE tenant_option_id = :id
                    ')
                        ->execute([
                            'option_value' => $jsonData,
                            'id' => $tenantOption['tenant_option_id']
                        ]);
                }
            }
        }
    }
}
