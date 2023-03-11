<?php

namespace App\Repositories\DonationIp;

use App\Models\DonationIpWhitelist;

class WhitelistRepository
{
    /**
    * @var DonationIpWhitelist: Model
    */
    private $donationIpWhitelist;

    /**
     * Create a new controller instance.
     *
     * @param DonationIpWhitelist $donationIpWhitelist
     *
     * @return void
     */
    public function __construct(DonationIpWhitelist $donationIpWhitelist)
    {
        $this->donationIpWhitelist = $donationIpWhitelist;
    }

    /**
     * Get whitelisted by id
     *
     * @param array $id
     *
     * @return DonationIpWhitelist
     */
    public function findById($id)
    {
        return $this->donationIpWhitelist->findOrFail($id);
    }

    /**
     * Get list of whitelisted Ips
     *
     * @param array $paginate
     *              $paginate['perPage'] Item limit count per page|null to disable pagination
     * @param array $filters
     *              $filters['search'] Search for pattern or description
     *
     * @return Object
     */
    public function getList($paginate, $filters)
    {
        $whiteListQuery = $this->donationIpWhitelist
            ->select(
                'id',
                'pattern',
                'description',
                'created_at'
            )
            ->when($filters['search'], function($query) use ($filters) {
                $keyword = $filters['search'];
                $query->where('pattern', 'like', "%$keyword%")
                    ->orWhere('description', 'like', "%$keyword%");
            })
            ->when($filters['order'], function($query) use ($filters) {
                foreach ($filters['order'] as $column => $direction) {
                    if ($direction === null || !in_array($direction, ['desc', 'asc'])) {
                        continue;
                    }
                    $query->orderBy($column, $direction);
                }
            });

            if (!$paginate['perPage']) {
                return $whiteListQuery->get();
            }

            return $whiteListQuery->paginate($paginate['perPage']);
    }

    /**
     * Create whitelisted Ip
     *
     * @param DonationIpWhitelist $whitelistIp
     *
     * @return DonationIpWhitelist
     */
    public function create(DonationIpWhitelist $whitelistIp)
    {
        return $this->donationIpWhitelist->create(
            $whitelistIp->toArray()
        );
    }

    /**
     * Update whitelisted Ip
     *
     * @param string $id
     * @param DonationIpWhitelist $whitelistIp
     *
     * @return bool
     */
    public function update(DonationIpWhitelist $whitelistIp)
    {
        $payload = [
            'description' => $whitelistIp->description
        ];

        if ($whitelistIp->pattern) {
            $payload['pattern'] = $whitelistIp->pattern;
        }

        return $this->donationIpWhitelist
            ->find($whitelistIp->id)
            ->update($payload);
    }

    /**
     * Delete whitelisted Ip
     *
     * @param string $id
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->donationIpWhitelist
            ->find($id)
            ->delete();
    }

}