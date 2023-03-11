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

        $missionThemes = $pdo->query('select mission_theme_id,translations from mission_theme')->fetchAll();
        if (!empty($missionThemes)) {
            foreach ($missionThemes as $missionTheme) {
                if ($missionTheme['translations'] === null) {
                    continue;
                } else {
                    $data = @json_decode($missionTheme['translations'], true);
                }

                if ($data !== null) {
                    $missionThemeArray = json_decode($missionTheme['translations'], true);
                    $jsonData  = serialize($missionThemeArray);

                    $pdo->prepare('
                        UPDATE mission_theme
                        SET `translations` = :translations
                        WHERE mission_theme_id = :mission_theme_id
                    ')
                        ->execute([
                            'translations' => $jsonData,
                            'mission_theme_id' => $missionTheme['mission_theme_id']
                        ]);
                }
            }
        }
    }
}
