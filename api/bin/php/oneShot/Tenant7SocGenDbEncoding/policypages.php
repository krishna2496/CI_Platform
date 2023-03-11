<?php

require_once('../../../../bootstrap/app.php');

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

$items = $pdo->query('select * from policy_pages_language')->fetchAll();

foreach ($items as $item) {
    $id = $item['id'];
    $title = $item['title'];
    $description = unserialize($item['description']);

    $pdo->exec('SET NAMES utf8mb4');
    $pdo->exec('SET CHARACTER SET utf8mb4');

    $pdo->prepare('
        UPDATE policy_pages_language
        SET description = :description,
        title = :title
        WHERE id = :id
    ')
        ->execute([
            'description' => serialize($description),
            'title' => $title,
            'id' => $id
        ]);
}
