<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use DB;

class DatabaseHelper
{
    /**
     * Create connection with tenant database based on passed tenant id.
     *
     * @param  int $tenantId Identify it's database
     * @return boolean
     */
    public function connectWithTenantDatabase(int $tenantId)
    {
        DB::purge('tenant');
        // Set configuration options for the newly create tenant
        Config::set(
            'database.connections.tenant',
            array(
                'driver'    => 'mysql',
                'host'      => env('DB_HOST'),
                'database'  => 'ci_tenant_'.$tenantId,
                'username'  => env('DB_USERNAME'),
                'password'  => env('DB_PASSWORD'),
            )
        );
        // Set default connection with newly created database
        DB::setDefaultConnection('tenant');
        DB::connection('tenant')->getPdo();

        return true;
    }
}
