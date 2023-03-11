<?php
namespace App\Repositories\TenantLanguage;

use Illuminate\Http\Request;
use App\Models\TenantLanguage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TenantLanguageInterface
{
    /**
     * Get tenant language lists.
     *
     * @param Illuminate\Http\Request $request
     * @param int $tenantId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTenantLanguageList(Request $request, int $tenantId): LengthAwarePaginator;
   
    /**
     * Store/Update tenant language data.
     *
     * @param  array $tenantLanguageData
     * @return App\Models\TenantLanguage
     */
    public function storeOrUpdate(array $tenantLanguageData): TenantLanguage;
    
    /**
     * Delete tenant language data.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool;
    
    /**
     * Check default language settings.
     *
     * @param  int $tenantId
     * @param  int $languageId
     * @return bool
     */
    public function checkDefaultLanguageSettings(int $tenantId, int $languageId): bool;

    /**
     * Get language detail.
     *
     * @param  int  $id
     * @return App\Models\TenantLanguage
     */
    public function find(int $id): TenantLanguage;
}
