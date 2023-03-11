<?php
namespace App\Repositories\Organization;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrganizationInterface
{
    /**
     * Store a newly created resource in storage
     *
     * @param \Illuminate\Http\Request $request
     * @return App\Models\Organization
     */
    public function store(Request $request): Organization;
    
    /**
     * Find the specified resource from database
     *
     * @param int $organizationId
     */
    public function getOrganizationDetails($organizationId);
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $organizationId
    * @return App\Models\Organization
    */
    public function update(Request $request, $organizationId);
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $organizationId
     * @return bool
     */
    public function delete($organizationId);

    /**
    * Display a listing of organizations.
    *
    * @param Illuminate\Http\Request $request
    * @return Illuminate\Pagination\LengthAwarePaginator
    */
    public function getOrganizationList(Request $request): LengthAwarePaginator;

    /**
    * find the organization.
    *
    * @param  int  $organizationId
    * @return App\Models\Organization
    */
    public function find($organizationId);
}
