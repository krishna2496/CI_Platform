<?php

require_once(__DIR__.'/../../../../bootstrap/app.php');

$db = app()->make('db');

$pdo = $db->connection('mysql')->getPdo();

\Illuminate\Support\Facades\Config::set('database.default', 'mysql');

$tenants = $pdo->query('select * from tenant where status=1 AND deleted_at IS NULL')->fetchAll();

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

        $uniqueOrganizationDataFromMission = $pdo
        ->query('select organisation_id, organisation_name, created_at from mission GROUP BY organisation_id ORDER BY created_at DESC')
        ->fetchAll();

        if (!empty($uniqueOrganizationDataFromMission)) {
            foreach ($uniqueOrganizationDataFromMission as $organizationData) {
                $id = $organizationData['organisation_id'];
                $name = $organizationData['organisation_name'];
                $createdAt = $organizationData['created_at'];
                $pdo->exec('SET NAMES utf8mb4');
                $pdo->exec('SET CHARACTER SET utf8mb4');

                $sql = $pdo->prepare("SELECT organization_id FROM organization WHERE organization_id=?");
                $sql->execute([$id]);
                $getExistingOrganization = $sql->fetchAll();

                if (count($getExistingOrganization) === 0) {
                    $pdo->prepare('
                        INSERT INTO organization (organization_id, name, created_at) VALUES
                        (:id, :name, :created_at)
                    ')
                    ->execute([
                        'id' => $id,
                        'name' => $name,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }
}
