<?php

require_once('bootstrap/app.php');

$db = app()->make('db');

$pdo = $db->connection('mysql')->getPdo();

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

        $footerPageLanguages = $pdo->query('select id,description from footer_pages_language')->fetchAll();
        if (!empty($footerPageLanguages)) {
            foreach ($footerPageLanguages as $footerPageLanguage) {
                if ($footerPageLanguage['description'] === null) {
                    continue;
                } else {
                    $data = @json_decode($footerPageLanguage['description'], true);
                }

                if ($data !== null) {
                    $footerPageLanguageArray = json_decode($footerPageLanguage['description'], true);
                    $jsonData  = serialize($footerPageLanguageArray);

                    $pdo->exec('SET NAMES utf8mb4');
                    $pdo->exec('SET CHARACTER SET utf8mb4');

                    $pdo->prepare('
                        UPDATE footer_pages_language
                        SET `description` = :description
                        WHERE id = :id
                    ')
                        ->execute([
                            'description' => $jsonData,
                            'id' => $footerPageLanguage['id']
                        ]);
                }
            }
        }
    }
}
