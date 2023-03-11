<?php

namespace Tests\Unit\Services\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Models\PaymentGateway\PaymentGatewayCustomer;
use App\Repositories\PaymentGateway\CustomerRepository;
use App\Services\PaymentGateway\CustomerService;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;
use TestCase;

class CustomerServiceTest extends TestCase
{
    /**
     * @var App\Repositories\PaymentGateway\CustomerRepository
     */
    private $customerRepositoryMock;

    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->customerModel = $this->createMock(PaymentGatewayCustomer::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
    }

    public function testGetWithId()
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $assertedResult = 'foo';
        $collection = Collection::make([$assertedResult]);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($collection);
        $customerService = new customerService($this->customerRepositoryMock);
        $customers = $customerService->get($userId, $id);
        $this->assertSame($assertedResult, $customers->first());
    }

    public function testGetWithoutId()
    {
        $userId = rand(10, 99);
        $assertedResult = 'foo';
        $collection = Collection::make([$assertedResult]);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId)
            ->willReturn($collection);
        $customerService = new customerService($this->customerRepositoryMock);
        $customers = $customerService->get($userId);
        $this->assertSame($assertedResult, $customers->first());
    }

    public function testCreate()
    {
        $detailedCustomer = new PaymentGatewayDetailedCustomer;
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($detailedCustomer)
            ->willReturn($detailedCustomer);
        $customerService = new customerService($this->customerRepositoryMock);
        $customer = $customerService->create($detailedCustomer);
        $this->assertInstanceOf(PaymentGatewayDetailedCustomer::class, $customer);
    }
}
