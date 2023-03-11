<?php

require_once('bootstrap/app.php');

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
	echo 'Updating tenant ' . $tenant['tenant_id'] . "... \n";

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
	$connection = $db->connection('tenant');
	$pdo = $connection->getPdo();

	// Set default database
	\Illuminate\Support\Facades\Config::set('database.default', 'tenant');

	$activeCustomField = $pdo->query(
		"SELECT field_id FROM user_custom_field WHERE deleted_at IS NULL"
	)->fetchAll();

	try {
		$connection->beginTransaction();
		$order = 1;
		foreach ($activeCustomField as $customField) {

			$prepareQuery = $pdo->prepare(
				"UPDATE user_custom_field SET `order` = :order WHERE `field_id` = :field_id"
			);

			$query = $prepareQuery->execute([
				'order' => $order,
				'field_id' => $customField['field_id']
			]);

			echo 'Successfully updated ' . $query . " row.. \n";

			$order++;

		}
		$connection->commit();

		echo 'Done updating the order column of tenant ' . $tenant['tenant_id'] . "... \n";
	} catch (Exception $e) {
		$connection->rollback();
		echo 'Failed: ' . $e->getMessage() . " ... \n";
	}
}
