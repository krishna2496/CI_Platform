<?php
namespace App\Repositories\City;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\City;
use Illuminate\Pagination\LengthAwarePaginator;

interface CityInterface
{
    /**
    * Get a listing of resource.
    *
    * @param int $countryId
    * @return Illuminate\Support\Collection
    */
    public function cityList(int $countryId): Collection;

    /**
     * Get city data from cityId
     *
     * @param string $cityId
     * @param int $languageId
     * @return array
     */
    public function getCity(string $cityId, int $languageId) : array;

    /**
     * Store city data
     *
     * @param Request $request
     * @return City
     */
    public function store(Request $request): City;

    /**
     * Get listing of all city.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function cityLists(Request $request): LengthAwarePaginator;
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request $request
    * @param  int $id
    * @return App\Models\City
    */
    public function update(Request $request, int $id): City;

    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\City
     */
    public function find(int $id): City;

    /**
    * Get listing of all city by country wise with pagination.
    *
    * @param Illuminate\Http\Request $request
    * @param int $countryId
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function getCityList(Request $request, int $countryId) : LengthAwarePaginator;
}
