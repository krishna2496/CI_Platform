<?php

require_once(__DIR__.'/../../../../bootstrap/app.php');

$db = app()->make('db');
$pdo = $db->connection('mysql')->getPdo();

\Illuminate\Support\Facades\Config::set('database.default', 'mysql');
$tenants = $pdo->query('select * from tenant where status = 1 and deleted_at IS NULL')->fetchAll();

foreach ($tenants as $tenant) {
    $tenantId = $tenant['tenant_id'];
    $db->purge('tenant');
    // Create connection to tenant
    \Illuminate\Support\Facades\Config::set('database.connections.tenant', array(
        'driver' => 'mysql',
        'host' => env('DB_HOST'),
        'database' => 'ci_tenant_' . $tenantId,
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
    ));
    // Create connection for the tenant database
    $pdo = $db->connection('tenant')->getPdo();
    $pdo->exec('SET NAMES utf8mb4');
    $pdo->exec('SET CHARACTER SET utf8mb4');

    // Set default database
    \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

    $skills = $pdo->query('select skill_id, translations from skill')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($skills as $skill) {
        $skillTranslations = json_decode($skill['translations'], true);

        $trans = [];
        foreach ($skillTranslations as $key => $translation) {
            if (!is_array($translation) || !array_key_exists('lang', $translation) || !array_key_exists('title', $translation)) {
                dump('Need manual verification: tenant id: ' . $tenantId . ' and skill id: '. $skill['skill_id']);
                $trans[$key] = $translation;
                continue;
            }

            $trans[$translation['lang']] = $translation['title'];
        }
        $translations = json_encode($trans, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $pdo->prepare('
            UPDATE skill
            SET translations = :translations
            WHERE skill_id = :id
        ')
        ->execute([
            'translations' => $translations,
            'id' => $skill['skill_id'],
        ]);
    }
}
