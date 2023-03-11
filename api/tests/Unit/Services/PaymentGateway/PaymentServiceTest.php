<?php

namespace Tests\Unit\Services\PaymentGateway;

use App\Models\PaymentGateway\Payment;
use App\Repositories\PaymentGateway\PaymentRepository;
use App\Services\PaymentGateway\PaymentService;
use Faker\Factory as FakerFactory;
use Mockery;
use TestCase;

class PaymentServiceTest extends TestCase
{
    /**
     * @var App\Repositories\PaymentGateway\PaymentRepository
     */
    private $repository;

    /**
     * @var App\Models\PaymentGateway\Payment
     */
    private $payment;

    /**
     * @var App\Services\PaymentGateway\PaymentService
     */
    private $service;

    /**
     * @var Faker
     */
    private $faker;

    public function setUp(): void
    {
        $this->repository = $this->mock(PaymentRepository::class);
        $this->payment = $this->mock(Payment::class);
        $this->faker = FakerFactory::create();

        $this->service = new PaymentService(
            $this->repository
        );
    }

    /**
     * @testdox Test create method on PaymentService class
     */
    public function testCreate()
    {
        $expected = (new Payment())
            ->setAttribute('id', $this->faker->uuid)
            ->setAttribute('amount', 100);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($this->payment)
            ->andReturn($expected);

        $response = $this->service->create(
            $this->payment
        );

        $this->assertSame($response, $expected);
    }

    /**
     * @testdox Test update method on PaymentService class
     */
    public function testUpdate()
    {
        $expected = true;

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($this->payment)
            ->andReturn($expected);

        $response = $this->service->update(
            $this->payment
        );

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
