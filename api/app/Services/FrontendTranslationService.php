<?php

namespace App\Services;

use App\Helpers\S3Helper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class FrontendTranslationService
{
    /**
     * @var S3Helper
     */
    private $s3Helper;

    /**
     * FrontendTranslationService constructor.
     */
    public function __construct(S3Helper $s3Helper)
    {
        $this->s3Helper = $s3Helper;
    }

    /**
     * @param string $tenantName
     * @param string $isoCode
     * @return string
     */
    private function getCacheKey($tenantName, $isoCode)
    {
        return "${tenantName}/languages/${isoCode}";
    }

    /**
     * @param string $tenantName
     * @param string $isoCode
     * @return Collection
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getCustomTranslationsForLanguage(string $tenantName, string $isoCode)
    {
        $cachedTranslationsKey = $this->getCacheKey($tenantName, $isoCode);
        $cachedTranslations = Cache::get($cachedTranslationsKey);
        if ($cachedTranslations !== null && config('app.env') !== 'local') {
            return $cachedTranslations;
        }

        // Retrieve the generic translations
        $translations = $this->getGenericTranslationsForLanguage($tenantName, $isoCode);

        /*
         * Check for any custom translation.
         * If no don't find any, we can
         * return the default ones.
         */
        $cdnStorage = Storage::disk('s3');
        $customTranslationsPath = $this->s3Helper->getCustomLanguageFilePath($tenantName, $isoCode);
        if (!$cdnStorage->exists($customTranslationsPath)) {
            Cache::forever($cachedTranslationsKey, $translations);
            return $translations;
        }

        // Otherwise we'll download and merge the custom translations with the default ones
        $customTranslationsJson = $cdnStorage->get($customTranslationsPath);
        $customTranslations = collect(json_decode($customTranslationsJson, true));
        $mergedTranslations = collect();
        $translations->each(function ($translationsGroup, $translationsGroupName) use ($customTranslations, $mergedTranslations) {
            $mergedTranslationsGroup = collect($translationsGroup);

            if ($customTranslations->keys()->contains($translationsGroupName)) {
                $mergedTranslationsGroup = $mergedTranslationsGroup->merge($customTranslations->get($translationsGroupName));
            }

            $mergedTranslations->put($translationsGroupName, $mergedTranslationsGroup);
        });

        // Write these translations to disk cache
        Cache::forever($cachedTranslationsKey, $mergedTranslations);

        return $mergedTranslations;
    }

    /**
     * Return the frontend translations without
     * the customisations introduced
     * by the client
     *
     * @param $tenantName
     * @param $isoCode
     */
    public function getGenericTranslationsForLanguage($tenantName, $isoCode)
    {
        $defaultTranslations = Storage::disk('resources')->get("frontend/translations/${isoCode}.json");
        return collect(json_decode($defaultTranslations, true));
    }

    /**
     * @param string $tenantName
     * @param string $isoCode
     */
    public function clearCache($tenantName, $isoCode)
    {
        Cache::forget($this->getCacheKey($tenantName, $isoCode));
    }

    /**
     * Store JSON containing tenant's custom translations
     * for a specific language
     * in a file on S3
     *
     * @param string $tenantName
     * @param string $isoCode
     * @param string $translations
     */
    public function storeCustomTranslations($tenantName, $isoCode, $translations)
    {
        $cdnStorage = Storage::disk('s3');
        $documentName = "${isoCode}.json";
        $documentPath =  config('constants.AWS_S3_LANGUAGES_FOLDER_NAME') . '/' . $documentName;
        $cdnStorage->put($tenantName . '/' . $documentPath, $translations);
    }
}
