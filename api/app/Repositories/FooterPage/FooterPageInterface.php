<?php
namespace App\Repositories\FooterPage;

use App\Models\FooterPage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FooterPageInterface
{
    /**
     * Store a newly created resource in storage
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\FooterPage
     */
    public function store(Request $request): FooterPage;
    
    /**
     * Find the specified resource from database
     *
     * @param int $id
     * @return App\Models\FooterPage
     */
    public function find(int $id): FooterPage;
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return App\Models\FooterPage
    */
    public function update(Request $request, int $id): FooterPage;
    
    /**
    * Display a listing of footer pages.
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function footerPageList(Request $request): LengthAwarePaginator;
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get a listing of resource.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection
     */
    public function getPageList(Request $request): Collection;

    /**
    * Get a listing of resource.
    *
    * @return Illuminate\Support\Collection
    */
    public function getPageDetailList(): Collection;

    /**
    * Get a listing of resource.
    * @param Illuminate\Http\Request $request
    * @param string $slug
    * @return App\Models\FooterPage
    */
    public function getPageDetail(Request $request, string $slug): FooterPage;
}
