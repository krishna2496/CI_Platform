<?php

namespace App\Services\DonationIp;

use App\Models\DonationIpWhitelist;
use App\Repositories\DonationIp\WhitelistRepository;

class WhitelistService
{
    /**
    * @var WhitelistRepository
    */
    private $whitelistRepository;

    /**
     * Create a new controller instance.
     *
     * @param WhitelistRepository $whitelistRepository
     *
     * @return void
     */
    public function __construct(WhitelistRepository $whitelistRepository)
    {
        $this->whitelistRepository = $whitelistRepository;
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
        return $this->whitelistRepository->findById($id);
    }

    /**
     * Get list of whitelisted Ips
     *
     * @param array $paginate
     *              $paginate['perPage'] Item limit count per page
     * @param array $filters
     *              $filters['search'] Search for pattern or description
     *
     * @return Object
     */
    public function getList(array $paginate, array $filters)
    {
        return $this->whitelistRepository->getList($paginate, $filters);
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
        return $this->whitelistRepository->create($whitelistIp);
    }

    /**
     * Update whitelisted Ip
     *
     * @param DonationIpWhitelist $whitelistIp
     *
     * @return bool
     */
    public function update(DonationIpWhitelist $whitelistIp)
    {
        return $this->whitelistRepository->update($whitelistIp);
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
        return $this->whitelistRepository->delete($id);
    }

}