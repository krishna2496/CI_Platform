<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use DB;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Support\Collection;

class LanguageHelper
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var DB
     */
    private $db;

    /**
     * Create a new helper instance.
     *
     * @param App\Helpers\Helpers $helpers
     *
     * @return void
     */
    public function __construct(Helpers $helpers)
    {
        $this->helpers = $helpers;
        $this->db = app()->make('db');
    }

    /**
     * Get languages from `ci_admin` table.
     *
     * @return Illuminate\Support\Collection
     */
    public function getLanguages(): Collection
    {
        // Connect master database to get language details
        $this->helpers->switchDatabaseConnection('mysql');
        $languages = $this->db->table('language')->whereNull('deleted_at')->get();

        // Connect tenant database
        $this->helpers->switchDatabaseConnection('tenant');

        return $languages;
    }

    public function getLanguage(int $id)
    {
        $this->helpers->switchDatabaseConnection('mysql');
        $language = $this->db->table('language')
            ->select('language_id', 'code', 'name')
            ->where('language_id', $id)
            ->first();
        $this->helpers->switchDatabaseConnection('tenant');

        return $language;
    }

    /**
     * Get languages from `ci_admin` table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mix
     */
    public function getTenantLanguages(Request $request)
    {
        $tenant = $this->helpers->getTenantDetail($request);
        // Connect master database to get language details
        $tenantLanguages = $this->getTenantLanguagesByTenantId($tenant->tenant_id);

        return $tenantLanguages;
    }

    public function getTenantLanguagesByTenantId($tenantId)
    {
        $this->helpers->switchDatabaseConnection('mysql');
        $tenantLanguages = $this->db->table('tenant_language')
            ->select('language.language_id', 'language.code', 'language.name', 'tenant_language.default')
            ->leftJoin('language', 'language.language_id', '=', 'tenant_language.language_id')
            ->where('tenant_id', $tenantId)
            ->whereNull('tenant_language.deleted_at')
            ->whereNull('language.deleted_at')
            ->get();
        // Connect tenant database
        $this->helpers->switchDatabaseConnection('tenant');

        return $tenantLanguages;
    }

    /**
     * Check for valid language_id from `ci_admin` table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mix
     */
    public function validateLanguageId(Request $request)
    {
        $tenant = $this->helpers->getTenantDetail($request);
        // Connect master database to get language details
        $this->helpers->switchDatabaseConnection('mysql');

        $tenantLanguage = $this->db->table('tenant_language')
        ->where('tenant_id', $tenant->tenant_id)
        ->where('language_id', $request->language_id);

        // Connect tenant database
        $this->helpers->switchDatabaseConnection('tenant');

        return ($tenantLanguage->count() > 0) ? true : false;
    }

    /**
     * Get languages from `ci_admin` table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mix
     */
    public function getTenantLanguageList(Request $request)
    {
        $tenant = $this->helpers->getTenantDetail($request);
        // Connect master database to get language details
        $this->helpers->switchDatabaseConnection('mysql');

        $tenantLanguages = $this->db->table('tenant_language')
        ->select('language.language_id', 'language.code', 'language.name', 'tenant_language.default')
        ->leftJoin('language', 'language.language_id', '=', 'tenant_language.language_id')
        ->where('tenant_id', $tenant->tenant_id)
        ->where('tenant_language.deleted_at', null)
        ->where('language.deleted_at', null)
        ->pluck('language.name', 'language.language_id');

        // Connect tenant database
        $this->helpers->switchDatabaseConnection('tenant');

        return $tenantLanguages;
    }

    /**
     * Get languages code from `ci_admin` table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Illuminate\Support\Collection
     */
    public function getTenantLanguageCodeList(Request $request): Collection
    {
        $tenant = $this->helpers->getTenantDetail($request);
        // Connect master database to get language details
        $this->helpers->switchDatabaseConnection('mysql');

        $tenantLanguagesCodes = $this->db->table('tenant_language')
        ->select('language.language_id', 'language.code', 'language.name', 'tenant_language.default')
        ->leftJoin('language', 'language.language_id', '=', 'tenant_language.language_id')
        ->where('tenant_id', $tenant->tenant_id)
        ->whereNull('tenant_language.deleted_at')
        ->pluck('language.code', 'language.language_id');
        // Connect tenant database
        $this->helpers->switchDatabaseConnection('tenant');

        return $tenantLanguagesCodes;
    }

    /**
     * Get language id from request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return int
     */
    public function getLanguageId(Request $request): int
    {
        $languages = $this->getTenantLanguages($request);

        return $languages->where('code', config('app.locale'))->first()->language_id;
    }

    /**
     * Get language details from request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Object|null
     */
    public function getLanguageDetails(Request $request): ?Object
    {
        $languages = $this->getTenantLanguages($request);
        $languageCode = ($request->hasHeader('X-localization')) ?
        $request->header('X-localization') : $this->getDefaultTenantLanguage($request);

        $language = $languages->where('code', $languageCode)->first();

        return (!is_null($language)) ? $language : $this->getDefaultTenantLanguage($request);
    }

    /**
     * Get language id from request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Object
     */
    public function getDefaultTenantLanguage(Request $request): Object
    {
        $languages = $this->getTenantLanguages($request);

        return $languages->where('default', 1)->first();
    }

    /**
     * Get language details for localization.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Object
     */
    public function checkTenantLanguage(Request $request): Object
    {
        // Get tenant name from front user's request
        if (is_null($request->header('php-auth-user')) || $request->header('php-auth-user') === '') {
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
        } else { // Get tenant name from front admin's request
            $tenantDetails = DB::table('api_user')
            ->leftJoin('tenant', 'tenant.tenant_id', '=', 'api_user.tenant_id')
            ->where('api_key', base64_encode($request->header('php-auth-user')))
            ->where('api_user.status', '1')
            ->where('tenant.status', '1')
            ->whereNull('api_user.deleted_at')
            ->whereNull('tenant.deleted_at')
            ->first();
            $tenantName = $tenantDetails->name;
        }

        // Get tenant details from tenant name
        $tenant = DB::table('tenant')->where('name', $tenantName)->first();

        // Connect master database to get language details
        $tenantLanguagesQuery = DB::table('tenant_language')
        ->select('language.language_id', 'language.code', 'language.name', 'tenant_language.default')
        ->leftJoin('language', 'language.language_id', '=', 'tenant_language.language_id')
        ->where('tenant_id', $tenant->tenant_id)
        ->get();

        $language = $tenantLanguagesQuery->where('code', config('app.locale'))->first();

        // If localization language not found then use tenant default language
        if (is_null($language)) {
            $language = $tenantLanguagesQuery->where('default', 1)->first();
        }

        return $language;
    }

    /**
     * Search for the tenant language using language code.
     *
     * @param Request $request
     * @param string  $languageCode
     *
     * @return Array
     */
    public function getTenantLanguageByCode(Request $request, string $languageCode)
    {
        $tenantLanguages = $this->getTenantLanguages($request);

        return $tenantLanguages->where('code', $languageCode)
            ->first();
    }

    /**
     * Check language code is valid for tenant.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $request
     *
     * @return Object
     */
    public function isValidTenantLanguageCode(Request $request, string $languageCode)
    {
        $tenantLanguageCodes = $this->getTenantLanguageCodeList($request);

        return in_array($languageCode, $tenantLanguageCodes->toArray());
    }

    public function isValidAdminLanguageCode(string $languageCode): bool
    {
        // Connect master database to get language details
        $this->helpers->switchDatabaseConnection('mysql');
        $language = $this->db->table('language')
            ->where('code', $languageCode)
            ->whereNull('deleted_at')
            ->first();

        // Connect tenant database
        $this->helpers->switchDatabaseConnection('tenant');

        return $language !== null;
    }
}
