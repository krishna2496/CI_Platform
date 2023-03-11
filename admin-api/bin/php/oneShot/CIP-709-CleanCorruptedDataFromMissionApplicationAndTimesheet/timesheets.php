<?php

require_once(__DIR__.'/../../../../bootstrap/app.php');

$db = app()->make('db');

$pdo = $db->connection('mysql')->getPdo();

\Illuminate\Support\Facades\Config::set('database.default', 'mysql');

$tenants = $pdo->query('select * from tenant where status=1 AND deleted_at IS NULL')->fetchAll();

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

    // Set default database
    \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

    $corruptedIds = $pdo->query('
        SELECT t.timesheet_id
        FROM timesheet t
        INNER JOIN mission m
        ON m.mission_id = t.mission_id
        WHERE t.deleted_at IS NULL
        AND m.deleted_at IS NOT NULL
        ')
        ->fetchAll(PDO::FETCH_COLUMN);

    $deletedAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    foreach ($corruptedIds as $id) {
        $pdo->prepare('
            UPDATE timesheet
            SET deleted_at = :deleted_at
            WHERE timesheet_id = :id
            ')
            ->execute([
                'deleted_at' => $deletedAt,
                'id' => $id
            ]);
    }
}
