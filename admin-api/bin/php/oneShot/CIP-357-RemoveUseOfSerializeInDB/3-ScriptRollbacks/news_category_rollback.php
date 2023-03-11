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

        $newsCategories = $pdo->query('select news_category_id,translations from news_category')->fetchAll();
        if (!empty($newsCategories)) {
            foreach ($newsCategories as $newsCategory) {
                if ($newsCategory['translations'] === null) {
                    continue;
                } else {
                    $data = @json_decode($newsCategory['translations'], true);
                }

                if ($data !== null) {
                    $newsCategoryArray = json_decode($newsCategory['translations'], true);
                    $jsonData  = serialize($newsCategoryArray);

                    $pdo->prepare('
                        UPDATE news_category
                        SET `translations` = :translations
                        WHERE news_category_id = :news_category_id
                    ')
                        ->execute([
                            'translations' => $jsonData,
                            'news_category_id' => $newsCategory['news_category_id']
                        ]);
                }
            }
        }
    }
}
