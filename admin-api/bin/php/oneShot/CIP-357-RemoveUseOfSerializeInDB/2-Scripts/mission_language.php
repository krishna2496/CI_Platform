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
                    $data = false;
                } else {
                    $data = @unserialize($missionLanguage['description']);
                }

                if ($data !== false) {
                    $missionLanguageArray = unserialize($missionLanguage['description']);
                    $jsonData  = json_encode($missionLanguageArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    $pdo->prepare('
                        UPDATE mission_language
                        SET `description` = :description
                        WHERE mission_language_id = :id
                    ')
                        ->execute([
                            'description' => $jsonData,
                            'id' => $missionLanguage['mission_language_id']
                        ]);
                } else {
                    if ($missionLanguage['description'] !== null) {
                        var_dump(
                            'Needs manual verification for following context: ' . json_encode(
                                [
                                    'tenantId' => $tenantId,
                                    'table' => 'missionLanguage',
                                    'column' => 'description',
                                    'id' => $missionLanguage['mission_language_id']
                                ])
                        );
                    }
                }

                //custom information
                if ($missionLanguage['custom_information'] === null) {
                    $customInformationData = false;
                } else {
                    $customInformationData = @unserialize($missionLanguage['custom_information']);
                }

                if ($customInformationData !== false) {
                    $missionLanguageArray = unserialize($missionLanguage['custom_information']);
                    $jsonData  = json_encode($missionLanguageArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    $pdo->prepare('
                        UPDATE mission_language
                        SET `custom_information` = :custom_information
                        WHERE mission_language_id = :id
                    ')
                        ->execute([
                            'custom_information' => $jsonData,
                            'id' => $missionLanguage['mission_language_id']
                        ]);
                } else {
                    if ($missionLanguage['custom_information'] !== null) {
                        var_dump(
                            'Needs manual verification for following context: ' . json_encode(
                                [
                                    'tenantId' => $tenantId,
                                    'table' => 'missionLanguage',
                                    'column' => 'custom_information',
                                    'id' => $missionLanguage['mission_language_id']
                                ])
                        );
                    }
                }
            }
        }
    }
}
