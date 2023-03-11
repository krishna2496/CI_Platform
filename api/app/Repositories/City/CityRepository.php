<?php
namespace App\Repositories\City;

use Illuminate\Http\Request;
use App\Repositories\City\CityInterface;
use App\Models\City;
use App\Models\CityLanguage;
use App\Models\Country;
use Illuminate\Support\Collection;
use App\Helpers\LanguageHelper;
use Illuminate\Pagination\LengthAwarePaginator;

class CityRepository implements CityInterface
{
    /**
     * @var App\Models\City
     */
    public $city;

    /**
     * @var App\Models\CityLanguage
     */
    public $cityLanguage;

    /**
     * @var App\Models\Country
     */
    public $country;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\City $city
     * @param App\Models\Country $country
     * @param App\Models\CityLanguage $cityLanguage
     * @param App\Helpers\LanguageHelper $languageHelper
     * @return void
     */
    public function __construct(
        City $city,
        Country $country,
        CityLanguage $cityLanguage,
        LanguageHelper $languageHelper
    ) {
        $this->city = $city;
        $this->country = $country;
        $this->cityLanguage = $cityLanguage;
        $this->languageHelper = $languageHelper;
    }

    /**
    * Get listing of all city.
    *
    * @param int $countryId
    * @return Illuminate\Support\Collection
    */
    public function cityList(int $countryId): Collection
    {
        $this->country->findOrFail($countryId);
        $cities = $this->city->with('languages')->where('country_id', $countryId)->get();

        $languages = $this->languageHelper->getLanguages();
        foreach ($cities as $key => $value) {
            foreach ($value->languages as $languageValue) {
                $languageData = $languages->where('language_id', $languageValue->language_id)->first();
                $languageValue->language_code = $languageData->code;
            }
        }
        return $cities;
    }

    /**
     * Get city data from cityId
     *
     * @param string $cityId
     * @param int $languageId
     * @return array
     */
    public function getCity(string $cityId, int $languageId) : array
    {
        $city = $this->city->with('languages')->whereIn("city_id", explode(",", $cityId))->get()->toArray();

        $cityData = [];
        if (!empty($city)) {
            foreach ($city as $key => $value) {
                $translation = $value['languages'];
                $cityData[$value['city_id']] =  $translation[0]['name'] ?? '';
                $translationkey = '';
                if (array_search($languageId, array_column($translation, 'language_id')) !== false) {
                    $translationkey = array_search($languageId, array_column($translation, 'language_id'));
                }

                if ($translationkey !== '') {
                    $cityData[$value['city_id']] = $translation[$translationkey]['name'];
                }
            }
        }
        return $cityData;
    }

    /**
     * Get city detail from city_id with all languages
     *
     * @param int  $cityId
     * @return array
     */
    public function getCityData(int $cityId) : array
    {
        $city = $this->city
            ->with('languages')
            ->where('city_id', $cityId)
            ->firstOrFail();

        $languages = $this->languageHelper->getLanguages();

        foreach ($city->languages as $lang) {
            $languageData = $languages->where('language_id', $lang->language_id)->first();
            $lang->language_code = $languageData->code;
        }

        return $city->toArray();
    }

    /**
     * Store city data
     *
     * @param Request $request
     * @return City
     */
    public function store(Request $request): City
    {
        $stateId = null;
        if ($request->state_id) {
            $stateId = $request->state_id;
        }
        return $this->city->create(['country_id' => $request->country_id,'state_id' => $stateId]);
    }

    /**
     * Store city language data
     *
     * @param array $cityData
     * @return void
     */
    public function storeCityLanguage(array $cityData)
    {
        $languages = $this->languageHelper->getLanguages();

        foreach ($cityData['translations'] as $key => $city) {
            $data = [];
            $languageId = $languages->where('code', $city['lang'])->first()->language_id;

            $data['city_id'] = $cityData['city_id'];
            $data['language_id'] = $languageId;
            $data['name'] = $city['name'];

            $this->cityLanguage->create($data);
        }
    }

