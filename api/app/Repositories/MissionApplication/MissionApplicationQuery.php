<?php

namespace App\Repositories\MissionApplication;

use App\Helpers\LanguageHelper;
use App\Models\MissionApplication;
use App\Repositories\Core\QueryableInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MissionApplicationQuery implements QueryableInterface
{
    private const FILTER_APPLICATION_IDS    = 'applicationIds';
    private const FILTER_APPLICATION_DATE   = 'applicationDate';
    private const FILTER_APPLICANT_SKILLS   = 'applicantSkills';
    private const FILTER_MISSION_SKILLS     = 'missionSkills';
    private const FILTER_MISSION_THEMES     = 'missionThemes';
    private const FILTER_MISSION_COUNTRIES  = 'missionCountries';
    private const FILTER_MISSION_CITIES     = 'missionCities';
    private const FILTER_MISSION_TYPES      = 'missionTypes';
    private const FILTER_MISSION_VIRTUAL    = 'isVirtual';
    private const FILTER_STATUS             = 'approvalStatuses';

    private const ALLOWED_SORTABLE_FIELDS = [
        'applicant' => 'user.last_name',
        'applicantLastName' => 'user.last_name',
        'applicantFirstName' => 'user.first_name',
        'applicantEmail' => 'user.email',
        'missionType' => 'mission.mission_type',
        'missionCountryCode' => 'country_language_name',
        'status' => 'mission_application.approval_status',
        'missionCityId' => 'city_language_name',
        'applicationDate' => 'mission_application.applied_at',
        'applicationSkills' => 'applicant_skills',
        'missionName' => 'mission_language_title',
    ];

    private const ALLOWED_SORTING_DIR = ['ASC', 'DESC'];

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    public function __construct(LanguageHelper $languageHelper)
    {
        $this->languageHelper = $languageHelper;
    }

    /**
     * @param array $parameters
     * @return LengthAwarePaginator
     */
    public function run($parameters = [])
    {
        $filters = $parameters['filters'];
        $search = $parameters['search'];
        $andSearch = $parameters['andSearch'];
        $order = $this->getOrder($parameters['order']);
        $limit = $this->getLimit($parameters['limit']);
        $tenantLanguages = $parameters['tenantLanguages'];

        $defaultLanguageId = $tenantLanguages->filter(function ($language) {
            return $language->default === '1';
        })->first()->language_id;

        $hasMissionFilters = isset($filters[self::FILTER_MISSION_THEMES])
            || isset($filters[self::FILTER_MISSION_COUNTRIES])
            || isset($filters[self::FILTER_MISSION_CITIES])
            || isset($filters[self::FILTER_MISSION_TYPES])
            || isset($filters[self::FILTER_MISSION_VIRTUAL]);

        $languageId = $this->getFilteringLanguage($filters, $tenantLanguages);

        $query = MissionApplication::query();
        $applications = $query
            ->select(DB::raw("
                mission_application.*,
                user.last_name,
                user.email,
                mission.mission_type,
                COALESCE(mission_language.title, mission_language_fallback.title) AS mission_language_title,
                COALESCE(city_language.name, city_language_fallback.name) AS city_language_name,
                COALESCE(country_language.name, country_language_fallback.name) AS country_language_name
            "))
            ->join('user', 'user.user_id', '=', 'mission_application.user_id')
            ->join('mission', 'mission.mission_id', '=', 'mission_application.mission_id')
            ->leftJoin('mission_language', function ($join) use ($languageId) {
                $join->on('mission_language.mission_id', '=', 'mission.mission_id')
                    ->where('mission_language.language_id', '=', $languageId);
            })
            ->leftJoin('mission_language AS mission_language_fallback', function ($join) use ($defaultLanguageId) {
                $join->on('mission_language_fallback.mission_id', '=', 'mission.mission_id')
                    ->where('mission_language_fallback.language_id', '=', $defaultLanguageId);
            })
            ->leftJoin('city_language', function($join) use ($languageId) {
                $join->on('city_language.city_id', '=', 'mission.city_id')
                    ->where('city_language.language_id', '=', $languageId);
            })
            ->leftJoin('city_language AS city_language_fallback', function($join) use ($defaultLanguageId) {
                $join->on('city_language_fallback.city_id', '=', 'mission.city_id')
                    ->where('city_language_fallback.language_id', '=', $defaultLanguageId);
            })
            ->leftJoin('country_language', function($join) use ($languageId) {
                $join->on('country_language.country_id', '=', 'mission.country_id')
                    ->where('country_language.language_id', '=', $languageId);
            })
            ->leftJoin('country_language AS country_language_fallback', function($join) use ($defaultLanguageId) {
                $join->on('country_language_fallback.country_id', '=', 'mission.country_id')
                    ->where('country_language_fallback.language_id', '=', $defaultLanguageId);
            })
            ->with([
                'user:user_id,first_name,last_name,avatar,email',
                'user.skills.skill:skill_id',
                'mission',
                'mission.missionLanguage',
                'mission.missionSkill',
                'mission.country.languages',
                'mission.city.languages',
                'mission.volunteeringAttribute'
            ])
            // Filter by Status
            ->when(isset($filters[self::FILTER_STATUS]), function($query) use ($filters) {
                $query->whereIn('approval_status', $filters[self::FILTER_STATUS]);
            })
            // Filter by application ID
            ->when(isset($filters[self::FILTER_APPLICATION_IDS]), function($query) use ($filters) {
                $query->whereIn('mission_application_id', $filters[self::FILTER_APPLICATION_IDS]);
            })
            // Filter by application start date
            ->when(isset($filters[self::FILTER_APPLICATION_DATE]['from']), function($query) use ($filters) {
                $query->where('applied_at', '>=', $filters[self::FILTER_APPLICATION_DATE]['from']);
            })
            // Filter by application end date
            ->when(isset($filters[self::FILTER_APPLICATION_DATE]['to']), function($query) use ($filters) {
                $query->where('applied_at', '<=', $filters[self::FILTER_APPLICATION_DATE]['to']);
            })
            ->when($hasMissionFilters, function($query) use ($filters) {
                $query->whereHas('mission', function($query) use ($filters) {
                    // Filter by mission theme
                    $query->when(isset($filters[self::FILTER_MISSION_THEMES]), function($query) use ($filters) {
                        $query->whereIn('theme_id', $filters[self::FILTER_MISSION_THEMES]);
                    });
                    // Filter by mission country
                    $query->when(isset($filters[self::FILTER_MISSION_COUNTRIES]), function($query) use ($filters) {
                        $query->whereIn('country_id', $filters[self::FILTER_MISSION_COUNTRIES]);
                    });
                    // Filter by mission city
                    $query->when(isset($filters[self::FILTER_MISSION_CITIES]), function($query) use ($filters) {
                        $query->whereIn('city_id', $filters[self::FILTER_MISSION_CITIES]);
                    });
                    // Filter by mission type
                    $query->when(isset($filters[self::FILTER_MISSION_TYPES]), function($query) use ($filters) {
                        $query->whereIn('mission_type', $filters[self::FILTER_MISSION_TYPES]);
                    });
                    // Filter by mission is_virtual
                    $query->when(isset($filters[self::FILTER_MISSION_VIRTUAL]) && $filters[self::FILTER_MISSION_VIRTUAL] !== "", function($query) use ($filters) {
                        $isVirtual = filter_var($filters[self::FILTER_MISSION_VIRTUAL], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        $value = $isVirtual ? '1' : '0';
                        if ($isVirtual === null) {
                            $value = $isVirtual;
                        }
                        $query->whereHas('volunteeringAttribute', function ($query) use ($value) {
                            $query->where('is_virtual', $value);
                        });
                    });
                });
            })
            // Filter by applicant skills
            ->when(isset($filters[self::FILTER_APPLICANT_SKILLS]), function($query) use ($filters) {
                $query->whereHas('user.skills', function($query) use ($filters) {
                    $query->whereIn('skill_id', $filters[self::FILTER_APPLICANT_SKILLS]);
                });
            })
            // Filter by mission skill
            ->when(isset($filters[self::FILTER_MISSION_SKILLS]), function($query) use ($filters) {
                $query->whereHas('mission.missionSkill', function($query) use ($filters) {
                    $query->whereIn('skill_id', $filters[self::FILTER_MISSION_SKILLS]);
                });
            })
            // Search
            ->when(!empty($search), function($query) use ($search, $filters, $languageId, $andSearch) {
                $searchCallback = function ($query) use ($search, $filters, $languageId) {
                    $query
                        ->whereHas('user', function($query) use ($search) {
                            $query
                                ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["${search}"])
                                ->orWhere('email', 'like', "${search}%")
                                ->orWhere('first_name', 'like', "${search}%")
                                ->orWhere('last_name', 'like', "${search}%");
                        })
                        ->orWhere('mission_language.title', 'like', "%${search}%")
                        ->orWhere(function ($query) use ($search) {
                            $query
                                ->whereNull('mission_language.title')
                                ->where('mission_language_fallback.title', 'like', "${search}%");
                        })
                        ->orWhere('city_language.name', 'like', "${search}%")
                        ->orWhere(function ($query) use ($search) {
                            $query
                                ->whereNull('city_language.name')
                                ->where('city_language_fallback.name', 'like', "${search}%");

                        })
                        ->orWhere('country_language.name', 'like', "${search}%")
                        ->orWhere(function ($query) use ($search) {
                            $query
                                ->whereNull('country_language.name')
                                ->where('country_language_fallback.name', 'like', "${search}%");
                        });
                };

                /* In the case we have the $andSearch set to false,
                 * the condition on the where can *not* be exclusive as we might lose valid results from
                 * previous filtering (in Optimy). We then need to use the OR condition for searchable fields.
                 */
                $andSearch
                    ? $query->where($searchCallback)
                    : $query->orWhere($searchCallback);
            })
            // Ordering
            ->when($order, function ($query) use ($order) {
                $query->orderBy($order['orderBy'], $order['orderDir']);
            })
            // Pagination
            ->paginate(
                $limit['limit'],
                '*',
                'page',
                1 + ceil($limit['offset'] / $limit['limit']));

        $this->addCityCountryLanguageCode($applications);

        return $applications;
    }

    /**
     * Add the property 'language_code' in the 'translations' property of the mission city and country.
     *
     * @param LengthAwarePaginator $applications
     */
    private function addCityCountryLanguageCode($applications)
    {
        $ciLanguages = $this->languageHelper->getLanguages();
        // Setting the language_code property
        foreach ($applications as $application) {
            // Adding property for country
            foreach ($application->mission->country->languages as $countryLanguage) {
                $ciLanguage = $ciLanguages->where('language_id', $countryLanguage->language_id)->first();
                $countryLanguage->language_code = $ciLanguage->code;
            }

            // Adding property for city
            foreach ($application->mission->city->languages as $cityLanguage) {
                $ciLanguage = $ciLanguages->where('language_id', $cityLanguage->language_id)->first();
                $cityLanguage->language_code = $ciLanguage->code;
            }
        }
    }

    /**
     * @param array $filters
     * @param Collection $tenantLanguages
     * @return int
     */
    private function getFilteringLanguage(array $filters, Collection $tenantLanguages): int
    {
        $hasLanguageFilter = array_key_exists('language', $filters);
        $defaultLanguageId = $tenantLanguages->filter(function ($language) use ($filters) { return $language->default == 1; })->first()->language_id;

        if (!$hasLanguageFilter) {
            return $defaultLanguageId;
        }

        $language = $tenantLanguages->filter(function ($language) use ($filters) { return $language->code === $filters['language']; })->first();

        if (is_null($language)) {
            return $defaultLanguageId;
        }

        return $language->language_id;
    }

    /**
     * @param array $order
     * @return array
     */
    private function getOrder(array $order): array
    {
        if (array_key_exists('orderBy', $order)) {
            if (array_key_exists($order['orderBy'], self::ALLOWED_SORTABLE_FIELDS)) {
                $order['orderBy'] = self::ALLOWED_SORTABLE_FIELDS[$order['orderBy']];
            } else {
                // Default to application date
                $order['orderBy'] = self::ALLOWED_SORTABLE_FIELDS['applicationDate'];
            }

            if (array_key_exists('orderDir', $order)) {
                if (!in_array($order['orderDir'], self::ALLOWED_SORTING_DIR)) {
                    // Default to ASC
                    $order['orderDir'] = self::ALLOWED_SORTING_DIR[0];
                }
            } else {

                // Default to ASC
                $order['orderDir'] = self::ALLOWED_SORTING_DIR[0];
            }
        }
        return $order;
    }

    /**
     * @param array $limit
     * @return array
     */
    private function getLimit(array $limit): array
    {
        if (!array_key_exists('limit', $limit)) {
            $limit['limit'] = config('constants.PER_PAGE_ALL'); // we get all the results
        }

        if (!array_key_exists('offset', $limit)) {
            $limit['offset'] = 0;
        }

        return $limit;
    }

}
