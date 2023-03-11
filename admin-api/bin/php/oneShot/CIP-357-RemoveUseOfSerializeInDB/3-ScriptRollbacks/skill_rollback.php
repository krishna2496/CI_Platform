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

        $skills = $pdo->query('select translations,skill_id from skill')->fetchAll();
        if (!empty($skills)) {
            foreach ($skills as $skill) {
                if ($skill['translations'] === null) {
                    continue;
                } else {
                    $data = @json_decode($skill['translations'], true);
                }

                if ($data !== null) {
                    $skillArray = json_decode($skill['translations'], true);
                    $jsonData  = serialize($skillArray);

                    $pdo->prepare('
                        UPDATE skill
                        SET `translations` = :translations
                        WHERE skill_id = :skill_id
                    ')
                        ->execute([
                            'translations' => $jsonData,
                            'skill_id' => $skill['skill_id']
                        ]);
                }
            }
        }
    }
}
