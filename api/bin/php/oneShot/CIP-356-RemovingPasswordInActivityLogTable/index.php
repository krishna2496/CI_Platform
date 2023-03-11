<?php

require_once('../../../../bootstrap/app.php');

$db = app()->make('db');

// Create connection to db
\Illuminate\Support\Facades\Config::set('database.connections.tenant', array(
    'driver' => 'mysql',
    'host' => env('DB_HOST'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
));

// Create connection for the admin database
$pdo = $db->connection('mysql')->getPdo();

// Set default database
\Illuminate\Support\Facades\Config::set('database.default', 'mysql');

// Get all the tenants id
$tenants = $pdo->query('select tenant_id from tenant where deleted_at is null')->fetchAll();

foreach ($tenants as $tenant) {
    echo 'Processing tenant ' . $tenant['tenant_id'] . "... \n";

    // Create connection to the tenant
    \Illuminate\Support\Facades\Config::set('database.connections.tenant', array(
        'driver' => 'mysql',
        'host' => env('DB_HOST'),
        'database' => 'ci_tenant_' . $tenant['tenant_id'],
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
    ));

    // As we are in a loop, we need to purge the connection to clear the cache
    $db->purge('tenant');

    // Create connection for the tenant database
    $pdo = $db->connection('tenant')->getPdo();

    // Set default database
    \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

    // Get all logs which can potentially contain a clear password
    $activitiesLog = $pdo->query(
        "select activity_log_id, object_value 
        from activity_log 
        where `type` like '%USER%' 
        or `type` like '%AUTH%'"
    )
        ->fetchAll();

    $logKeysToDelete = [
        'confirm_password',
        'old_password',
        'password',
        'password_confirmation'
    ];

    $numberOfDeletedLog = 0;

    foreach ($activitiesLog as $activityLog) {
        $id = $activityLog['activity_log_id'];
        $log = unserialize($activityLog['object_value']);

        if (is_array($log)) {
            $needModification = false;

            foreach ($logKeysToDelete as $logKeyToDelete) {
                if (array_key_exists($logKeyToDelete, $log)) {
                    unset($log[$logKeyToDelete]);
                    $needModification = true;
                }
            }

            if ($needModification) {
                $pdo->prepare('
                UPDATE activity_log
                SET `object_value` = :object_value
                WHERE `activity_log_id` = :id
            ')
                    ->execute(['object_value' => serialize($log), 'id' => $id]);
                $numberOfDeletedLog++;
            }
        }
    }
    echo 'Deleted ' . $numberOfDeletedLog . " password(s) in activity_log table for this tenant. \n \n";
}