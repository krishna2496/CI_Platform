<?php

require_once ('../../../../bootstrap/app.php');

$db = app()->make('db');

// Create connection to tenant 7
\Illuminate\Support\Facades\Config::set('database.connections.tenant', array(
    'driver' => 'mysql',
    'host' => env('DB_HOST'),
    'database' => 'ci_tenant_7',
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
));
// Create connection for the tenant database
$pdo = $db->connection('tenant')->getPdo();

// Set default database
\Illuminate\Support\Facades\Config::set('database.default', 'tenant');

$skills = $pdo->query('select * from skill')->fetchAll();

foreach ($skills as $skill) {
    $skillId = $skill['skill_id'];
    $translations = unserialize($skill['translations']);

    $pdo->exec('SET NAMES utf8mb4');
    $pdo->exec('SET CHARACTER SET utf8mb4');

    $pdo->prepare('
        UPDATE skill
        SET `translations` = :skillTranslations
        WHERE skill_id = :skillId
    ')
        ->execute([
            'skillTranslations' => serialize($translations),
            'skillId' => $skillId
        ]);
}
