<?php

namespace App\Services;

use App\Models\TenantOption;
use App\Repositories\TenantOption\TenantOptionRepository;
use Illuminate\Support\Collection;

class TenantOptionService
{
    /**
     * @var  TenantOptionRepository
     */
    private $tenantOptionRepository;

    /**
     * @return  TenantOptionRepository
     *
     * @codeCoverageIgnore
     */
    public function __construct(TenantOptionRepository $tenantOptionRepository)
    {
        $this->tenantOptionRepository = $tenantOptionRepository;
    }

    /**
     * Get option value by option name.
     *
     * @param  String
     *
     * @return  TenantOption|null
     */
    public function getOptionValueFromOptionName(string $name): ?TenantOption
    {
        return $this->tenantOptionRepository->getOptionValueFromOptionName($name);
    }
}
