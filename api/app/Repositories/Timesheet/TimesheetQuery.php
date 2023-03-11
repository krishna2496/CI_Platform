<?php

namespace App\Repositories\Timesheet;

use App\Helpers\LanguageHelper;
use App\Models\Timesheet;
use App\Repositories\Core\QueryableInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimesheetQuery implements QueryableInterface
{
    private const FILTER_MISSION_THEMES = 'missionThemes';
    private const FILTER_APPLICATION_DATE = 'applicationDate';
    private const FILTER_MISSION_STATUSES = 'customMissionStatus';
    private const FILTER_APPROVAL_STATUS = 'timesheetStatus';
    private const FILTER_MISSION_COUNTRIES = 'missionCountries';
    private const FILTER_MISSION_CITIES = 'missionCities';
    private const FILTER_TIMESHEET_IDS = 'timesheetIds';
    private const FILTER_TYPE = 'type';

    private const ALLOWED_SORTABLE_FIELDS = [
        'appliedDate' => 'date_volunteered',
        'applicant' => 'user.last_name',
        'reviewedHours' => 'time',
        'note' => 'notes',
        'appliedDay' => 'day_volunteered',
        'applicantEmailAddress' => 'user.email',
        'missionCountryCode' => 'country_language_name',
        'approvalStatus' => 'status',
        'missionCityId' => 'city_language_name',
        'appliedTo' => 'mission_language_title',
        'reviewedObjective' => 'action',
        'notes' => 'notes',
        'applicantFirstName' => 'user.first_name',
        'applicantLastName' => 'user.last_name',
    ];

    private const ALLOWED_SORTING_DIR = ['ASC', 'DESC'];

    /**
     * @var string
     */
    private $missionType;

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
        $defaultLanguage = $tenantLanguages->filter(function ($language) {
            return $language->default === '1';
        })->first();
        $defaultLanguageId = (is_null($defaultLanguage)) ? 1 : $defaultLanguage->language_id;

        $hasMissionFilters = isset($filters[self::FILTER_MISSION_THEMES])
            || isset($filters[self::FILTER_MISSION_COUNTRIES])
            || isset($filters[self::FILTER_MISSION_CITIES])
            || isset($filters[self::FILTER_MISSION_STATUSES]);

        $languageId = $this->getFilteringLanguage($filters, $tenantLanguages);
        $query = Timesheet::query();
        $timesheets = $query
            ->select(DB::raw("
                timesheet.*,
                COALESCE(mission_language.title, mission_language_fallback.title) AS mission_language_title,
                COALESCE(country_language.name, country_language_fallback.name) AS country_language_name,
                COALESCE(city_language.name, city_language_fallback.name) AS city_language_name
            "))
            ->join('user', 'user.user_id', '=', 'timesheet.user_id')
            ->join('mission', 'mission.mission_id', '=', 'timesheet.mission_id')
            ->leftJoin('mission_language', function ($join) use ($languageId) {
                $join->on('mission_language.mission_id', '=', 'timesheet.mission_id')
                    ->where('mission_language.language_id', '=', $languageId);
            })
            ->leftJoin('mission_language AS mission_language_fallback', function ($join) use ($defaultLanguageId) {
                $join->on('mission_language_fallback.mission_id', '=', 'mission.mission_id')
                    ->where('mission_language_fallback.language_id', '=', $defaultLanguageId);
            })
            ->leftJoin('country_language', function ($join) use ($languageId) {
                $join->on('country_language.country_id', '=', 'mission.country_id')
                    ->where('country_language.language_id', '=', $languageId);
            })
            ->leftJoin('country_language AS country_language_fallback', function ($join) use ($defaultLanguageId) {
                $join->on('country_language_fallback.country_id', '=', 'mission.country_id')
                    ->where('country_language_fallback.language_id', '=', $defaultLanguageId);
            })
            ->leftJoin('city_language', function ($join) use ($languageId) {
                $join->on('city_language.city_id', '=', 'mission.city_id')
                    ->where('city_language.language_id', '=', $languageId);
            })
            ->leftJoin('city_language AS city_language_fallback', function ($join) use ($defaultLanguageId) {
                $join->on('city_language_fallback.city_id', '=', 'mission.city_id')
                    ->where('city_language_fallback.language_id', '=', $defaultLanguageId);
            })
            ->whereNotIn('timesheet.status', [config('constants.timesheet_status.PENDING')])
            ->whereHas('mission', function ($query) {
                $query->whereIn(
                    'publication_status',
                    [config("constants.publication_status")["APPROVED"], config("constants.publication_status")["PUBLISHED_FOR_APPLYING"]]
                );
            })
            ->whereHas('mission.missionApplication', function ($query) {
                $query->whereIn('approval_status', [config("constants.application_status")["AUTOMATICALLY_APPROVED"]]);
            })
            ->with([
                'user:user_id,first_name,last_name,avatar,email',
                'user.skills.skill:skill_id',
                'mission.missionLanguage',
                'mission.goalMission',
                'mission.missionSkill',
                'mission.country.languages',
                'mission.city.languages',
                'timesheetDocument',
            ])
            // Filter by application start date
            ->when(isset($filters[self::FILTER_APPLICATION_DATE]['from']), function ($query) use ($filters) {
                $query->where('date_volunteered', '>=', $filters[self::FILTER_APPLICATION_DATE]['from']);
            })
            // Filter by timesheet ids
            ->when(isset($filters[self::FILTER_TIMESHEET_IDS]), function ($query) use ($filters) {
                $query->whereIn('timesheet_id', $filters[self::FILTER_TIMESHEET_IDS]);
            })
            // Filter by application end date
            ->when(isset($filters[self::FILTER_APPLICATION_DATE]['to']), function ($query) use ($filters) {
                $query->where('date_volunteered', '<=', $filters[self::FILTER_APPLICATION_DATE]['to']);
            })
            // Filter by timesheet status
            ->when(isset($filters[self::FILTER_APPROVAL_STATUS]), function ($query) use ($filters) {
                $query->whereIn(
                    'timesheet.status',
                    collect($filters[self::FILTER_APPROVAL_STATUS])->map(function ($val) {
                        return strtoupper($val);
                    })
                );
            })
            ->when($hasMissionFilters, function ($query) use ($filters) {
                $query->whereHas('mission', function ($query) use ($filters) {
                    // Filter by mission theme
                    $query->when(isset($filters[self::FILTER_MISSION_THEMES]), function ($query) use ($filters) {
                        $query->whereIn('theme_id', $filters[self::FILTER_MISSION_THEMES]);
                    });
                    // Filter by mission country
                    $query->when(isset($filters[self::FILTER_MISSION_COUNTRIES]), function ($query) use ($filters) {
                        $query->whereIn('country_id', $filters[self::FILTER_MISSION_COUNTRIES]);
                    });
                    // Filter by mission city
                    $query->when(isset($filters[self::FILTER_MISSION_CITIES]), function ($query) use ($filters) {
                        $query->whereIn('city_id', $filters[self::FILTER_MISSION_CITIES]);
                    });
                    // Filter by mission Status
                    $query->when(isset($filters[self::FILTER_MISSION_STATUSES]), function ($query) use ($filters) {
                        collect($filters[self::FILTER_MISSION_STATUSES])
                            ->map(function ($val) use ($query, &$countFilterMissionStatus) {
                                if ($val === 'active') {
                                    $countFilterMissionStatus++;
                                    return $query->whereIn('publication_status', [
                                        config("constants.publication_status")["PUBLISHED_FOR_APPLYING"],
                                        config("constants.publication_status")["APPROVED"]
                                    ], $countFilterMissionStatus > 1 ? 'or' : 'and');
                                } else {
                                    $countFilterMissionStatus++;
                                    return $query->whereIn('publication_status', [
                                        config("constants.publication_status")["UNPUBLISHED"],
                                        config("constants.publication_status")["DRAFT"]
                                    ], $countFilterMissionStatus > 1 ? 'or' : 'and');
                                }
                            });
                    });
                });
            })
            ->whereHas('mission', function ($query) use ($filters) {
                $query->when(isset($filters[self::FILTER_TYPE]), function ($query) use ($filters) {
                    $this->missionType = $filters[self::FILTER_TYPE] === 'goal' ? config('constants.mission_type.GOAL') : config('constants.mission_type.TIME');
                    $query->where('mission_type', '=', "$this->missionType");
                });
            })
            // Search
            ->when(!empty($search), function ($query) use ($search, $filters, $andSearch) {
                $searchCallback = function ($query) use ($search, $filters) {
                    $query
                        ->whereNotIn('timesheet.status', [config('constants.timesheet_status.PENDING')])
                        ->where(function ($query) use ($search, $filters) {
                            $query->where('timesheet.status', 'like', "${search}%")
                                ->orWhere('timesheet.time', 'like', "${search}%")
                                ->orWhere('timesheet.action', 'like', "${search}%")
                                ->orWhere('timesheet.notes', 'like', "${search}%")
                                ->orWhere('timesheet.day_volunteered', 'like', "${search}%")
                                ->orWhere('timesheet.date_volunteered', 'like', "${search}%")
                                ->orwhereHas('timesheetDocument', function ($query) use ($search) {
                                    $query
                                        ->where('document_name', 'like', "${search}%");
                                })
                                ->orwhereHas('user', function ($query) use ($search) {
                                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["${search}"])
                                        ->orWhere('email', 'like', "${search}%")
                                        ->orWhere('first_name', 'like', "${search}%")
                                        ->orWhere('last_name', 'like', "${search}%");
                                })
                                ->orwhere('mission_language.title', 'like', "${search}%")
                                ->orwhere('mission_language.objective', 'like', "${search}%")
                                ->orwhere(function ($query) use ($search) {
                                    $query
                                        ->whereNull('mission_language.title')
                                        ->whereNull('mission_language.objective')
                                        ->where(function ($query) use ($search) {
                                            $query
                                                ->where('mission_language_fallback.title', 'like', "${search}%")
                                                ->orWhere('mission_language_fallback.objective', 'like', "${search}%");
                                        });
                                })
                                ->orwhereHas('mission.goalMission', function ($query) use ($search) {
                                    $query
                                        ->where('goal_objective', 'like', "${search}%");
                                })
                                ->orwhere('city_language.name', 'like', "${search}%")
                                ->orwhere(function ($query) use ($search) {
                                    $query
                                        ->whereNull('city_language.name')
                                        ->where('city_language_fallback.name', 'like', "${search}%");
                                })
                                ->orwhere('country_language.name', 'like', "${search}%")
                                ->orwhere(function ($query) use ($search) {
                                    $query
                                        ->whereNull('country_language.name')
                                        ->where('country_language_fallback.name', 'like', "${search}%");
                                });
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
            ->whereHas('mission', function ($query) use ($filters) {
                $query->when(isset($filters[self::FILTER_TYPE]), function ($query) {
                    $query->where('mission_type', '=', "$this->missionType");
                });
            })
            // Ordering
            ->when($order, function ($query) use ($order) {
                $query->orderBy($order['orderBy'], $order['orderDir']);
            })
            // Pagination
            ->paginate($limit['limit'], '*', 'page', 1 + ceil($limit['offset'] / $limit['limit']));

        $this->addCityCountryLanguageCode($timesheets);

        return $timesheets;
    }

    /**
     * Add the property 'language_code' in the 'translations' property of the mission city and country.
     *
     * @param LengthAwarePaginator $timesheets

     */
    private function addCityCountryLanguageCode(LengthAwarePaginator $timesheets)
    {
        $ciLanguages = $this->languageHelper->getLanguages();
        // Setting the language_code property
        foreach ($timesheets as $timesheet) {
            // Adding property for country
            foreach ($timesheet->mission->country->languages as $countryLanguage) {
                $ciLanguage = $ciLanguages->where('language_id', $countryLanguage->language_id)->first();
                $countryLanguage->language_code = $ciLanguage->code;
            }

            // Adding property for city
            foreach ($timesheet->mission->city->languages as $cityLanguage) {
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
        $defaultLanguageId = $tenantLanguages->filter(function ($language) use ($filters) {
            return $language->default === '1';
        })->first()->language_id;

        if (!$hasLanguageFilter) {
            return $defaultLanguageId;
        }

        $language = $tenantLanguages->filter(function ($language) use ($filters) {
            return $language->code === $filters['language'];
        })->first();

        if (is_null($language)) {
            return $defaultLanguageId;
        }

        return $language->language_id;
    }

    /**
     * @param $order
     * @return mixed
     */
    private function getOrder($order)
    {
        if (array_key_exists('orderBy', $order)) {
            if (array_key_exists($order['orderBy'], self::ALLOWED_SORTABLE_FIELDS)) {
                $order['orderBy'] = self::ALLOWED_SORTABLE_FIELDS[$order['orderBy']];
            } else {
                // Default to application date
                $order['orderBy'] = self::ALLOWED_SORTABLE_FIELDS['appliedDate'];
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
