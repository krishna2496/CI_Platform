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
        
        // Set default database
        \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

        $missionData = $pdo->query('select mission_id, total_seats, availability_id, is_virtual from mission')->fetchAll();
        if (!empty($missionData)) {
            foreach ($missionData as $mission) {
                    $pdo->prepare('
                        INSERT INTO volunteering_attribute
                        (volunteering_attribute_id, mission_id, availability_id, total_seats, is_virtual, created_at)
                        VALUES (:volunteering_attribute_id, :mission_id, :availability_id, :total_seats, :is_virtual, :created_at)
                    ')
                        ->execute([
                            'volunteering_attribute_id' => Uuid::uuid4()->toString(),
                            'mission_id' => $mission['mission_id'],
                            'availability_id' => $mission['availability_id'],
                            'total_seats' => $mission['total_seats'],
                            'is_virtual' => $mission['is_virtual'],
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
            }
        }
    }
}