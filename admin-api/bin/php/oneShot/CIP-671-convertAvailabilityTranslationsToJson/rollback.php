<?php

require_once(__DIR__.'/../../../../bootstrap/app.php');

$db = app()->make('db');
$pdo = $db->connection('mysql')->getPdo();

\Illuminate\Support\Facades\Config::set('database.default', 'mysql');
$tenants = $pdo->query('select * from tenant where status = 1 and deleted_at IS NULL')->fetchAll();

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
        $availabilities = $pdo->query('select availability_id, translations from availability')->fetchAll();

        if (!empty($availabilities)) {
            foreach ($availabilities as $availability) {

                $availabilityTranslations = json_decode($availability['translations'], true);

                $trans = [];
                foreach ($availabilityTranslations as $key => $translation) {
                    $trans[] = [
                        'lang' => $key,
                        'title' => $translation
                    ];
                }

                $translations = json_encode($trans, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $pdo->prepare('
                        UPDATE availability
                        SET translations = :translations
                        WHERE availability_id = :id
                    ')
                    ->execute([
                        'translations' => $translations,
                        'id' => $availability['availability_id'],
                    ]);
            }
        }
    }
}
