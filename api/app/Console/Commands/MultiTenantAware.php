<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

abstract class MultiTenantAware extends Command
{
    /**
     * @var DB
     */
    private $db;

    /**
     * @var Collection
     */
    private $tenants;

    /**
     * Retrieve all tenants from the main database
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = app()->make('db');

        // Create connection to main DB
        Config::set('database.connections.tenant', array(
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ));
        $pdo = $this->db->connection('mysql')->getPdo();
        Config::set('database.default', 'mysql');

        // Get all tenants
        $this->tenants = collect(
            $pdo
                ->query('SELECT tenant_id, name from tenant WHERE deleted_at IS NULL')
                ->fetchAll()
        );
    }

    /**
     * No action is implemented directly in this method.
     * This will loop over all tenants,
     * switch the DB connection to this tenant,
     * then call the handleTenant method
     * for each one of them.
     * This handleTenant method must be implemented
     * by child classes to execute an operation
     * on one tenant.
     */
    final public function handle(): void
    {
        // Count tenants and start the progress bar
        $nbTenants = $this->tenants->count();
        $progressBar = $this->output->createProgressBar($nbTenants);
        $this->info("Total tenants : ${nbTenants}\n");
        $progressBar->start();

        // Switch schema for each tenant and execute the command
        $this->tenants->each(function ($tenant) use ($progressBar) {
            $tenantId = $tenant['tenant_id'];
            $tenantName = $tenant['name'];

            // Create connection to the tenant
            Config::set(
                'database.connections.tenant',
                [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST'),
                    'database' => 'ci_tenant_' . $tenantId,
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                ]
            );

            // Open connection to the tenant DB
            $this->db->purge('tenant');
            $this->db->setDefaultConnection('tenant');

            // Child classes should implement the command in the "handleTenant" method
            $this->handleTenant($tenantId, $tenantName);

            $progressBar->advance(1);
        });

        $progressBar->finish();
        $this->info("\n");
    }

    /**
     * A single operation per tenant can be implemented
     * by child classes in this method.
     *
     * @param int $tenantId
     * @param string $tenantName
     */
    protected abstract function handleTenant($tenantId, $tenantName): void;
}
