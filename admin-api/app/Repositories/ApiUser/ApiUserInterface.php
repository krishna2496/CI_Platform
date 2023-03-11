<?php
namespace App\Repositories\ApiUser;

use Illuminate\Http\Request;
use App\Models\ApiUser;
use Illuminate\Pagination\LengthAwarePaginator;

interface ApiUserInterface
{
    /**
     * Store a new resource.
     *
     * @param  int $id
     * @param  array $apiKeys
     * @return App\Models\ApiUser
     */
    public function store(int $id, array $apiKeys): ApiUser;

    /**
     * Update resource.
     *
     * @param  int $tenantId
     * @param  int $id
     * @param  string $apiSecret
     * @return App\Models\ApiUser
     */
    public function update(int $tenantId, int $id, string $apiSecret): ApiUser;

    /**
     * Listing of a all resources.
     *
     * @param  int $tenantId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function apiUserList(int $tenantId): LengthAwarePaginator;

    /**
     * Find a specified resource.
     *
     * @param  int $id
     * @return App\Models\ApiUser
     */
    public function findApiUser(int $id): ApiUser;

    /**
     * Delete a specified resource.
     * @param  int $tenantId
     * @param  int $id
     * @return bool
     */
    public function delete(int $tenantId, int $id): bool;
}
