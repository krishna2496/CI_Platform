<?php
require_once('bootstrap/app.php');

$db = app()->make('db');
$db->purge('tenant');
// Create connection to tenant
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

$rows = $pdo->query('SELECT activity_log_id, object_value FROM activity_log')->fetchAll();

foreach ($rows as $row) {
    if ($row['object_value'] === null) {
        continue;
    }


    $id = $row['activity_log_id'];
    try {
        $value = unserialize($row['object_value']);
        $pdo->exec('SET NAMES utf8mb4');
        $pdo->exec('SET CHARACTER SET utf8mb4');

        $pdo->prepare('
        UPDATE activity_log
        SET object_value = :object_value
        WHERE activity_log_id = :id
    ')
            ->execute([
                'object_value' => serialize($value),
                'id' => $id
            ]);

    } catch (\Exception $exception) {
        // Data is already well encoded, do nothing
        continue;
    }
}
