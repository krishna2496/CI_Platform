<?php
namespace App\Repositories\State;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\State;
use Illuminate\Pagination\LengthAwarePaginator;

interface StateInterface
{
    /**
     * Store state data
     *
     * @param string $countryId
     * @return State
     */
    public function store(string $countryId): State;
    
    /**
    * Get listing of all state.
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function stateLists(Request $request): LengthAwarePaginator;
    
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
    * @return App\Models\State
    */
    public function update(Request $request, int $id): State;

    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\State
     */
    public function find(int $id): State;

    /**
    * Get listing of all state by country wise with pagination.
    *
    * @param Illuminate\Http\Request $request
    * @param int $countryId
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function getStateList(Request $request, int $countryId) : LengthAwarePaginator;
    
    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\State
     */
    public function getStateDetails(int $id): State;
    
    /**
     * It will check is state belongs to any user or not
     *
     * @param int $id
     * @return bool
     */
    public function hasUser(int $id): bool;

    /**
     * It will check is state belongs to any mission or not
     *
     * @param int $id
     * @return bool
     */
    public function hasMission(int $id): bool;
}
