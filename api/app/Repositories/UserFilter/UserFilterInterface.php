<?php
namespace App\Repositories\UserFilter;

use Illuminate\Http\Request;
use App\Models\UserFilter;

interface UserFilterInterface
{
    /**
     * Display a listing of User filter.
     *
     * @param Illuminate\Http\Request $request
     * @return null|App\Models\UserFilter
     */
    public function userFilter(Request $request): ?UserFilter;

    /**
     * Store or Update created resource.
     *
     * @param  Illuminate\Http\Request
     * @return App\Models\UserFilter
     */
    public function saveFilter(Request $request): UserFilter;
}
