<?php

namespace App\Helpers;

use App\Models\Tenant;
use App\Repositories\Tenant\TenantRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class Helpers
{
    /**
     * @var App\Repositories\Tenant\TenantRepository
     */
    private $tenantRepository;

    /**
     * Create a new Helpers class instance.
     *
     * @param App\Repositories\Tenant\TenantRepository $tenantRepository
     * @return void
     */
    public function __construct(
        TenantRepository $tenantRepository
    ) {
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * Pagination transform
     *
     * @param object $data
     * @param array $requestString
     * @param string $url
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginationTransform(object $data, array $requestString, string $url): LengthAwarePaginator
    {
        $paginatedData = new LengthAwarePaginator(
            $data->getCollection(),
            $data->total(),
            $data->perPage(),
            $data->currentPage(),
            [
                'path' => $url.'?'.http_build_query($requestString),
                'query' => [
                    'page' => $data->currentPage()
                ]
            ]
        );
        return $paginatedData;
    }

    /**
     * Get the tenant details, based on it's id
     *
     * @param  int  $tenantId
     * @return App\Models\Tenant $tenant
     */
    public function getTenantDetails(int $tenantId): Tenant
    {
        return $this->tenantRepository->find($tenantId);
    }
}
