<?php

namespace Tests\Unit\Services\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Models\PaymentGateway\PaymentGatewayPaymentMethod;
use App\Repositories\PaymentGateway\PaymentMethodRepository;
use App\Services\PaymentGateway\CustomerService;
use App\Services\PaymentGateway\PaymentMethodService;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use StdClass;
use TestCase;

class PaymentMethodServiceTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayInterface
     */
    private $paymentGatewayMock;

    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayFactory
     */
    private $paymentGatewayFactoryMock;

    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayFactory
     */
    private $paymentMethodRepositoryMock;

    /**
     * @var App\Services\PaymentGateway\CustomerService
     */
    private $customerServiceMock;

    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->paymentMethodRepositoryMock = $this->createMock(PaymentMethodRepository::class);
        $this->paymentGatewayMock = $this->createMock(PaymentGatewayInterface::class);
        $this->paymentGatewayFactoryMock = $this->createMock(PaymentGatewayFactory::class);
        $this->customerServiceMock = $this->createMock(CustomerService::class);
    }

    public function testGetWithId()
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $paymentMethodId = 'pm_foo';

        $customerCollection = Collection::make([ new PaymentGatewayDetailedCustomer ]);
        $this->customerServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($userId)
            ->willReturn($customerCollection);

        $assertedResult = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $paymentMethodCollection = Collection::make([$assertedResult]);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($paymentMethodCollection);

        $this->paymentGatewayMock
            ->expects($this->never())
            ->method('getCustomerPaymentMethods');

        $this->paymentGatewayMock
            ->expects($this->once())
            ->method('getPaymentMethod')
            ->with($paymentMethodId)
            ->willReturn($assertedResult);

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethods = $paymentMethodService->get($userId, $id);

        $detailedPaymentMethod = $paymentMethods->first();
        $this->assertInstanceOf(PaymentGatewayDetailedPaymentMethod::class, $detailedPaymentMethod);
        $this->assertSame($paymentMethodId, $detailedPaymentMethod->getPaymentGatewayPaymentMethodId());
    }

    public function testGetWithIdNonExistingData()
    {
        $this->expectException(ModelNotFoundException::class);
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $paymentMethodId = 'non_existing';


        $customerCollection = Collection::make([ new PaymentGatewayDetailedCustomer ]);
        $this->customerServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($userId)
            ->willReturn($customerCollection);

        $paymentMethodCollection = Collection::make([]);
        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($paymentMethodCollection);

        $this->paymentGatewayMock
            ->expects($this->never())
            ->method('getPaymentMethod');

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethodService->get($userId, $id);
    }

    public function testGetWithoutId()
    {
        $userId = rand(10, 99);
        $paymentMethodId = 'pm_foo';
        $customerId = 'cus_foo';
        $paymentMethodType = 'card';

        $customerCollection = Collection::make([
            (new PaymentGatewayDetailedCustomer)->setPaymentGatewayCustomerId($customerId),
        ]);
        $this->customerServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($userId)
            ->willReturn($customerCollection);

        $assertedResult = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $paymentMethodCollection = Collection::make([$assertedResult]);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId)
            ->willReturn($paymentMethodCollection);

        $this->paymentGatewayMock
            ->expects($this->once())
            ->method('getCustomerPaymentMethods')
            ->with($customerId, $paymentMethodType);

        $this->paymentGatewayMock
            ->expects($this->never())
            ->method('getPaymentMethod');

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethods = $paymentMethodService->get($userId);

        $detailedPaymentMethod = $paymentMethods->first();
        $this->assertInstanceOf(PaymentGatewayDetailedPaymentMethod::class, $detailedPaymentMethod);
        $this->assertSame($paymentMethodId, $detailedPaymentMethod->getPaymentGatewayPaymentMethodId());
    }

    public function testGetWithFilters()
    {
        $userId = rand(10, 99);
        $filters = [
            'recent' => true
        ];
        $paymentMethodId = 'pm_foo';
        $customerId = 'cus_foo';
        $paymentMethodType = 'card';

        $customerCollection = Collection::make([
            (new PaymentGatewayDetailedCustomer)->setPaymentGatewayCustomerId($customerId),
        ]);
        $this->customerServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($userId)
            ->willReturn($customerCollection);

        $assertedResult = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $paymentMethodCollection = Collection::make([$assertedResult]);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $userId,
                null,
                $filters
            )
            ->willReturn($paymentMethodCollection);

        $this->paymentGatewayMock
            ->expects($this->once())
            ->method('getCustomerPaymentMethods')
            ->with($customerId, $paymentMethodType);

        $this->paymentGatewayMock
            ->expects($this->never())
            ->method('getPaymentMethod');

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethods = $paymentMethodService->get(
            $userId,
            null,
            $filters
        );

        $detailedPaymentMethod = $paymentMethods->first();
        $this->assertInstanceOf(PaymentGatewayDetailedPaymentMethod::class, $detailedPaymentMethod);
        $this->assertSame($paymentMethodId, $detailedPaymentMethod->getPaymentGatewayPaymentMethodId());
    }

    public function testCreate()
    {
        $detailedPaymentMethod = new PaymentGatewayDetailedPaymentMethod;

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->with($detailedPaymentMethod);

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethods = $paymentMethodService->create($detailedPaymentMethod);
    }

    public function testUpdate()
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();

        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setId($id)
            ->setUserId($userId);
        $collection = Collection::make([$detailedPaymentMethod]);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($collection);

        $this->paymentGatewayMock
            ->expects($this->once())
            ->method('updateCustomerPaymentMethod')
            ->with($detailedPaymentMethod);

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethods = $paymentMethodService->update($detailedPaymentMethod);
    }

    public function testUpdateWithNonExistingData()
    {
        $this->expectException(ModelNotFoundException::class);
        $userId = rand(10, 99);
        $id = 'non_existing';

        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setId($id)
            ->setUserId($userId);
        $collection = Collection::make([]);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($collection);

        $this->paymentGatewayMock
            ->expects($this->never())
            ->method('updateCustomerPaymentMethod');

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethodService->update($detailedPaymentMethod);
    }

    public function testDelete()
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $paymentMethodId = 'pm_foo';

        $assertedResult = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $collection = Collection::make([$assertedResult]);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($collection);

        $this->paymentGatewayMock
            ->expects($this->once())
            ->method('detachCustomerPaymentMethod')
            ->with($paymentMethodId);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with($userId, $id);

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethods = $paymentMethodService->delete($userId, $id);
    }

    public function testDeleteWithNonExistingData()
    {
        $this->expectException(ModelNotFoundException::class);
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $paymentMethodId = 'non_existing';

        $assertedResult = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $collection = Collection::make([]);

        $this->paymentMethodRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($collection);

        $this->paymentGatewayMock
            ->expects($this->never())
            ->method('detachCustomerPaymentMethod');

        $paymentMethodService = $this->getPaymentMethodService();
        $paymentMethodService->delete($userId, $id);
    }

    public function getPaymentMethodService()
    {
        $this->paymentGatewayFactoryMock
            ->expects($this->once())
            ->method('getPaymentGateway')
            ->willReturn($this->paymentGatewayMock);
        $paymentMethodService = new PaymentMethodService(
            $this->paymentMethodRepositoryMock,
            $this->paymentGatewayFactoryMock,
            $this->customerServiceMock
        );
        return $paymentMethodService;
    }
}
