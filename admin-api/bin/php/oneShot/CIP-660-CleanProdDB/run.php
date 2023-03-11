<?php
require_once('bootstrap/app.php');

$db = app()->make('db');
$pdo = $db->connection('mysql')->getPdo();

$pdo->exec('
DROP DATABASE IF EXISTS ci_tenant_1;
DROP DATABASE IF EXISTS ci_tenant_2;
DROP DATABASE IF EXISTS ci_tenant_3;
DROP DATABASE IF EXISTS ci_tenant_4;
DROP DATABASE IF EXISTS ci_tenant_5;
DROP DATABASE IF EXISTS ci_tenant_6;
DROP DATABASE IF EXISTS ci_tenant_10;
DROP DATABASE IF EXISTS ci_tenant_11;
');
