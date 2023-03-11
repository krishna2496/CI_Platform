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

// Update Custom styles
$pdo
    ->prepare('
        UPDATE tenant_option
        SET `option_value` = :option_value
        WHERE tenant_option_id = :id
    ')
    ->execute([
        'option_value' => 'https://optimy-ci.s3.eu-central-1.amazonaws.com/ci.prod.optimy.net/assets/css/style.css',
        'id' => 1
    ]);

// Update Custom logo
$pdo
    ->prepare('
        UPDATE tenant_option
        SET `option_value` = :option_value
        WHERE tenant_option_id = :id
    ')
    ->execute([
        'option_value' => 'https://optimy-ci.s3.eu-central-1.amazonaws.com/ci.prod.optimy.net/assets/images/logo.png',
        'id' => 2
    ]);
