<?php
namespace App\Repositories\Availability;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Availability;

interface AvailabilityInterface
{
    /**
     * Fetch availability lists with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAvailabilityList(Request $request): LengthAwarePaginator;

    /**
     * Store a newly created availability details.
     *
     * @param array $availabilityData
     * @return App\Models\Availability
     */
    public function store(array $availabilityData): Availability;

    /**
     * Update availability details.
     *
     * @param  array  $availabilityData
     * @param  int  $availabilityId
     * @return App\Models\Availability
     */
    public function update(array $availabilityData, int $availabilityId): Availability;

    /**
     * Remove availability details.
     *
     * @param int $availabilityId
     * @return bool
     */
    public function delete(int $availabilityId): bool;
    
    /**
     * Find availability details.
     *
     * @param  int  $id
     * @return App\Models\Availability
     */
    public function find(int $id): Availability;
}
