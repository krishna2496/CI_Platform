<?php
namespace App\Repositories\ApiUser;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\ApiUser\ApiUserInterface;
use Illuminate\Http\Request;
use App\Models\ApiUser;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiUserRepository implements ApiUserInterface
{
    /**
     * @var App\Models\ApiUser
     */
    public $apiUser;

    /**
     * Create a new ApiUser repository instance.
     *
     * @param  App\Models\ApiUser $apiUser
     * @return void
     */
    public function __construct(ApiUser $apiUser)
    {
        $this->apiUser = $apiUser;
    }

    /**
     * Get listing of tenants
     *
     * @param int $tenantId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function apiUserList(int $tenantId): LengthAwarePaginator
    {
        $apiUserQuery = $this->apiUser->select('api_user_id', 'tenant_id', 'api_key', 'status')
        ->where('tenant_id', $tenantId);
        return $apiUserQuery->paginate(config('constants.PER_PAGE_LIMIT'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return App\Models\ApiUser $apiUser
     */
    public function findApiUser(int $id): ApiUser
    {
        return $this->apiUser->select('api_user_id', 'tenant_id', 'api_key', 'status')->findOrFail($id);
    }

    /**
     * Create API user for tenant
     *
     * @param  int  $id
     * @param  array $apiKeys
     * @return App\Models\ApiUser $apiUser
     */
    public function store(int $id, array $apiKeys): ApiUser
    {
        $data['tenant_id'] = $id;
        $data['api_key'] = $apiKeys['api_key'];
        $data['api_secret'] = $apiKeys['api_secret'];
        return $this->apiUser->create($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $tenantId
     * @param  int  $id
     * @return bool
     */
    public function delete(int $tenantId, int $id): bool
    {
        $apiUser = $this->apiUser->select('api_user_id', 'tenant_id', 'api_key', 'status')
        ->where('tenant_id', $tenantId)
        ->where('api_user_id', $id)
        ->first();

        if ($apiUser) {
            return $apiUser->delete();
        } else {
            throw new ModelNotFoundException(
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND'),
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND')
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $tenantId
     * @param  int  $id
     * @param  string $apiSecret
     * @return App\Models\ApiUser $apiUser
     */
    public function update(int $tenantId, int $id, string $apiSecret): ApiUser
    {
        $apiUser = $this->apiUser->select('api_user_id', 'tenant_id', 'api_key', 'status')
        ->where('tenant_id', $tenantId)
        ->where('api_user_id', $id)
        ->first();

        if ($apiUser) {
            $data['api_secret'] = $apiSecret;
            $apiUser->update($data);
            return $apiUser;
        } else {
            throw new ModelNotFoundException(
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND'),
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND')
            );
        }
    }
}
