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

        $missionLanguages = $pdo->query('select mission_language_id,description,custom_information from mission_language')->fetchAll();
        if (!empty($missionLanguages)) {
            foreach ($missionLanguages as $missionLanguage) {
                if ($missionLanguage['description'] === null) {
                    $data = null;
                } else {
                    $data = @json_decode($missionLanguage['description'],true);
                }

                if ($data !== null) {
                    $missionLanguageArray = json_decode($missionLanguage['description'],true);
                    $jsonData  = serialize($missionLanguageArray);

                    $pdo->prepare('
                        UPDATE mission_language
                        SET `description` = :description
                        WHERE mission_language_id = :id
                    ')
                        ->execute([
                            'description' => $jsonData,
                            'id' => $missionLanguage['mission_language_id']
                        ]);
                }
                //custom information
                if ($missionLanguage['custom_information'] === null) {
                    $customInformationData = null;
                } else {
                    $customInformationData = @json_decode($missionLanguage['custom_information'], true);
                }

                if ($customInformationData !== null) {
                    $missionLanguageArray = json_decode($missionLanguage['custom_information'], true);
                    $jsonData  = serialize($missionLanguageArray);

                    $pdo->prepare('
                        UPDATE mission_language
                        SET `custom_information` = :custom_information
                        WHERE mission_language_id = :id
                    ')
                        ->execute([
                            'custom_information' => $jsonData,
                            'id' => $missionLanguage['mission_language_id']
                        ]);
                }
            }
        }
    }
}
