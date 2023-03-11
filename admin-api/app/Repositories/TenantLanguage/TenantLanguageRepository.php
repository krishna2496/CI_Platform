<?php
namespace App\Repositories\TenantLanguage;

use App\Repositories\TenantLanguage\TenantLanguageInterface;
use Illuminate\Http\Request;
use App\Models\TenantLanguage;
use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Tenant;

class TenantLanguageRepository implements TenantLanguageInterface
{
    /**
     * @var App\Models\TenantLanguage
     */
    private $tenantLanguage;

    /**
     * @var App\Models\Language
     */
    private $language;
    
    /**
     * @var App\Models\Tenant
     */
    private $tenant;

    /**
     * Create a new tenant language repository instance.
     *
     * @param App\Models\TenantLanguage $tenantLanguage
     * @param App\Models\Language $language
     * @param App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(TenantLanguage $tenantLanguage, Language $language, Tenant $tenant)
    {
        $this->tenantLanguage = $tenantLanguage;
        $this->language = $language;
        $this->tenant = $tenant;
    }

    /**
     * Get tenant language lists.
     *
     * @param Illuminate\Http\Request $request
     * @param int $tenantId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTenantLanguageList(Request $request, int $tenantId): LengthAwarePaginator
    {
        // Check tenant is present in the system
        $tenantData = $this->tenant->findOrFail($tenantId);
     
        $tenantLanguageQuery = $this->tenantLanguage
        ->with(['language' => function ($query) {
            $query->select('language_id', 'name', 'code');
        }])->whereHas('language');
       
        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $tenantLanguageQuery->orderBy('created_at', $orderDirection);
        }
        $tenantLanguageData = $tenantLanguageQuery->where('tenant_id', $tenantId)->paginate($request->perPage);

        foreach ($tenantLanguageData as $value) {
            $value->name = $value->language->name;
            $value->code = $value->language->code;
            unset($value->language);
        }

        return $tenantLanguageData;
    }

    /**
     * Store/Update tenant language data.
     *
     * @param  array $tenantLanguageData
     * @return App\Models\TenantLanguage
     */
    public function storeOrUpdate(array $tenantLanguageData): TenantLanguage
    {
        $condition = array('tenant_id' => $tenantLanguageData['tenant_id'],
        'language_id' => $tenantLanguageData['language_id']);

        if ($tenantLanguageData['default'] == config('constants.language_status.ACTIVE')) {
            $this->tenantLanguage->resetDefaultTenantLanguage($tenantLanguageData['tenant_id']);
        }
        // Check for deleted data
        $languageTrashedData = $this->tenantLanguage->where($condition)
        ->onlyTrashed()->first();
        if ($languageTrashedData) {
            $this->tenantLanguage->where($condition)->restore();
            $languageTrashedData->update(['default' => $tenantLanguageData['default']]);

            return $languageTrashedData;
        } else {
            return $this->tenantLanguage->createOrUpdate($condition, $tenantLanguageData);
        }
    }

    /**
     * Delete tenant language data.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $tenantLanguageData =  $this->tenantLanguage->findOrFail($id);
        return $tenantLanguageData->delete();
    }

    /**
     * Check default language settings.
     *
     * @param  int $tenantId
     * @param  int $languageId
     * @return bool
     */
    public function checkDefaultLanguageSettings(int $tenantId, int $languageId): bool
    {
        $data = $this->tenantLanguage->where(
            [
            'tenant_id' => $tenantId,
            'language_id' =>$languageId,
            'default' => config('constants.language_status.ACTIVE')
            ]
        )->get();
        return ($data->count() > 0) ? true : false;
    }

    /**
     * Get language detail.
     *
     * @param  int  $id
     * @return App\Models\TenantLanguage
     */
    public function find(int $id): TenantLanguage
    {
        return $this->tenantLanguage->findOrFail($id);
    }
}
