<?php

namespace App\Repositories\Availability;

use App\Models\Availability;
use App\Repositories\Availability\AvailabilityInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AvailabilityRepository implements AvailabilityInterface
{
    /**
     *
     * @var App\Models\Availability
     */
    private $availability;

    /**
     * Create a new availability repository instance.
     *
     * @param App\Models\Availability $availability
     * @return void
     */
    public function __construct(Availability $availability)
    {
        $this->availability = $availability;
    }

    /**
     * Fetch availability lists with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAvailabilityList(Request $request): LengthAwarePaginator
    {
        $availabilityQuery = $this->availability->select('availability_id', 'type', 'translations');
        if ($request->has('search')) {
            $availabilityQuery->where(function ($query) use ($request) {
                $query->orWhere('type', 'like', $request->input('search') . '%');
                $query->orWhere('translations', 'like', $request->input('search') . '%');
            });
        }
        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $availabilityQuery->orderBy('availability_id', $orderDirection);
        }
        return $availabilityQuery->paginate($request->perPage);
    }

    /**
     * Store a newly created availability details.
     *
     * @param array $availabilityData
     * @return App\Models\Availability
     */
    public function store(array $availabilityData): Availability
    {
        return $this->availability->create($availabilityData);
    }

    /**
     * Update availability details.
     *
     * @param  array  $availabilityData
     * @param  int  $availabilityId
     * @return App\Models\Availability
     */
    public function update(array $availabilityData, int $availabilityId): Availability
    {
        $availabilityDetails = $this->availability->findOrFail($availabilityId);
        $availabilityDetails->update($availabilityData);
        return $availabilityDetails;
    }

    /**
     * Remove availability details.
     *
     * @param int $availabilityId
     * @return bool
     */
    public function delete(int $availabilityId): bool
    {
        return $this->availability->deleteAvailability($availabilityId);
    }

    /**
     * Find availability details.
     *
     * @param  int  $id
     * @return App\Models\Availability
     */
    public function find(int $id): Availability
    {
        return $this->availability->findOrFail($id);
    }

    /**
     * It will check is availability belongs to any mission or not
     *
     * @param int $id
     * @return bool
     */
    public function hasMission(int $id): bool
    {
        return $this->availability->whereHas('volunteeringAttribute')->whereAvailabilityId($id)->count() ? true : false;
    }

    /**
     * It will check is availability belongs to any user or not
     *
     * @param int $id
     * @return bool
     */
    public function hasUser(int $id): bool
    {
        return $this->availability->whereHas('user')->whereAvailabilityId($id)->count() ? true : false;
    }
}
