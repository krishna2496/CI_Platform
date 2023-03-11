<?php

namespace Tests\Unit\Repositories\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Models\PaymentGateway\PaymentGatewayCustomer;
use App\Repositories\PaymentGateway\CustomerRepository;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use StdClass;
use TestCase;

class CustomerRepositoryTest extends TestCase
{
    /**
     * @var App\Models\PaymentGateway\PaymentGatewayCustomer
     */
    private $PaymentGatewayCustomer;

    private $faker;

    /**
     * @var App\Repositories\PaymentGateway\CustomerRepository
     */
    private $customerRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    public function testGetModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $userId = 123;

        $whereClause = [
            'user_id' => $userId,
            'deleted_at' => null,
        ];
        $returnedResult = Collection::make([]);

        $customerModel = $this->getCustomerModelMock($whereClause, $returnedResult);

        $customerRepository = new CustomerRepository($customerModel);
        $customerRepository->get($userId);
    }

    public function testGetWithIdSuccess()
    {
        $userId = 123;
        $id = $this->faker->uuid();

        $customer = new StdClass;
        $customer->id = $id;
        $customer->user_id = $userId;
        $customer->payment_gateway_customer_id = 'cus_foo';
        $customer->payment_gateway = 1;

        $whereClause = [
            'user_id' => $userId,
            'deleted_at' => null,
            'id' => $id,
        ];
        $returnedResult = Collection::make([$customer]);

        $customerModel = $this->getCustomerModelMock($whereClause, $returnedResult);

        $customerRepository = new CustomerRepository($customerModel);
        $customers = $customerRepository->get($userId, $id);

        $this->assertInstanceOf(Collection::class, $customers);
        $this->assertSame(1, $customers->count());

        $detailedCustomer = $customers->first();
        $this->assertInstanceOf(PaymentGatewayDetailedCustomer::class, $detailedCustomer);
        $this->assertSame($customer->id, $detailedCustomer->getId());
        $this->assertSame(
            $customer->payment_gateway_customer_id,
            $detailedCustomer->getPaymentGatewayCustomerId()
        );
    }

    public function testGetWithoutIdSuccess()
    {
        $userId = 123;

        $generateCustomer = function() use ($userId) {
            $customer = new StdClass;
            $customer->id = $this->faker->uuid();
            $customer->user_id = $userId;
            $customer->payment_gateway_customer_id = 'cus_foo_'.rand(1000,9999);
            $customer->payment_gateway = 1;
            return $customer;
        };

        $whereClause = [
            'user_id' => $userId,
            'deleted_at' => null,
        ];

        $customer = $generateCustomer();
        $returnedResult = Collection::make([
            $customer,  // an assertion is made for this first customer.
            $generateCustomer(),
            $generateCustomer(),
            $generateCustomer(),
            $generateCustomer(),
        ]);

        $customerModel = $this->getCustomerModelMock($whereClause, $returnedResult);

        $customerRepository = new CustomerRepository($customerModel);
        $customers = $customerRepository->get($userId);

        $this->assertInstanceOf(Collection::class, $customers);
        $this->assertSame(5, $customers->count());

        $detailedCustomer = $customers->first();
        $this->assertInstanceOf(PaymentGatewayDetailedCustomer::class, $detailedCustomer);
        $this->assertSame($customer->id, $detailedCustomer->getId());
        $this->assertSame(
            $customer->payment_gateway_customer_id,
            $detailedCustomer->getPaymentGatewayCustomerId()
        );
    }

    public function getCustomerModelMock($whereClause, $returnedResult)
    {
        $customerModelMock = $this->getMockBuilder(PaymentGatewayCustomer::class)
            ->setMethods([
                'get',
                'keyBy',
                'where',
            ])
            ->getMock();
        $customerModelMock
            ->expects($this->once())
            ->method('where')
            ->with($whereClause)
            ->willReturnSelf();
        $customerModelMock
            ->expects($this->once())
            ->method('get')
            ->willReturnSelf();
        $customerModelMock
            ->expects($this->once())
            ->method('keyBy')
            ->with('id')
            ->willReturn($returnedResult);
        return $customerModelMock;
    }
}
