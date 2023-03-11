<?php
namespace App\Repositories\TenantActivatedSetting;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TenantActivatedSetting;

interface TenantActivatedSettingInterface
{
    /**
     * Create new activated settings
     *
     * @param array $data
     * @return bool
     */
    public function store(array $data): bool;

    /**
     * Get fetch all activated tenant settings
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getAllTenantActivatedSetting(Request $request): array;
}
