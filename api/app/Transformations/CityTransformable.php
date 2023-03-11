<?php
namespace App\Transformations;

trait CityTransformable
{
    /**
     * City transformation.
     *
     * @param array $cityList
     * @param int $languageId
     * @param int $defaultTenantlanguageId
     * @return Array
     */
    public function cityTransform(array $cityList, int $languageId, int $defaultTenantlanguage): Array
    {
        $cityData = array();
        foreach ($cityList as $value) {
            $index = array_search($languageId, array_column($value['languages'], 'language_id'));

            $language = ($index === false) ? $defaultTenantlanguage : $languageId;
            $translationIndex = array_search($language, array_column($value['languages'], 'language_id'));
            if ($translationIndex !== false) {
                $cityData[$value['languages'][$index]['city_id']] = $value['languages'][$translationIndex]['name'];
            }
        }
        return $cityData;
    }
}
