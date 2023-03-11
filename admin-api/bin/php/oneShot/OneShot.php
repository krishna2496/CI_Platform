<?php

namespace optimy\console;

require_once(__DIR__.'/../../../bootstrap/app.php');


use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;


class OneShot extends Command
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->output = new ConsoleOutput;
        if (!in_array('start', get_class_methods(get_called_class()))) {
            throw new Exception('Implemention for the ::start() class method not found.');
        }
    }

    /**
     * Poorman's progress indicator. :(
     *
     * @param string|null  the character to be printed
     * @param int  times the character is to be printed
     *
     * @return void
     */
    protected function progress($char = null, $count = 1)
    {
        print(str_repeat($char ?: '·', $count));
    }

    /**
     * sprintf-like helper for outting strings with timestamp.
     * example: $this->writeLn('Today is %s %d, %d', 'Aug', 25, 2020);
     *
     * @param string|null  a template with placeholders for arguments
     * @param mixed  list of arguments to fill in the placeholders
     *
     * @return void
     */
    protected function writeLn($format=null, ...$args)
    {
        if ($format === null) {
            print(PHP_EOL);
        } else {
            $format = sprintf('[%s] %s', date('Y-m-d H:i:s'), $format);
            $this->info(vsprintf($format, $args));
        }
    }

    /**
     * Returns a formatted error message with timestamp.
     *
     * @param Exception $exception
     *
     * @return string
     */
    protected function formatError(Exception $exception): string
    {
        return sprintf(
            '[%s] Error "%s" on %s:%d',
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    /**
     * Debug helper method.
     *
     * @param mixed  list of arguments to dump
     *
     * @return string
     */
    protected function dump(...$args)
    {
        print(PHP_EOL);
        print_r($args);
        print(PHP_EOL);
    }

    /**
     * Prepares and connects to a tenant's database.
     *
     * @param int  the tenant ID number
     *
     * @return string
     */
    protected function connectTenantDb(int $tenantId)
    {
        DB::purge('tenant');
        Config::set(
            'database.connections.tenant',
            [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST'),
                'database'  => 'ci_tenant_'.$tenantId,
                'username'  => env('DB_USERNAME'),
                'password'  => env('DB_PASSWORD'),
            ]
        );
        DB::setDefaultConnection('tenant');
        DB::connection('tenant')->getPdo();
    }

    /**
     * Disconnects from a tenant's database going back to master.
     *
     * @return string
     */
    protected function disconnectTenantDb()
    {
        DB::disconnect('tenant');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');
    }

    /**
     * Table query builder helper with progress indicator.
     *
     * @return string
     */
    protected function getDbTable(string $tableName): Builder
    {
        $this->progress();
        return DB::table($tableName);
    }
}


/* boot the Application to initialize the IoC/DI container. */
$app->boot();
