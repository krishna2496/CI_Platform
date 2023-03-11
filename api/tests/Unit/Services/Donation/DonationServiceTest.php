<?php

namespace Tests\Unit\Services\Donation;

use App\Models\Donation;
use App\Models\Mission;
use App\Models\PaymentGateway\Payment;
use App\Repositories\Donation\DonationRepository;
use App\Services\Donation\DonationService;
use Faker\Factory as FakerFactory;
use Mockery;
use TestCase;

class DonationServiceTest extends TestCase
{
    /**
     * @var App\Repositories\Donation\DonationRepositoryTest
     */
    private $repository;

    /**
     * @var App\Models\Donation
     */
    private $donation;

    /**
     * @var App\Services\Donation\DonationService
     */
    private $service;

    /**
     * @var Faker
     */
    private $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->mock(DonationRepository::class);
        $this->donation = $this->mock(Donation::class);
        $this->faker = FakerFactory::create();

        $this->service = new DonationService(
            $this->repository
        );
    }

    /**
     * @testdox Test create method on DonationService class
     */
    public function testCreate()
    {
        $expected = (new Donation())
            ->setAttribute('id', $this->faker->uuid)
            ->setAttribute('mission_id', rand(1, 100))
            ->setAttribute('payment_id', $this->faker->uuid)
            ->setAttribute('organization_id', $this->faker->uuid)
            ->setAttribute('user_id', 1);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($this->donation)
            ->andReturn($expected);

        $response = $this->service->create(
            $this->donation
        );

        $this->assertSame($response, $expected);
    }

    /**
     * @testdox Test getStatistics method on DonationService class
     */
    public function testGetStatistics()
    {
        $filters = [
            '1970-01-01:1975-12-20'
        ];

        $mission = (new Mission())
            ->setAttribute('missions', 'missionCount')
            ->setAttribute('missions_0', 'mission0Count');

        $this->repository
            ->shouldReceive('getMissionStatistics')
            ->once()
            ->with($filters)
            ->andReturn($mission);

        $payment = (new Payment())
            ->setAttribute('donations', 'donationsCount')
            ->setAttribute('organizations', 'organizationsCount')
            ->setAttribute('donations_0', 'donations0Count')
            ->setAttribute('organizations_0', 'organizations0Count');

        $this->repository
            ->shouldReceive('getDonationStatistics')
            ->once()
            ->with($filters)
            ->andReturn($payment);

        list($from, $to) = explode(':', $filters[0]);

        $expected = [
            'missions' => $mission->missions,
            'organizations' => $payment->organizations,
            'donations' => [
                'currency' => null,
                'amount' => $payment->donations
            ],
            'date_ranges' => [
                [
                    'start_date' => $from,
                    'end_date' => $to,
                    'total_missions' => $mission["missions_0"],
                    'total_organizations' => $payment["organizations_0"],
                    'total_donations' => [
                        'currency' => null,
                        'amount' => $payment["donations_0"]
                    ]
                ]
            ]
        ];

        $response = $this->service->getStatistics($filters, null);
        $this->assertSame($response, $expected);
    }

    /**
     * @testdox Test getMissionDonationStatistics method on DonationService class
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
            ->setAttribute('median', '13505.6700')
            ->setAttribute('highest', '994068136.4200')
            ->setAttribute('all_donations', '3036358400960.8700')
            ->setAttribute('average', '30363584.0096');

        $this->repository
            ->shouldReceive('getMissionDonationStatistics')
            ->once()
            ->with($donation->mission_id)
            ->andReturn($donation);

        $expected = [
            'total' => 100000,
            'donors' => 10,
            'first_datetime' => '1970-01-01 09:06:12',
            'last_datetime' => '2020-10-30 02:03:05',
            'amount' => [
                'total' => '3036358400960.8700',
                'minimum' => '10.0000',
                'median' => '13505.6700',
                'maximum' => '994068136.4200',
                'average' => '30363584.0096',
                'percentage' => 100.0
            ]
        ];

        $response = $this->service->getMissionDonationStatistics($donation->mission_id);
        $this->assertSame($response, $expected);
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
