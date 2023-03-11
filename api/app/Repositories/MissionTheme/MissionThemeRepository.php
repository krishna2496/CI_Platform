<?php
namespace App\Repositories\MissionTheme;

use App\Repositories\MissionTheme\MissionThemeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MissionTheme;
use Illuminate\Support\Collection;
use \Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MissionThemeRepository implements MissionThemeInterface
{
    /**
     * @var App\Models\MissionTheme
     */
    public $missionTheme;

    /**
     * Create a new MissionTheme repository instance.
     *
     * @param  App\Models\MissionTheme $missionTheme
     * @return void
     */
    public function __construct(MissionTheme $missionTheme)
    {
        $this->missionTheme = $missionTheme;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $theme_id
     * @return Illuminate\Support\Collection
     */
    public function missionThemeList(Request $request, String $theme_id = ''): Collection
    {
        $themeQuery = $this->missionTheme->select('mission_theme_id', 'theme_name', 'translations');
        if ($theme_id !== '') {
            $themeQuery->whereIn("mission_theme_id", explode(",", $theme_id));
        }
        $theme = $themeQuery->get();
        return $theme;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function missionThemeDetails(Request $request): LengthAwarePaginator
    {
        $themeQuery = $this->missionTheme->select('theme_name', 'mission_theme_id', 'translations');
        if ($request->has('search')) {
            $themeQuery->where(function ($query) use ($request) {
                if ($request->has('language')) {
                    $query->where(DB::raw("lower(json_unquote(json_extract(translations, '$.".$request->get('language')."')))"), 'LIKE', strtolower( $request->input('search') ).'%');
                } else {
                    $query->whereNotNull(DB::raw("JSON_SEARCH(lower(translations),'one','".strtolower( $request->input('search') ).'%'."')"));
                    $query->orWhere(DB::raw("lower(theme_name)"), 'LIKE', strtolower( $request->input('search') ).'%');
                }
            });
        }
        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $themeQuery->orderBy('mission_theme_id', $orderDirection);
        }
        return $themeQuery->paginate($request->perPage);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $request
     * @return App\Models\MissionTheme
     */
    public function store(array $request): MissionTheme
    {
        return $this->missionTheme->create($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  array  $request
     * @param  int  $id
     * @return App\Models\MissionTheme
     */
    public function update(array $request, int $id): MissionTheme
    {
        $missionTheme = $this->missionTheme->findOrFail($id);
        $missionTheme->update($request);
        return $missionTheme;
    }

    /**
     * Find specified resource in storage.
     *
     * @param  int  $id
     * @return App\Models\MissionTheme
     */
    public function find(int $id): MissionTheme
    {
        return $this->missionTheme->findMissionTheme($id);
    }

    /**
     * Remove specified resource in storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->missionTheme->deleteMissionTheme($id);
    }

    /**
     * Get all theme history with total minutes logged, based on year and all years.
     *
     * @param int $year
     * @param int $userId
     * @return Illuminate\Support\Collection
     */
    public function getHoursPerTheme(int $year = null, int $userId): Collection
    {
        $queryBuilder = $this->missionTheme->select([
            'mission_theme.mission_theme_id',
            'mission_theme.theme_name',
            'mission_theme.translations',
            \DB::raw('sum(minute(time) + (hour(time)*60)) as total_minutes')
        ])
        ->leftjoin('mission', 'theme_id', 'mission_theme_id')
        ->leftjoin('timesheet', 'mission.mission_id', 'timesheet.mission_id')
        ->where('mission.mission_type', 'TIME');
        if (!empty($year)) {
            $queryBuilder = $queryBuilder->whereRaw(\DB::raw('year(timesheet.created_at) = "'.$year.'"'));
        }

        $statusArray = [
            config('constants.timesheet_status.AUTOMATICALLY_APPROVED'),
            config('constants.timesheet_status.APPROVED')
        ];
        $queryBuilder = $queryBuilder->where('mission.publication_status', 'APPROVED')
        ->where('timesheet.user_id', $userId)
        ->whereNotNull('mission.mission_id')
        ->whereIn('timesheet.status', $statusArray)
        ->whereNotNull('timesheet.timesheet_id')
        ->whereNull('timesheet.deleted_at')
        ->groupBy('mission_theme.mission_theme_id');


        $hoursPerThemes = $queryBuilder->get();

        $languageCode = config('app.locale');
        foreach ($hoursPerThemes as $theme) {
            $arrayKey = array_search($languageCode, array_column(
                $theme->translations,
                'lang'
            ));
            if ($arrayKey  !== false) {
                $theme->theme_name = $theme->translations[$arrayKey]['title'];
            }
            unset($theme->translations);
        }
        return $hoursPerThemes;
    }

    /**
     * It will check is MissionTheme belongs to any mission or not
     *
     * @param int $id
     * @return bool
     */
    public function hasMission(int $id): bool
    {
        return $this->missionTheme->whereHas('mission')->whereMissionThemeId($id)->count() ? true : false;
    }
}
