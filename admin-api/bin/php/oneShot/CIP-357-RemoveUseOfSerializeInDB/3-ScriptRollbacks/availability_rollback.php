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

        $availabilities = $pdo->query('select availability_id,translations from availability')->fetchAll();
        if (!empty($availabilities)) {
            foreach ($availabilities as $availability) {
                if ($availability['translations'] === null) {
                    continue;
                } else {
                    $data = @json_decode($availability['translations'], true);
                }

                if ($data !== null) {
                    $availabilityArray = json_decode($availability['translations'], true);
                    $jsonData  = serialize($availabilityArray);

                    $pdo->prepare('
                        UPDATE availability
                        SET `translations` = :translations
                        WHERE availability_id = :availability_id
                    ')
                        ->execute([
                            'translations' => $jsonData,
                            'availability_id' => $availability['availability_id']
                        ]);
                }
            }
        }
    }
}
