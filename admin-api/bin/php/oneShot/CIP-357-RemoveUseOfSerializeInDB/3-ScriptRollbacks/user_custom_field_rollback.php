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

        $userCustomFields = $pdo->query('select translations,field_id from user_custom_field')->fetchAll();
        if (!empty($userCustomFields)) {
            foreach ($userCustomFields as $userCustomField) {
                if ($userCustomField['translations'] === null) {
                    continue;
                } else {
                    $data = @json_decode($userCustomField['translations'], true);
                }

                if ($data !== null) {
                    $userCustomFieldArray = json_decode($userCustomField['translations'], true);
                    $jsonData  = serialize($userCustomFieldArray);

                    $pdo->prepare('
                        UPDATE user_custom_field
                        SET `translations` = :translations
                        WHERE field_id = :id
                    ')
                        ->execute([
                            'translations' => $jsonData,
                            'id' => $userCustomField['field_id']
                        ]);
                }
            }
        }
    }
}
