<?php
namespace App\Repositories\UserFilter;

use App\Repositories\UserFilter\UserFilterInterface;
use Illuminate\Http\Request;
use App\Models\UserFilter;
use App\Traits\RestExceptionHandlerTrait;

class UserFilterRepository implements UserFilterInterface
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Models\UserFilter
     */
    public $filters;
   
    /**
     * Create a new user filter repository instance.
     *
     * @param  App\Models\UserFilter $filters
     * @return void
     */
    public function __construct(UserFilter $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Display a listing of User filter.
     *
     * @param Illuminate\Http\Request $request
     * @return null|App\Models\UserFilter
     */
    public function userFilter(Request $request): ?UserFilter
    {
        return $this->filters->get()->where("user_id", $request->auth->user_id)->first();
    }

    /**
     * Store or Update created resource.
     *
     * @param  Illuminate\Http\Request
     * @return App\Models\UserFilter
     */
    public function saveFilter(Request $request): UserFilter
    {
        $defaultCountryId = $defaultCityId = '';

        if (!$request->has('country_id')) {
            if (isset($request->auth) && ($request->auth->country_id !== '') && ($request->auth->country_id !== 0)) {
                $defaultCountryId = $request->auth->country_id;
            }
        }
        if (!$request->has('city_id')) {
            if (isset($request->auth) && ($request->auth->city_id !== '') && ($request->auth->city_id !== 0)) {
                $defaultCityId = $request->auth->city_id;
            }
        }

        if (!$request->has('state_id')) {
            if (isset($request->auth) && ($request->auth->state_id !== '') && ($request->auth->state_id !== 0)) {
                $defaultStateId = $request->auth->state_id;
            }
        }

         
        if ($request->has('country_id') && (!$request->has('state_id'))) {
            $defaultStateId = '';
        }

        if ($request->has('country_id') && (!$request->has('city_id'))) {
            $defaultCityId = '';
        }

        if ($request->has('explore_mission_type') && $request->input('explore_mission_type') !== '') {
            $defaultCountryId = $defaultCityId = $defaultStateId = '';
        }

        $userFilterData["search"] = $request->has('search') ? $request->input('search') : '';
        $userFilterData["country_id"] = $request->has('country_id') ? $request->input('country_id') : $defaultCountryId;
        $userFilterData["city_id"] = $request->has('city_id') ? $request->input('city_id') : $defaultCityId;
        $userFilterData["state_id"] = $request->has('state_id') ? $request->input('state_id') : $defaultStateId;
        $userFilterData["theme_id"] = $request->has('theme_id') ? $request->input('theme_id') : '';
        $userFilterData["skill_id"] = $request->has('skill_id') ? $request->input('skill_id') : '';
        $userFilterData["sort_by"] = $request->has('sort_by') ? $request->input('sort_by') : '';
        $userFilterData["current_view"] = $request->has('current_view') ? $request->input('current_view') : 0;
        $userFilter= $this->filters->createOrUpdateUserFilter(
            ['user_id' => $request->auth->user_id],
            array('filters' => $userFilterData)
        );

        return $userFilter;
    }
}
