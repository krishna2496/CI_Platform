<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Tenant\TenantRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use App\Helpers\EmailHelper;
use App\Models\Tenant;
use DB;


class MigrationRollack extends Command
{

    /**
     * @var App\Helpers\EmailHelper
     */
    private $emailHelper;

    /**
     * @var App\Repositories\Tenant\TenantRepository
     */
    private $tenantRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant-migration:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "It will rollback migration changes from all tenant's database";

    /**
     * Create a new command instance.
     * @codeCoverageIgnore
     * 
     * @param  App\Repositories\Tenant\TenantRepository $tenantRepository    
     * @return void
     */
    public function __construct(TenantRepository $tenantRepository)
    {
        parent::__construct();
        $this->tenantRepository = $tenantRepository;
        $this->emailHelper = new EmailHelper();
    }

    /**
     * Execute the console command.
     * @codeCoverageIgnore
     * 
     * @return mixed
     */
    public function handle()
    {
        $tenants = $this->tenantRepository->getAllTenants();
        $bar = $this->output->createProgressBar($tenants->count());
        if ($tenants->count() > 0) {
            $this->info("Total tenants : ". $tenants->count());
            $this->info("\nIt is going to rollback migration changes\n");
            $bar->start();
            foreach ($tenants as $tenant) {
                // Create connection of tenant one by one
                if ($this->createConnection($tenant->tenant_id) !== 0) {
                    try {
                        // Run migration command to apply migration change
                        Artisan::call('migrate:rollback --path=database/migrations/tenant');
                    } catch (\Exception $e) {
                        $this->warn("\n \n Migration rollback change have some error for tenant :
                        $tenant->name (tenant id : $tenant->tenant_id)");
                        $this->error("\n\n".$e->getMessage());
                        // Failed then send mail to admin
                        $this->sendFailerMail($tenant, config('constants.migration_file_type.migration'));
                        continue;
                    }
                    $bar->advance();
                    // Disconnect database and connect with master DB
                    DB::disconnect('tenant');
                    DB::reconnect('mysql');
                }
            }
            $bar->finish();
            $this->info("\n \nAll rollback changes are done!");
        } else {
            $this->warn("No tenant found");
        }
    }

    /**
     * Send email notification to admin
     * @codeCoverageIgnore
     *
     * @param App\Models\Tenant $tenant
     * @param string $type
     * @return void
     */
    public function sendFailerMail(Tenant $tenant, string $type)
    {
        $message = "Seeder rollback filed for tenant : ". $tenant->name. '.';
        $params['subject'] = 'Error in migration rollback';

        $message .= "<br> Database name : ". "ci_tenant_". $tenant->tenant_id;

        $data = array(
            'message'=> $message,
            'tenant_name' => $tenant->name
        );

        $params['to'] = config('constants.ADMIN_EMAIL_ADDRESS'); //required
        $params['template'] = config('constants.EMAIL_TEMPLATE_FOLDER').'.'
        .config('constants.EMAIL_TEMPLATE_MIGRATION_NOTIFICATION'); //path to the email template

        $params['data'] = $data;

        $this->emailHelper->sendEmail($params);
    }

    /**
     * Create connection with tenant's database
     * @codeCoverageIgnore
     *
     * @param int $tenantId
     * @return int
     */
    public function createConnection(int $tenantId): int
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

        try {
            DB::connection('tenant')->getPdo();
        } catch (\Exception $exception) {
            return 0;
        }

        return $tenantId;
    }
}
