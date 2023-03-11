<?php
namespace App\Repositories\UserCustomField;

use Illuminate\Http\Request;
use App\Models\UserCustomField;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserCustomFieldInterface
{
    /**
     * Store a newly created resource in storage.
     *
     * @param array $request
     * @return App\Models\UserCustomField
     */
    public function store(array $request): UserCustomField;

    /**
    * Update the specified resource in storage.
    *
    * @param  array $request
    * @param  int $id
    * @return App\Models\UserCustomField
    */
    public function update(array $request, int $id): UserCustomField;
    
    /**
     * Find user custom field in storage.
     *
     * @param  int  $id
     * @return App\Models\UserCustomField
     */
    public function find(int $id): UserCustomField;
    
    /**
     * Get listing of user custom fields
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function userCustomFieldList(Request $request): LengthAwarePaginator;

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get listing of user custom fields
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection
     */
    public function getUserCustomFields(Request $request): Collection;
}
