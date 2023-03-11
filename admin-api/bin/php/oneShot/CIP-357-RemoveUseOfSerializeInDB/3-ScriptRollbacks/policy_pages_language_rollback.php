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

        $policyPageLanguages = $pdo->query('select id,description from policy_pages_language')->fetchAll();
        if (!empty($policyPageLanguages)) {
            foreach ($policyPageLanguages as $policyPageLanguage) {
                if ($policyPageLanguage['description'] === null) {
                    continue;
                } else {
                    $data = @json_decode($policyPageLanguage['description'], true);
                }

                if ($data !== null) {
                    $policyPageLanguageArray = json_decode($policyPageLanguage['description'], true);
                    $jsonData  = serialize($policyPageLanguageArray);

                    $pdo->prepare('
                        UPDATE policy_pages_language
                        SET `description` = :description
                        WHERE id = :id
                    ')
                        ->execute([
                            'description' => $jsonData,
                            'id' => $policyPageLanguage['id']
                        ]);
                }
            }
        }
    }
}
