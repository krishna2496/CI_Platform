<?php

namespace App\Services\Donation;

use App\Libraries\Amount;
use App\Models\Donation;
use App\Repositories\Donation\DonationRepository;

class DonationService
{
    const DONATION_AMOUNT_PRECISION = 4;

    /**
     * @var App\Repositories\Donation\DonationRepository
     */
    private $donationRepository;

    /**
     * Creates a new Donation service instance
     *
     * @param DonationRepository $donationRepository
     *
     * @return void
     */
    public function __construct(DonationRepository $donationRepository)
    {
        $this->donationRepository = $donationRepository;
    }

    /**
     * Creates a donation record
     *
     * @param Donation $donation
     *
     * @return Donation
     */
    public function create(Donation $donation): Donation
    {
        return $this->donationRepository->create($donation);
    }

    /**
     * Get total donations amount by mission id
     *
     * @param int $missionId
     *
     * @return \App\Libraries\Amount
     */
    public function getMissionTotalDonationAmount(int $missionId): Amount
    {
        return $this->donationRepository->getMissionTotalDonationAmount($missionId);
    }

    /**
     * Process donation statistics with date ranges filters
     *
     * @param array $filters Format: ['Y-M-D:Y-M-D','Y-M-D:Y-M-D']
     * @param string|null $currency
     *
     * @return array
     */
    public function getStatistics(array $filters = [], ?string $currency = null): array
    {
        $mission = $this->donationRepository->getMissionStatistics($filters);
        $payment = $this->donationRepository->getDonationStatistics($filters);

        $ranges = [];
        foreach ($filters as $key => $filter) {
            list($from, $to) = explode(':', $filter);
            $ranges[] = [
                'start_date' => $from,
                'end_date' => $to,
                'total_missions' => $mission["missions_$key"],
                'total_organizations' => $payment["organizations_$key"],
                'total_donations' => [
                    'currency' => $currency,
                    'amount' => $payment["donations_$key"]
                ]
            ];
        }

        return [
            'missions' => $mission->missions,
            'organizations' => $payment->organizations,
            'donations' => [
                'currency' => $currency,
                'amount' => $payment->donations
            ],
            'date_ranges' => $ranges
        ];
    }

    /**
     * Get specific mission donation statistics
     *
     * @param string $missionId
     *
     * @return array
     */
    public function getMissionDonationStatistics(string $missionId): array
    {
        $donation = $this->donationRepository
            ->getMissionDonationStatistics($missionId);

        $precision = self::DONATION_AMOUNT_PRECISION;
        $percentage = ($donation->total->getValue($precision) / $donation->all_donations) * 100;

        return [
            'total' => $donation->count,
            'donors' => $donation->donors,
            'first_datetime' => $donation->first,
            'last_datetime' => $donation->last,
            'amount' => [
                'total' => $donation->total->getValue($precision),
                'minimum' => $donation->lowest->getValue($precision),
                'median' => $donation->median->getValue($precision),
                'maximum' => $donation->highest->getValue($precision),
                'average' => $donation->average->getValue($precision),
                'percentage' => round($percentage, 2)
            ]
        ];
    }
}