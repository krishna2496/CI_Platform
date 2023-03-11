<?php
namespace App\Repositories\NewsCategory;

use Illuminate\Http\Request;
use App\Models\NewsCategory;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface NewsCategoryInterface
{
    /**
     * Display news category list.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getNewsCategoryList(Request $request): LengthAwarePaginator;

    /**
     * Store news category.
     *
     * @param array $request
     * @return App\Models\NewsCategory
     */
    public function store(array $request): NewsCategory;

    /**
     * Update news category.
     *
     * @param  array $request
     * @param  int $id
     * @return App\Models\NewsCategory
     */
    public function update(array $request, int $id): NewsCategory;

    /**
     * Find news category.
     *
     * @param  int  $id
     * @return App\Models\NewsCategory
     */
    public function find(int $id): NewsCategory;

    /**
     * Remove news category.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool;
}
