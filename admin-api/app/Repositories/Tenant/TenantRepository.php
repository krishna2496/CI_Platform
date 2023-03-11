<?php
namespace App\Repositories\Tenant;

use App\Repositories\Tenant\TenantInterface;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TenantRepository implements TenantInterface
{
    /**
     * @var App\Models\Tenant
     */
    public $tenant;

    /**
     * Create a new Tenant repository instance.
     *
     * @param  App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Get listing of tenants
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function tenantList(Request $request): LengthAwarePaginator
    {
        $tenantQuery = $this->tenant->with('tenantLanguages', 'tenantLanguages.language');

        if ($request->has('search')) {
            $tenantQuery->where('name', 'like', '%' . $request->input('search') . '%');
        }
        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $tenantQuery->orderBy('tenant_id', $orderDirection);
        }

        return $tenantQuery->paginate($request->perPage);
    }

    /**
     * Store a newly created resource in storage
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\Tenant $tenant
     */
    public function store(Request $request): Tenant
    {
        $tenant = $this->tenant->create($request->toArray());

        // ONLY FOR DEVELOPMENT MODE. (PLEASE REMOVE THIS CODE IN PRODUCTION MODE)
        /* if (env('APP_ENV')=='local') {
            $apiUserData['api_key'] = $tenant->name.'_api_key';
            $apiUserData['api_secret'] = $tenant->name.'_api_secret';
            // Insert api_user data into table
            $tenant->apiUsers()->create($apiUserData);
        } */
        // ONLY FOR DEVELOPMENT MODE END
        return $tenant;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return App\Models\Tenant $tenant
     */
    public function find(int $id): Tenant
    {
        return $this->tenant->findTenant($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->tenant->deleteTenant($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return App\Models\Tenant $tenant
     */
    public function update(Request $request, int $id): Tenant
    {
        $tenant = $this->tenant->findOrFail($id);
        $tenant->update($request->toArray());
        return $tenant;
    }

    /**
     * Get pending tenant list to execute their background process
     * @param int $tenantId
     * @return null|Illuminate\Support\Collection
     */
    public function getPendingTenantsForProcess(int $tenantId = null)
    {
        $query = $this->tenant->where(
            'background_process_status',
            config('constants.background_process_status.PENDING')
        );
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        return $query->get();
    }

    /**
     * Get pending tenant list to execute their background process
     * @codeCoverageIgnore
     * 
     * @param int $tenantId
     * @return null|Illuminate\Support\Collection
     */
    public function getAllTenants(): ?Collection
    {
        return $this->tenant->where('status', '1')->get();
    }
}
