<?php
namespace App\Repositories\Tenant;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

interface TenantInterface
{
    /**
     * Store a new resource.
     *
     * @param  Illuminate\Http\Request $request
     * @return App\Models\Tenant
     */
    public function store(Request $request): Tenant;

    /**
     * Update resource.
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $id
     * @return App\Models\Tenant
     */
    public function update(Request $request, int $id): Tenant;


    /**
     * Listing of a all resources.
     *
     * @param  Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function tenantList(Request $request): LengthAwarePaginator;

    /**
     * Find a specified resource.
     *
     * @param  int $id
     * @return App\Models\Tenant
     */
    public function find(int $id): Tenant;

    /**
     * Delete a specified resource.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get pending tenant list to execute their background process
     * @param int $tenantId
     * @return null|Illuminate\Support\Collection
     */
    public function getPendingTenantsForProcess(int $tenantId = null);
}
