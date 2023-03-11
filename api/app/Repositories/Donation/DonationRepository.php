<?php

namespace App\Repositories\Donation;

use App\Libraries\Amount;
use App\Models\Donation;
use App\Models\Mission;
use App\Models\PaymentGateway\Payment;
use DB;

class DonationRepository
{
    /**
     * @var App\Models\Donation
     */
    private $donation;

    /**
     * @var App\Models\Mission
     */
    private $mission;

    /**
     * @var App\Models\PaymentGateway\Payment
     */
    private $payment;

    /**
     * Creates donation repository instance
     *
     * @param Donation $donation
     * @param Mission $mission
     * @param Payment $payment
     *
     * @return void
     */
    public function __construct(
        Donation $donation,
        Mission $mission,
        Payment $payment
    ) {
        $this->donation = $donation;
        $this->mission = $mission;
        $this->payment = $payment;
    }

    /**
     * Create donation record
     * @param Donation $donation
     *
     * @return Donation
     */
    public function create(Donation $donation): Donation
    {
        return $this->donation->create($donation->getAttributes());
    }

    /**
     * Get total donations amount by mission id
     * @param int $missionId
     *
     * @return Amount $amount
     */
    public function getMissionTotalDonationAmount(int $missionId)
    {
        // TODO: consider different currencies and exchange rate conversions
        $amount = $this->donation
            ->join('payment', 'payment.id', '=', 'donation.payment_id')
            ->where('donation.mission_id', '=', $missionId)
            ->where('payment.status', config('constants.payment_statuses.SUCCESS'))
            ->sum('payment.transfer_amount');

        return new Amount($amount);
    }

    /**
     * Get donation mission statistics with date ranges filters
     *
     * @param array $filters Format: ['Y-M-D:Y-M-D','Y-M-D:Y-M-D']
     *
     * @return Mission
     */
    public function getMissionStatistics(array $filters = []): Mission
    {
        return $this->mission
            ->selectRaw('
                COUNT(mission_id) as missions
            ')
            ->when(!empty($filters), function($query) use ($filters) {
                $conditions = 'DATE(start_date) BETWEEN ? AND ?';
                foreach ($filters as $key => $value) {
                    list($from, $to) = explode(':', $value);
                    $params = [$from, $to];
                    $query->selectRaw(
                        "SUM($conditions) AS missions_$key",
                        $params
                    )->orWhere(function($query) use ($conditions, $params) {
                        $query->whereRaw(
                            $conditions,
                            $params
                        );
                    });
                }
            })
            ->isApproved()
            ->isDonationTypes()
            ->first();
    }

    /**
     * Get donation statistics with date ranges filters
     *
     * @param array $filters Format: ['Y-M-D:Y-M-D','Y-M-D:Y-M-D']
     *
     * @return Payment
     */
    public function getDonationStatistics(array $filters = []): Payment
    {
        // TODO: When multi currency supports. Convert amount to default currency
        return $this->payment
            ->selectRaw('
                SUM(payment.amount) as donations,
                COUNT(DISTINCT donation.organization_id) as organizations
            ')
            ->when(!empty($filters), function($query) use ($filters) {
                $conditions = 'DATE(payment.created_at) BETWEEN ? AND ?';
                foreach ($filters as $key => $value) {
                    list($from, $to) = explode(':', $value);
                    $params = [$from, $to];
                    $query->selectRaw(
                        "SUM(IF($conditions, payment.amount, 0)) AS donations_$key",
                        $params
                    )->selectRaw(
                        "COUNT(DISTINCT IF($conditions, donation.organization_id, NULL)) AS organizations_$key",
                        $params
                    )->orWhere(function($query) use ($conditions, $params) {
                        $query->whereRaw(
                            $conditions,
                            $params
                        );
                    });
                }
            })
            ->where('payment.status', config('constants.payment_statuses.SUCCESS'))
            ->join('donation', 'donation.payment_id', '=', 'payment.id')
            ->first();
    }

    /**
     * Get mission donation statistics
     *
     * @param int $missionId
     *
     * @return Donation
     */
    public function getMissionDonationStatistics(int $missionId): Donation
    {
        $donation = $this->donation
            ->selectRaw('
                COUNT(donation.id) as count,
                COUNT(DISTINCT donation.user_id) as donors,
                MIN(donation.created_at) as first,
                MAX(donation.created_at) as last,
                SUM(payment.amount) as total,
                MIN(payment.amount) as lowest,
                MAX(payment.amount) as highest,
                ROUND(AVG(payment.amount), 4) as average,
                (SELECT SUM(amount) FROM payment WHERE status = ?) as all_donations
            ', [config('constants.payment_statuses.SUCCESS')])
            ->where('donation.mission_id', $missionId)
            ->where('payment.status', config('constants.payment_statuses.SUCCESS'))
            ->join('payment', 'payment.id', '=', 'donation.payment_id')
            ->first();

        $median = $this->getDonationMedian(
            $donation->count,
            $missionId
        );

        $donation->setAttribute('median', $median->amount);
        return $donation;
    }

    /**
     * Get mission donation median
     *
     * @param int $donationCount
     * @param int|null $missionId
     *
     * @return Donation
     */
    public function getDonationMedian($donationCount, int $missionId = null)
    {
        $type = $donationCount % 2;
        $offset = ceil($donationCount / 2);
        $clauses = [
            'limit' => $type ? 1 : 2,
            'offset' =>  $type ? $offset : $offset - 1
        ];

        return $this->donation
            ->selectRaw('
                AVG(donation.amount) as amount
            ')
            ->fromSub(function ($query) use ($missionId, $clauses) {
                $query
                    ->selectRaw('
                        payment.amount,
                        payment.deleted_at
                    ')
                    ->from('payment')
                    ->when($missionId !== null, function ($query) use ($missionId) {
                        $query->where('donation.mission_id', $missionId);
                    })
                    ->whereNull([
                        'payment.deleted_at',
                        'donation.deleted_at'
                    ])
                    ->where('payment.status', config('constants.payment_statuses.SUCCESS'))
                    ->join('donation', 'donation.payment_id', '=', 'payment.id')
                    ->orderBy('payment.amount', 'ASC')
                    ->take($clauses['limit'])
                    ->skip($clauses['offset']);
            }, 'donation')
            ->first();
    }
}