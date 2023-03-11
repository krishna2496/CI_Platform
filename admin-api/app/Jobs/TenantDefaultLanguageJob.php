<?php
namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use App\Helpers\EmailHelper;

class TenantDefaultLanguageJob extends Job
{
    /**
     * @var App\Models\Tenant
     */
    protected $tenant;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * Create a new job instance
     *
     * @param App\Tenant $tenant
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job
     *
     * @return void
     */
    public function handle()
    {
        // Add default English and French language for tenant - Testing purpose
        $defaultData = array(
            ['language_id' => 1, 'default' => '1'],
            ['language_id' => 2, 'default' => '0']
        );

        foreach ($defaultData as $key => $data) {
            $this->tenant->tenantLanguages()->create($data);
        }
    }
}
