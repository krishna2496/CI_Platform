<?php

namespace Tests\Unit\Repositories\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Models\PaymentGateway\PaymentGatewayPaymentMethod;
use App\Repositories\PaymentGateway\PaymentMethodRepository;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use StdClass;
use TestCase;

class PaymentMethodRepositoryTest extends TestCase
{
    private $faker;

    const TABLE = 'payment_gateway_payment_method';

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    public function testGetWithIdSuccess()
    {
        $userId = 123;
        $id = $this->faker->uuid();
        $table = self::TABLE;

        $paymentMethod = new StdClass;
        $paymentMethod->id = $id;
        $paymentMethod->user_id = $userId;
        $paymentMethod->payment_gateway_payment_method_id = 'pm_foo';
        $paymentMethod->payment_gateway_payment_method_type = 'card';
        $paymentMethod->payment_gateway = 1;

        $whereClause = [
            "$table.user_id" => $userId,
            "$table.deleted_at" => null,
            "$table.id" => $id,
        ];
        $returnedResult = Collection::make([$paymentMethod]);

        $paymentMethodModel = $this->getPaymentMethodModelMock($whereClause, $returnedResult);

        $paymentMethodRepository = new PaymentMethodRepository($paymentMethodModel);
        $paymentMethods = $paymentMethodRepository->get($userId, $id);

        $this->assertInstanceOf(Collection::class, $paymentMethods);
        $this->assertSame(1, $paymentMethods->count());

        $detailedPaymentMethod = $paymentMethods->first();
        $this->assertInstanceOf(PaymentGatewayDetailedPaymentMethod::class, $detailedPaymentMethod);
        $this->assertSame($paymentMethod->id, $detailedPaymentMethod->getId());
        $this->assertSame(
            $paymentMethod->payment_gateway_payment_method_id,
            $detailedPaymentMethod->getPaymentGatewayPaymentMethodId()
        );
    }

    public function testGetWithoutIdSuccess()
    {
        $userId = 123;
        $table = self::TABLE;

        $generatePaymentMethod = function() use ($userId) {
            $paymentMethod = new StdClass;
            $paymentMethod->id = $this->faker->uuid();
            $paymentMethod->user_id = $userId;
            $paymentMethod->payment_gateway_payment_method_id = 'pm_foo_'.rand(1000,9999);
            $paymentMethod->payment_gateway_payment_method_type = 'card';
            $paymentMethod->payment_gateway = 1;
            return $paymentMethod;
        };

        $whereClause = [
            "$table.user_id" => $userId,
            "$table.deleted_at" => null
        ];
        $paymentMethod = $generatePaymentMethod();
        $returnedResult = Collection::make([
            $paymentMethod,  // an assertion is made for this first payment method.
            $generatePaymentMethod(),
            $generatePaymentMethod(),
            $generatePaymentMethod(),
            $generatePaymentMethod(),
        ]);

        $paymentMethodModel = $this->getPaymentMethodModelMock($whereClause, $returnedResult);

        $paymentMethodRepository = new PaymentMethodRepository($paymentMethodModel);
        $paymentMethods = $paymentMethodRepository->get($userId);

        $this->assertInstanceOf(Collection::class, $paymentMethods);
        $this->assertSame(5, $paymentMethods->count());

        $detailedPaymentMethod = $paymentMethods->first();
        $this->assertInstanceOf(PaymentGatewayDetailedPaymentMethod::class, $detailedPaymentMethod);
        $this->assertSame($paymentMethod->id, $detailedPaymentMethod->getId());
        $this->assertSame(
            $paymentMethod->payment_gateway_payment_method_id,
            $detailedPaymentMethod->getPaymentGatewayPaymentMethodId()
        );
    }

    public function testGetWithFiltersSuccess()
    {
        $userId = 123;
        $table = self::TABLE;
        $filters = [
            'recent' => true
        ];

        $generatePaymentMethod = function() use ($userId) {
            $paymentMethod = new StdClass;
            $paymentMethod->id = $this->faker->uuid();
            $paymentMethod->user_id = $userId;
            $paymentMethod->payment_gateway_payment_method_id = 'pm_foo_'.rand(1000,9999);
            $paymentMethod->payment_gateway_payment_method_type = 'card';
            $paymentMethod->payment_gateway = 1;
            return $paymentMethod;
        };

        $whereClause = [
            "$table.user_id" => $userId,
            "$table.deleted_at" => null
        ];
        $paymentMethod = $generatePaymentMethod();
        $returnedResult = Collection::make([
            $paymentMethod,  // an assertion is made for this first payment method.
            $generatePaymentMethod(),
            $generatePaymentMethod(),
            $generatePaymentMethod(),
            $generatePaymentMethod(),
        ]);

        $paymentMethodModel = $this->getPaymentMethodModelMock($whereClause, $returnedResult, true);

        $paymentMethodRepository = new PaymentMethodRepository($paymentMethodModel);
        $paymentMethods = $paymentMethodRepository->get(
            $userId,
            null,
            $filters
        );

        $this->assertInstanceOf(Collection::class, $paymentMethods);
        $this->assertSame(5, $paymentMethods->count());

        $detailedPaymentMethod = $paymentMethods->first();
        $this->assertInstanceOf(PaymentGatewayDetailedPaymentMethod::class, $detailedPaymentMethod);
        $this->assertSame($paymentMethod->id, $detailedPaymentMethod->getId());
        $this->assertSame(
            $paymentMethod->payment_gateway_payment_method_id,
            $detailedPaymentMethod->getPaymentGatewayPaymentMethodId()
        );
    }

    public function getPaymentMethodModelMock(
        $whereClause,
        $returnedResult,
        $filters = false
    ) {
        $table = self::TABLE;
        $paymentMethodModelMock = $this->getMockBuilder(PaymentGatewayPaymentMethod::class)
            ->setMethods([
                'get',
                'keyBy',
                'where',
                'select',
                'leftJoin',
                'when',
                'orderBy'
            ])
            ->getMock();
        $paymentMethodModelMock
            ->expects($this->once())
            ->method('select')
            ->with("$table.*")
            ->willReturnSelf();
        $paymentMethodModelMock
            ->expects($this->once())
            ->method('where')
            ->with($whereClause)
            ->willReturnSelf();
        $paymentMethodModelMock
            ->expects($this->once())
            ->method('when')
            ->with($filters)
            ->willReturnSelf();
        $paymentMethodModelMock
            ->expects($this->once())
            ->method('orderBy')
            ->with("$table.created_at", 'DESC')
            ->willReturnSelf();
        $paymentMethodModelMock
            ->expects($this->once())
            ->method('get')
            ->willReturnSelf();
        $paymentMethodModelMock
            ->expects($this->once())
            ->method('keyBy')
            ->with('id')
            ->willReturn($returnedResult);
        return $paymentMethodModelMock;
    }
}
