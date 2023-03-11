<?php

require_once(__DIR__.'/../../../../bootstrap/app.php');
use Ramsey\Uuid\Uuid;

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

        $themes = $pdo->query('select mission_theme_id, translations from mission_theme')->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($themes)) {
            foreach ($themes as $theme) {
                $themeTranslations = json_decode($theme['translations'], true);

                $trans = [];
                foreach ($themeTranslations as $translation) {
                    $trans[$translation['lang']] = $translation['title'];
                }
                $translations = json_encode($trans, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $pdo->prepare('
                        UPDATE mission_theme
                        SET translations = :translations
                        WHERE mission_theme_id = :id
                    ')
                    ->execute([
                        'translations' => $translations,
                        'id' => $theme['mission_theme_id'],
                    ]);
            }
        }
    }
}
