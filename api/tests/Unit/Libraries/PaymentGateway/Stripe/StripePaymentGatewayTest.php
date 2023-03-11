<?php

namespace Tests\Unit\Libraries\PaymentGateway\Stripe;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;
use Exception;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use StdClass;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\StripeClient;
use TestCase;

class StripePaymentGatewayTest extends TestCase
{
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    public function testGetCustomer()
    {
        $customerId = 'cus_foo';
        $customer = new StdClass;
        $customer->id = rand(10, 99);
        $customer->name = $this->faker->name();
        $customer->email = $this->faker->email();
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customer);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $detailedCustomer = $stripePaymentGateway->getCustomer($customerId);
        $this->assertInstanceOf(PaymentGatewayDetailedCustomer::class, $detailedCustomer);
    }

    /**
     * @dataProvider  exceptionData
     */
    public function testGetCustomerExceptions(Exception $exceptionThrown)
    {
        $this->expectException(PaymentGatewayException::class);

        $customerId = 'cus_foo';
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willThrowException($exceptionThrown);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient, true);
        $stripePaymentGateway->getCustomer($customerId);
    }

    public function testGetPaymentMethod()
    {
        $paymentMethodId = 'pm_foo';

        $paymentMethod = new StdClass;
        $paymentMethod->id = rand(10, 99);
        $paymentMethod->type = 1;

        $paymentMethod->billing_details = new StdClass;
        $paymentMethod->billing_details->name = $this->faker->name();
        $paymentMethod->billing_details->email = $this->faker->email();
        $paymentMethod->billing_details->phone = $this->faker->e164PhoneNumber();

        $paymentMethod->billing_details->address = new StdClass;
        $paymentMethod->billing_details->address->line1 = $this->faker->streetAddress();
        $paymentMethod->billing_details->address->line2 = $this->faker->secondaryAddress();
        $paymentMethod->billing_details->address->city = $this->faker->city();
        $paymentMethod->billing_details->address->state = $this->faker->state();
        $paymentMethod->billing_details->address->postal_code = $this->faker->postcode();
        $paymentMethod->billing_details->address->country = $this->faker->country();

        $paymentMethod->card = new StdClass;
        $paymentMethod->card->brand = 'visa';
        $paymentMethod->card->last4 = rand(1000, 9999);
        $paymentMethod->card->exp_month = rand(1, 12);
        $paymentMethod->card->exp_year = rand(2021, 2030);

        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('retrieve')
            ->with($paymentMethodId)
            ->willReturn($paymentMethod);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $detailedPaymentMethod = $stripePaymentGateway->getPaymentMethod($paymentMethodId);
        $this->assertInstanceOf(PaymentGatewayDetailedPaymentMethod::class, $detailedPaymentMethod);
    }

    /**
     * @dataProvider  exceptionData
     */
    public function testGetPaymentMethodExceptions(Exception $exceptionThrown)
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentMethodId = 'pm_foo';
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('retrieve')
            ->with($paymentMethodId)
            ->willThrowException($exceptionThrown);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient, true);
        $stripePaymentGateway->getPaymentMethod($paymentMethodId);
    }

    public function testGetCustomerPaymentMethods()
    {
        $customerId = 'cus_foo';
        $paymentMethodType = 'card';
        $filter = [
            'customer' => $customerId,
            'type' => $paymentMethodType,
        ];
        $paymentMethods = Collection::make([]);
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('all')
            ->with($filter)
            ->willReturn($paymentMethods);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $stripePaymentGateway->getCustomerPaymentMethods($customerId, $paymentMethodType);
    }

    /**
     * @dataProvider  exceptionData
     */
    public function testGetCustomerPaymentMethodsExceptions(Exception $exceptionThrown)
    {
        $this->expectException(PaymentGatewayException::class);

        $customerId = 'cus_foo';
        $paymentMethodType = 'card';
        $filter = [
            'customer' => $customerId,
            'type' => $paymentMethodType,
        ];
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('all')
            ->with($filter)
            ->willThrowException($exceptionThrown);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient, true);
        $stripePaymentGateway->getCustomerPaymentMethods($customerId, $paymentMethodType);
    }

    public function testCreateCustomer()
    {
        $userId = rand(10, 99);
        $name = $this->faker->name();
        $email = $this->faker->email();

        $customerData = [
            'name' => $name,
            'email' => $email,
            'description' => sprintf('Customer for User %d', $userId),
        ];

        $customer = new StdClass;
        $customer->id = 'cus_foo';

        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('create')
            ->with($customerData)
            ->willReturn($customer);

        $detailedCustomer = (new PaymentGatewayDetailedCustomer)
            ->setUserId($userId)
            ->setName($name)
            ->setEmail($email);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $detailedCustomer = $stripePaymentGateway->createCustomer($detailedCustomer);

        $this->assertInstanceOf(PaymentGatewayDetailedCustomer::class, $detailedCustomer);
    }

    /**
     * @dataProvider  exceptionData
     */
    public function testCreateCustomerExceptions(Exception $exceptionThrown)
    {
        $this->expectException(PaymentGatewayException::class);

        $userId = rand(10, 99);
        $name = $this->faker->name();
        $email = $this->faker->email();

        $customerData = [
            'name' => $name,
            'email' => $email,
            'description' => sprintf('Customer for User %d', $userId),
        ];

        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('create')
            ->with($customerData)
            ->willThrowException($exceptionThrown);

        $detailedCustomer = (new PaymentGatewayDetailedCustomer)
            ->setUserId($userId)
            ->setName($name)
            ->setEmail($email);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient, true);
        $detailedCustomer = $stripePaymentGateway->createCustomer($detailedCustomer);
    }

    public function testUpdateCustomerPaymentMethod()
    {
        // fixtures
        $paymentMethodId = 'pm_foo';
        $name = $this->faker->name();
        $email = $this->faker->email();
        $phone = $this->faker->e164PhoneNumber();
        $expireMonth = rand(1, 12);
        $expireYear = rand(2021, 2030);
        $addressLine1 = $this->faker->streetAddress();
        $addressLine2 = $this->faker->secondaryAddress();
        $city = $this->faker->city();
        $state = $this->faker->state();
        $postalCode = $this->faker->postcode();
        $country = $this->faker->country();

        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId)
            ->setAddressLine1($addressLine1)
            ->setAddressLine2($addressLine2)
            ->setCity($city)
            ->setState($state)
            ->setPostalCode($postalCode)
            ->setCountry($country)
            ->setDetails([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'expire_month' => $expireMonth,
                'expire_year' => $expireYear,
            ]);

        $paymentMethodData = [
            'card' => [
                'exp_month' => $expireMonth,
                'exp_year' => $expireYear,
            ],
            'billing_details' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => [
                    'line1' => $addressLine1,
                    'line2' => $addressLine2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postalCode,
                    'country' => $country,
                ]
            ]
        ];

        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('update')
            ->with($paymentMethodId, $paymentMethodData);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $stripePaymentGateway->updateCustomerPaymentMethod($detailedPaymentMethod);
    }

    public function testUpdateCustomerPaymentMethodNoUpdateData()
    {
        $paymentMethodId = 'pm_foo';
        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $paymentMethodData = [];

        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('update')
            ->with($paymentMethodId, $paymentMethodData);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $stripePaymentGateway->updateCustomerPaymentMethod($detailedPaymentMethod);
    }

    /**
     * @dataProvider  exceptionData
     */
    public function testUpdateCustomerPaymentMethodExceptions(Exception $exceptionThrown)
    {
        $this->expectException(PaymentGatewayException::class);

        // fixtures
        $paymentMethodId = 'pm_foo';
        $name = $this->faker->name();
        $email = $this->faker->email();
        $phone = $this->faker->e164PhoneNumber();
        $expireMonth = rand(1, 12);
        $expireYear = rand(2021, 2030);
        $addressLine1 = $this->faker->streetAddress();
        $addressLine2 = $this->faker->secondaryAddress();
        $city = $this->faker->city();
        $state = $this->faker->state();
        $postalCode = $this->faker->postcode();
        $country = $this->faker->country();

        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId)
            ->setAddressLine1($addressLine1)
            ->setAddressLine2($addressLine2)
            ->setCity($city)
            ->setState($state)
            ->setPostalCode($postalCode)
            ->setCountry($country)
            ->setDetails([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'expire_month' => $expireMonth,
                'expire_year' => $expireYear,
            ]);

        $paymentMethodData = [
            'card' => [
                'exp_month' => $expireMonth,
                'exp_year' => $expireYear,
            ],
            'billing_details' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => [
                    'line1' => $addressLine1,
                    'line2' => $addressLine2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postalCode,
                    'country' => $country,
                ]
            ]
        ];

        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('update')
            ->with($paymentMethodId, $paymentMethodData)
            ->willThrowException($exceptionThrown);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient, true);
        $stripePaymentGateway->updateCustomerPaymentMethod($detailedPaymentMethod);
    }

    public function testAttachCustomerPaymentMethod()
    {
        $customerId = 'cus_foo';
        $paymentMethodId = 'pm_foo';
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('attach')
            ->with($paymentMethodId, ['customer' => $customerId]);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $stripePaymentGateway->attachCustomerPaymentMethod($customerId, $paymentMethodId);
    }

    /**
     * @dataProvider  exceptionData
     */
    public function testAttachCustomerPaymentMethodExceptions(Exception $exceptionThrown)
    {
        $this->expectException(PaymentGatewayException::class);

        $customerId = 'cus_foo';
        $paymentMethodId = 'pm_foo';
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('attach')
            ->with($paymentMethodId, ['customer' => $customerId])
            ->willThrowException($exceptionThrown);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient, true);
        $stripePaymentGateway->attachCustomerPaymentMethod($customerId, $paymentMethodId);
    }

    public function testDetachCustomerPaymentMethod()
    {
        $paymentMethodId = 'pm_foo';
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('detach')
            ->with($paymentMethodId);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient);
        $stripePaymentGateway->detachCustomerPaymentMethod($paymentMethodId);
    }

    /**
     * @dataProvider  exceptionData
     */
    public function testDetachCustomerPaymentMethodExceptions(Exception $exceptionThrown)
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentMethodId = 'pm_foo';
        $stripeClient = $this->getStripeClientMock();
        $stripeClient
            ->expects($this->once())
            ->method('detach')
            ->with($paymentMethodId)
            ->willThrowException($exceptionThrown);
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($stripeClient, true);
        $stripePaymentGateway->detachCustomerPaymentMethod($paymentMethodId);
    }

    /**
     * Exceptions data provider.
     */
    public function exceptionData(): array
    {
        return [
            [ new ApiConnectionException ],
            [ new AuthenticationException ],
            [ new CardException ],
            [ new InvalidRequestException ],
            [ new RateLimitException ],
            [ new Exception ],
        ];
    }

    /**
     * @dataProvider  responseExceptionData
     */
    public function testGetResponseByException(
        Exception $exceptionThrown,
        int $exceptionStatus,
        bool $stripeThrownException = true
    ) {
        $stripePaymentGateway = $this->getStripePaymentGatewayMock($this->getStripeClientMock(), false, false);
        $response = $stripePaymentGateway->getResponseByException($exceptionThrown);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($exceptionStatus, $response->getStatusCode());

        $error = json_decode($response->getContent())->errors[0];
        $this->assertSame(Response::$statusTexts[$exceptionStatus], $error->type);
        $this->assertEquals(123, $error->code);
        $this->assertSame('foo', $error->message);

        if ($stripeThrownException) {
            $this->assertObjectHasAttribute('external_code', $error);
            $this->assertObjectHasAttribute('external_message', $error);
            $this->assertEquals(456, $error->external_code);
            $this->assertStringStartsWith('STRIPE: bar ', $error->external_message);
            $this->assertStringEndsWith(' baz', $error->external_message);
        }
    }

    /**
     * Exceptions data provider.
     */
    public function responseExceptionData(): array
    {
        $responseExceptionData = [];

        // fixture
        $error = new StdClass;
        $error->code = 456;
        $error->message = 'bar';
        $error->param = 'baz';

        // Stripe originated exception

        $chainedException = new ApiConnectionException('foo', 123);
        $exceptionStatus = Response::HTTP_REQUEST_TIMEOUT;
        $chainedException->setHttpStatus($exceptionStatus);
        $chainedException->setError($error);
        $exception = new PaymentGatewayException('foo', 123, $chainedException);
        $responseExceptionData[] = [ $exception, $exceptionStatus ];

        $chainedException = new AuthenticationException('foo', 123);
        $exceptionStatus = Response::HTTP_UNAUTHORIZED;
        $chainedException->setHttpStatus($exceptionStatus);
        $chainedException->setError($error);
        $exception = new PaymentGatewayException('foo', 123, $chainedException);
        $responseExceptionData[] = [ $exception, $exceptionStatus ];

        $chainedException = new CardException('foo', 123);
        $exceptionStatus = Response::HTTP_PAYMENT_REQUIRED;
        $chainedException->setHttpStatus($exceptionStatus);
        $chainedException->setError($error);
        $exception = new PaymentGatewayException('foo', 123, $chainedException);
        $responseExceptionData[] = [ $exception, $exceptionStatus ];

        $chainedException = new InvalidRequestException('foo', 123);
        $exceptionStatus = Response::HTTP_BAD_REQUEST;
        $chainedException->setHttpStatus($exceptionStatus);
        $chainedException->setError($error);
        $exception = new PaymentGatewayException('foo', 123, $chainedException);
        $responseExceptionData[] = [ $exception, $exceptionStatus ];

        $chainedException = new RateLimitException('foo', 123);
        $exceptionStatus = Response::HTTP_TOO_MANY_REQUESTS;
        $chainedException->setHttpStatus($exceptionStatus);
        $chainedException->setError($error);
        $exception = new PaymentGatewayException('foo', 123, $chainedException);
        $responseExceptionData[] = [ $exception, $exceptionStatus ];

        $chainedException = new InvalidRequestException('foobar', 789);
        $exceptionStatus = Response::HTTP_NOT_ACCEPTABLE;
        $chainedException->setHttpStatus($exceptionStatus);
        $chainedException->setError($error);
        $exception = new PaymentGatewayException('foo', 123, $chainedException);
        $responseExceptionData[] = [ $exception, $exceptionStatus ];

        // non-Stripe originated exception

        $exceptionStatus = Response::HTTP_UNPROCESSABLE_ENTITY;

        $exception = new PaymentGatewayException('foo', 123);
        $responseExceptionData[] = [ $exception, $exceptionStatus, false ];

        $chainedException = new Exception('foobar', 789);
        $exception = new PaymentGatewayException('foo', 123, $chainedException);
        $responseExceptionData[] = [ $exception, $exceptionStatus, false ];

        $exception = new Exception('foo', 123);
        $responseExceptionData[] = [ $exception, $exceptionStatus, false ];

        return $responseExceptionData;
    }

    public function getStripeClientMock()
    {
        $stripeClientMock = $this->getMockBuilder(StripeClient::class)
            ->setMethods([
                'all',
                'attach',
                'create',
                'detach',
                'retrieve',
                'update',
            ])
            ->getMock();
        $stripeClientMock->accounts = $stripeClientMock;
        $stripeClientMock->customers = $stripeClientMock;
        $stripeClientMock->paymentMethods = $stripeClientMock;
        return $stripeClientMock;
    }

    public function getStripePaymentGatewayMock(
        StripeClient $stripeClientMock,
        bool $exceptionThrown = false,
        bool $requestClient = true
    ) {
        $stripePaymentGatewayMock = $this->getMockBuilder(StripePaymentGateway::class)
            ->setMethods([
                'getClient',
                'classifyException',
            ])
            ->getMock();
        if ($requestClient) {
            $stripePaymentGatewayMock
                ->expects($this->once())
                ->method('getClient')
                ->willReturn($stripeClientMock);
        }
        if ($exceptionThrown) {
            $stripePaymentGatewayMock
                ->expects($this->once())
                ->method('classifyException')
                ->with($this->isInstanceOf(Exception::class))
                ->willReturn([
                    123,      // $exception->code()
                    'foobar', // $exception->message()
                ]);
        }
        return $stripePaymentGatewayMock;
    }
}
