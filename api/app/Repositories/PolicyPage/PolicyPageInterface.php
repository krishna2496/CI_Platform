<?php
namespace App\Repositories\PolicyPage;

use App\Models\PolicyPage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PolicyPageInterface
{
    /**
     * Store a newly created resource in storage
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\PolicyPage
     */
    public function store(Request $request): PolicyPage;
    
    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\PolicyPage
     */
    public function find(int $id): PolicyPage;
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request $request
    * @param  int $id
    * @return App\Models\PolicyPage
    */
    public function update(Request $request, int $id): PolicyPage;
    
    /**
    * Display a listing of policy pages.
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function getPolicyPageList(Request $request): LengthAwarePaginator;
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get a listing of resource.
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection
     */
    public function getPageList(Request $request): Collection;
    
    /**
     * Get a listing of resource.
     *
     * @param Illuminate\Http\Request $request
     * @param string $slug
     * @return App\Models\PolicyPage
     */
    public function getPageDetail(Request $request, string $slug): PolicyPage;
}
