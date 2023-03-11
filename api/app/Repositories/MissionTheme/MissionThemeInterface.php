<?php
namespace App\Repositories\MissionTheme;

use Illuminate\Http\Request;
use App\Models\MissionTheme;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MissionThemeInterface
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $theme_id
     * @return Illuminate\Support\Collection
     */
    public function missionThemeList(Request $request, String $theme_id = ''): Collection;

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function missionThemeDetails(Request $request): LengthAwarePaginator;

    /**
     * Store a newly created resource in storage.
     *
     * @param array $request
     * @return App\Models\MissionTheme
     */
    public function store(array $request): MissionTheme;

    /**
     * Update the specified resource in storage.
     *
     * @param  array $request
     * @param  int $id
     * @return App\Models\MissionTheme
     */
    public function update(array $request, int $id): MissionTheme;

    /**
     * Find specified resource in storage.
     *
     * @param  int  $id
     * @return App\Models\MissionTheme
     */
    public function find(int $id): MissionTheme;

    /**
     * Remove specified resource in storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get all theme history with total minutes logged, based on year and all years.
     *
     * @param int $year
     * @param int $userId
     * @return Illuminate\Support\Collection
     */
    public function getHoursPerTheme(int $year = null, int $userId): Collection;
}
