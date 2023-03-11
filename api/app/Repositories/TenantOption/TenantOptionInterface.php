<?php
namespace App\Repositories\TenantOption;

use Illuminate\Http\Request;
use App\Models\TenantOption;
use Illuminate\Database\Eloquent\Collection;

interface TenantOptionInterface
{
    /**
     * Update style settings.
     *
     * @param  Illuminate\Http\Request $request
     * @return bool
     */
    public function updateStyleSettings(Request $request): bool;

    /**
     * Store tenant option data
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getOptions(): Collection;

    /**
    * Get a listing of resource.
    *
    * @param array $conditions
    * @return null|App\Models\TenantOption
    */
    public function getOptionWithCondition(array $conditions = []): ?TenantOption;

    /**
     * Create new option
     *
     * @param array $option
     * @return App\Models\TenantOption
     */
    public function store(array $option): TenantOption;

    /**
     * Select by option name
     *
     * @param String $data
     * @return Illuminate\Support\Collection
     */
    public function getOptionValue(string $data);

    /**
     * Get option value by option name
     *
     * @param String $data
     * @return null|App\Models\TenantOption
     */
    public function getOptionValueFromOptionName(string $data): ?TenantOption;
}
