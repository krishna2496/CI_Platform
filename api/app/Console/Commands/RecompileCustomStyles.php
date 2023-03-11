<?php

namespace App\Console\Commands;

use App\Jobs\RecompileCustomStylesJob;
use App\Repositories\TenantOption\TenantOptionRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RecompileCustomStyles extends MultiTenantAware
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'styles:custom:recompile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Recompile every custom CSS for tenants that enabled it.";

    /**
     * @var TenantOptionRepository
     */
    private $tenantOptionRepository;

    public function __construct(TenantOptionRepository $tenantOptionRepository)
    {
        parent::__construct();
        $this->tenantOptionRepository = $tenantOptionRepository;
    }

    /**
     * @inheritDoc
     */
    protected function handleTenant($tenantId, $tenantName): void
    {
        try {
            $tenantOption = $this->tenantOptionRepository->getOptionWithCondition(['option_name' => 'custom_css']);
            $isCustomCssDisabled = $tenantOption === null || $tenantOption->option_value !== 1;

            // Skip this tenant if custom css disabled
            if ($isCustomCssDisabled) {
                return;
            }
        } catch (ModelNotFoundException $e) {
            /*
             * If we cannot find the option record,
             * we consider that custom CSS is not enabled
             * and we skip the recompilation for this tenant
             */
            return;
        }

        $this->info("\nRecompiling SCSS with latest styles for tenant ${tenantId} [${tenantName}]\n");
        dispatch(new RecompileCustomStylesJob($tenantName));
    }
}