    /**
     * Get listing of all city.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function cityLists(Request $request): LengthAwarePaginator
    {
        $cityQuery = $this->city->with(['languages']);

        if ($request->has('search') && $request->input('search') != '') {
            $cityQuery->wherehas('languages', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%');
            });
        }

        $cities = $cityQuery->paginate($request->perPage);

        $languages = $this->languageHelper->getLanguages();
        foreach ($cities as $key => $value) {
            foreach ($value->languages as $languageValue) {
                $languageData = $languages->where('language_id', $languageValue->language_id)->first();
                $languageValue->language_code = $languageData->code;
            }
        }
        return $cities;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->city->deleteCity($id);
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request $request
    * @param  int $id
    * @return App\Models\City
    */
    public function update(Request $request, int $id): City
    {
        // Set data for update record
        $cityDetail = array();
        if (isset($request['country_id'])) {
            $cityDetail['country_id'] = $request['country_id'];
        }

        if (isset($request['state_id'])) {
            $cityDetail['state_id'] = $request['state_id'];
        }

        // Update city
        $cityData = $this->city->findOrFail($id);
        $cityData->update($cityDetail);

        $languages = $this->languageHelper->getLanguages();

        if (isset($request['translations'])) {
            foreach ($request['translations'] as $value) {
                $language = $languages->where('code', $value['lang'])->first();
                $cityLanguageData = [
                    'city_id' => $id,
                    'name' => $value['name'],
                    'language_id' => $language->language_id
                ];

                $this->cityLanguage->createOrUpdateCityLanguage(['city_id' => $id,
                 'language_id' => $language->language_id], $cityLanguageData);
                unset($cityLanguageData);
            }
        }
        return $cityData;
    }

    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\City
     */
    public function find(int $id): City
    {
        return $this->city->findOrFail($id);
    }

    /**
     * It will check is city belongs to any mission or not
     *
     * @param int $id
     * @return bool
     */
    public function hasMission(int $id): bool
    {
        return $this->city->whereHas('mission')->whereCityId($id)->count() ? true : false;
    }

    /**
     * It will check is city belongs to any user or not
     *
     * @param int $id
     * @return bool
     */
    public function hasUser(int $id): bool
    {
        return $this->city->whereHas('user')->whereCityId($id)->count() ? true : false;
    }

    /**
    * Get listing of all city by country wise with pagination.
    *
    * @param Illuminate\Http\Request $request
    * @param int $countryId
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function getCityList(Request $request, int $countryId) : LengthAwarePaginator
    {
        $this->country->findOrFail($countryId);

        $cities = $this->city->with(['languages'])
            ->where('country_id', $countryId);

        if ($request->has('search') && $request->input('search') != '') {
            $cities->wherehas('languages', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%');
            });
        }

        $cities = $cities->paginate($request->perPage);

        $languages = $this->languageHelper->getLanguages();
        foreach ($cities as $key => $value) {
            foreach ($value->languages as $languageValue) {
                $languageData = $languages->where('language_id', $languageValue->language_id)->first();
                $languageValue->language_code = $languageData->code;
            }
        }
        return $cities;
    }


    /**
     * Search city with language and country restriction
     * @param  string $search
     * @param  int    $languageId
     * @param  int    $countryId
     * @return Object
     */
    public function searchCity(
        string $cityName,
        int $languageId = null,
        int $countryId = null
    ) {
        $city = $this->city
            ->join('city_language', 'city_language.city_id', '=', 'city.city_id')
            ->where('city_language.name', 'LIKE', '%'.$cityName.'%');

        if ($languageId) {
            $city->where('city_language.language_id', $languageId);
        }

        if ($countryId) {
            $city->where('city.country_id', $countryId);
        }

        return $city->take(1)
            ->first();
    }
}
