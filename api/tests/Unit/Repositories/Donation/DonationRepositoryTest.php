<?php

namespace Tests\Unit\Repositories\Donation;

use App\Models\Donation;
use App\Models\Mission;
use App\Models\PaymentGateway\Payment;
use App\Repositories\Donation\DonationRepository;
use Faker\Factory as FakerFactory;
use Mockery;
use TestCase;

class DonationRepositoryTest extends TestCase
{
    /**
     * @var App\Repositories\Donation\DonationRepository
     */
    private $repository;

    /**
     * @var App\Models\Donation
     */
    private $donation;

    /**
     * @var Faker
     */
    private $faker;

    /**
     * @var Mission
     */
    private $mission;

    /**
     * @var Payment
     */
    private $payment;


    public function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
        $this->donation = $this->mock(Donation::class);
        $this->mission = $this->mock(Mission::class);
        $this->payment = $this->mock(Payment::class);

        $this->repository = new DonationRepository(
            $this->donation,
            $this->mission,
            $this->payment
        );
    }

    /**
     * @testdox Test create method on DonationRepository class
     */
    public function testCreate()
    {
        $data = [
            'mission_id' => rand(0, 100),
            'payment_id' => $this->faker->uuid,
            'organization_id' => $this->faker->uuid,
            'user_id' => 1
        ];

        $donation = (new Donation())
            ->setAttribute('mission_id', $data['mission_id'])
            ->setAttribute('payment_id', $data['payment_id'])
            ->setAttribute('organization_id', $data['organization_id'])
            ->setAttribute('user_id', $data['user_id']);

        $this->donation
            ->shouldReceive('create')
            ->once()
            ->with($donation->getAttributes())
            ->andReturn($donation);

        $response = $this->repository->create($donation);

        $this->assertSame($response, $donation);
    }

    /**
     * @testdox Test method getMissionStatistics
     */
    public function testGetMissionStatistics()
    {
        $filters = [
            '1970-01-01:1975-12-20'
        ];

        $mission = (new Mission())
            ->setAttribute('missions', 'missionCount')
            ->setAttribute('organizations', 'organizationsCount')
            ->setAttribute('missions_0', 'mission0Count')
            ->setAttribute('organizations_0', 'organizations0Count');

        $this->mission
            ->shouldReceive('selectRaw')
            ->once()
            ->with('
                COUNT(mission_id) as missions
            ')
            ->andReturnSelf()
            ->shouldReceive('when')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('isApproved')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('isDonationTypes')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($mission);

        $response = $this->repository->getMissionStatistics($filters);
        $this->assertInstanceOf(Mission::class, $response);
    }

    /**
     * @testdox Test method getDonationStatistics
     */
    public function testGetDonationStatistics()
    {
        $filters = [
            '1970-01-01:1975-12-20'
        ];

        $payment = (new Payment())
            ->setAttribute('donations', 'donationsCount')
            ->setAttribute('donations_0', 'donations0Count');

        $this->payment
            ->shouldReceive('selectRaw')
            ->once()
            ->with('
                SUM(payment.amount) as donations,
                COUNT(DISTINCT donation.organization_id) as organizations
            ')
            ->andReturnSelf()
            ->shouldReceive('when')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->once()
            ->with('payment.status', config('constants.payment_statuses.SUCCESS'))
            ->andReturnSelf()
            ->shouldReceive('join')
            ->once()
            ->with('donation', 'donation.payment_id', '=', 'payment.id')
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($payment);

        $response = $this->repository->getDonationStatistics($filters);
        $this->assertInstanceOf(Payment::class, $response);
    }

    /**
     * @testdox Test method getMissionDonationStatistics
     */
    public function testGetMissionDonationStatistics()
    {
        $donation = (new Donation())
            ->setAttribute('mission_id', 1)
            ->setAttribute('count', 100000)
            ->setAttribute('donors', 10)
            ->setAttribute('first', '1970-01-01 09:06:12')
            ->setAttribute('last', '2020-10-30 02:03:05')
            ->setAttribute('total', '3036358400960.8700')
            ->setAttribute('lowest', '10.0000')
            ->setAttribute('highest', '994068136.4200')
            ->setAttribute('all_donations', '3036358400960.8700')
            ->setAttribute('average', '30363584.0096')
            ->setAttribute('median', '13505.6700');

        $this->donation
            ->shouldReceive('selectRaw')
            ->once()
            ->with('
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
            ->andReturnSelf()
            ->shouldReceive('where')
            ->once()
            ->with('donation.mission_id', $donation->mission_id)
            ->andReturnSelf()
            ->shouldReceive('where')
            ->once()
            ->with('payment.status', config('constants.payment_statuses.SUCCESS'))
            ->andReturnSelf()
            ->shouldReceive('join')
            ->once()
            ->with('payment', 'payment.id', '=', 'donation.payment_id')
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($donation);

        $donationMedian = (new Donation())
            ->setAttribute('amount', '13505.6700');

        $missionId = $donation->mission_id;
        $type = $donation->count % 2;
        $offset = ceil($donation->count / 2);
        $clauses = [
            'limit' => $type ? 1 : 2,
            'offset' =>  $type ? $offset : $offset - 1
        ];

        $this->donation
            ->shouldReceive('selectRaw')
            ->once()
            ->with('
                AVG(donation.amount) as amount
            ')
            ->andReturnSelf()
            ->shouldReceive('fromSub')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($donationMedian);

        $response = $this->repository
            ->getMissionDonationStatistics($donation->mission_id);

        $this->assertInstanceOf(Donation::class, $response);
        $this->assertSame($donation, $response);
    }

    /**
     * @testdox Test method getDonationMedian
     */
    public function testGetDonationMedian()
    {
        $donationMedian = (new Donation())
            ->setAttribute('amount', '13505.6700');

        $this->donation
            ->shouldReceive('selectRaw')
            ->once()
            ->with('
                AVG(donation.amount) as amount
            ')
            ->andReturnSelf()
            ->shouldReceive('fromSub')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($donationMedian);

        $response = $this->repository
            ->getDonationMedian(10000, 1);

        $this->assertInstanceOf(Donation::class, $response);
        $this->assertSame($donationMedian, $response);
    }

    /**
     * Mock an object
     *
     * @param string name
     *
     * @return Mockery
     */
    private function mock($class)
    {
        return Mockery::mock($class);
    }
}
