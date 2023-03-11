<?php
namespace App\Transformations;

trait CountryTransformable
{
    /**
     * Country transformation.
     *
     * @param array $countryList
     * @param int $languageId
     * @param int $defaultLanguageId
     * @param bool|false $detailed
     * @return Array
     */
    public function countryTransform(array $countryList, int $languageId, int $defaultLanguageId, $detailed = false): Array
    {
        $countryData = array();
        foreach ($countryList as $key => $value) {
            $index = array_search($languageId, array_column($value['languages'], 'language_id'));

            $language = ($index === false) ? $defaultLanguageId : $languageId;
            $translationIndex = array_search($language, array_column($value['languages'], 'language_id'));
            if ($translationIndex !== false) {
                if ($detailed === true) {
                    $countryData[] = [
                        'id' => $value['country_id'],
                        'code' => $value['ISO'],
                        'name' => $value['languages'][$translationIndex]['name']
                    ];
                    continue;
                }
                $countryData[$value['languages'][$translationIndex]['country_id']] = $value['languages']
                [$translationIndex]['name'];
            }
        }
        return $countryData;
    }
}
