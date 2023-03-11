<?php
namespace App\Repositories\TenantSetting;

use Illuminate\Http\Request;
use App\Models\TenantSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TenantSettingInterface
{
    /**
     * Get Settings lists
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAllSettings(): Collection;
}
