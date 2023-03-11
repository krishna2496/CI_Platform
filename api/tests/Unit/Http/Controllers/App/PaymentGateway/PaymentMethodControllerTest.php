<?php

namespace Tests\Unit\Http\Controllers\App\PaymentGateway;

use App\Events\User\UserActivityLogEvent;
use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\App\PaymentGateway\PaymentMethodController;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Rules\CustomValidationRules;
use App\Services\PaymentGateway\CustomerService;
use App\Services\PaymentGateway\PaymentMethodService;
use App\Services\UserService;
use App\User;
use Exception;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use StdClass;
use TestCase;
use Validator;

class PaymentMethodControllerTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayInterface
     */
    private $paymentGateway;

    /**
     * @var App\User
     */
    private $userMock;

    /**
     * @var App\Services\UserService
     */
    private $userServiceMock;

    /**
     * @var App\Services\PaymentGateway\CustomerService
     */
    private $customerServiceMock;

    /**
     * @var App\Services\PaymentGateway\PaymentMethodService
     */
    private $paymentMethodServiceMock;

    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayFactory
     */
    private $paymentGatewayFactoryMock;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        CustomValidationRules::validate();  // load all custom validation
        $this->faker = FakerFactory::create();
        $this->paymentGateway = $this->createMock(PaymentGatewayInterface::class);
        $this->userMock = $this->createMock(User::class);
        $this->userMock->firstname = $this->faker->firstName();
        $this->userMock->lastname = $this->faker->lastName();
        $this->userMock->email = $this->faker->email();
        $this->userServiceMock = $this->createMock(UserService::class);
        $this->customerServiceMock = $this->createMock(CustomerService::class);
        $this->paymentMethodServiceMock = $this->createMock(PaymentMethodService::class);
        $this->paymentGatewayFactoryMock = $this->createMock(PaymentGatewayFactory::class);
        $this->paymentGatewayFactoryMock
            ->expects($this->once())
            ->method('getPaymentGateway')
            ->willReturn($this->paymentGateway);
        $this->responseHelper = new ResponseHelper;
        $this->request = new Request;
        $this->request->auth = new StdClass;
    }

    public function testGetSuccess()
    {
        $userId = rand(10, 99);
        $filters = [
            'recent' => false
        ];
        $this->request->auth->user_id = $userId;

        $paymentMethods = Collection::make([new PaymentGatewayDetailedPaymentMethod]);
        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(
                $userId,
                null,
                $filters
            )
            ->willReturn($paymentMethods);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->get($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);;
        $this->assertSame(trans('messages.success.MESSAGE_PAYMENT_METHOD_RETRIEVED'), $data['message']);


        $paymentMethod = $data['data']['payment_methods'][0];
        $this->assertArrayHasKey('id', $paymentMethod);
        $this->assertArrayHasKey('user_id', $paymentMethod);
        $this->assertArrayHasKey('payment_gateway_payment_method_id', $paymentMethod);
        $this->assertArrayHasKey('payment_gateway_payment_method_type', $paymentMethod);
        $this->assertArrayHasKey('payment_gateway', $paymentMethod);

        $this->assertArrayHasKey('billing', $paymentMethod);
        $this->assertArrayHasKey('address_line1', $paymentMethod['billing']);
        $this->assertArrayHasKey('address_line2', $paymentMethod['billing']);
        $this->assertArrayHasKey('city', $paymentMethod['billing']);
        $this->assertArrayHasKey('state', $paymentMethod['billing']);
        $this->assertArrayHasKey('postal_code', $paymentMethod['billing']);
        $this->assertArrayHasKey('country', $paymentMethod['billing']);

        $this->assertArrayHasKey('card', $paymentMethod);
        $this->assertArrayHasKey('name', $paymentMethod['card']);
        $this->assertArrayHasKey('email', $paymentMethod['card']);
        $this->assertArrayHasKey('phone', $paymentMethod['card']);
        $this->assertArrayHasKey('expire_month', $paymentMethod['card']);
        $this->assertArrayHasKey('expire_year', $paymentMethod['card']);
    }

    public function testGetByIdSuccess()
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();

        $this->request->auth->user_id = $userId;

        $paymentMethods = Collection::make([new PaymentGatewayDetailedPaymentMethod]);
        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willReturn($paymentMethods);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->getById($this->request, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame(trans('messages.success.MESSAGE_PAYMENT_METHOD_RETRIEVED'), $data['message']);

        $paymentMethod = $data['data']['payment_methods'][0];
        $this->assertArrayHasKey('id', $paymentMethod);
        $this->assertArrayHasKey('user_id', $paymentMethod);
        $this->assertArrayHasKey('payment_gateway_payment_method_id', $paymentMethod);
        $this->assertArrayHasKey('payment_gateway_payment_method_type', $paymentMethod);
        $this->assertArrayHasKey('payment_gateway', $paymentMethod);

        $this->assertArrayHasKey('billing', $paymentMethod);
        $this->assertArrayHasKey('address_line1', $paymentMethod['billing']);
        $this->assertArrayHasKey('address_line2', $paymentMethod['billing']);
        $this->assertArrayHasKey('city', $paymentMethod['billing']);
        $this->assertArrayHasKey('state', $paymentMethod['billing']);
        $this->assertArrayHasKey('postal_code', $paymentMethod['billing']);
        $this->assertArrayHasKey('country', $paymentMethod['billing']);

        $this->assertArrayHasKey('card', $paymentMethod);
        $this->assertArrayHasKey('name', $paymentMethod['card']);
        $this->assertArrayHasKey('email', $paymentMethod['card']);
        $this->assertArrayHasKey('phone', $paymentMethod['card']);
        $this->assertArrayHasKey('expire_month', $paymentMethod['card']);
        $this->assertArrayHasKey('expire_year', $paymentMethod['card']);
    }

    public function testGetPaymentMethodModelNotFound()
    {
        $userId = rand(10, 99);
        $this->request->auth->user_id = $userId;

        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($userId)
            ->willThrowException(new ModelNotFoundException);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->get($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $error = json_decode($response->getContent())->errors[0];
        $this->assertSame(Response::$statusTexts[Response::HTTP_NOT_FOUND], $error->type);
        $this->assertEquals(config('constants.error_codes.ERROR_PAYMENT_METHOD_NOT_FOUND'), $error->code);
        $this->assertSame(trans('messages.custom_error_message.MESSAGE_PAYMENT_METHOD_NOT_FOUND'), $error->message);
    }

    public function testGetByIdPaymentMethodModelNotFound()
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $this->request->auth->user_id = $userId;

        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('get')
            ->with($userId, $id)
            ->willThrowException(new ModelNotFoundException);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->getById($this->request, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $error = json_decode($response->getContent())->errors[0];
        $this->assertSame(Response::$statusTexts[Response::HTTP_NOT_FOUND], $error->type);
        $this->assertEquals(config('constants.error_codes.ERROR_PAYMENT_METHOD_NOT_FOUND'), $error->code);
        $this->assertSame(trans('messages.custom_error_message.MESSAGE_PAYMENT_METHOD_NOT_FOUND'), $error->message);
    }

    public function testCreateNoCustomerYetSuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $userId = rand(10, 99);
        $paymentMethodId = 'pm_'.base_convert(rand(1e12, 1e14), 10, 36);

        $this->request->auth->user_id = $userId;

        $this->request->query->add([
            'payment_gateway_payment_method_id' => $paymentMethodId,
            'payment_gateway' => 'STRIPE',
        ]);


        $this->userServiceMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->userMock);

        $collection = Collection::make([]);
        $this->customerServiceMock
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new ModelNotFoundException);

        $customerId = 'cus_foo';
        $detailedCustomer = (new PaymentGatewayDetailedCustomer)
            ->setUserId($userId)
            ->setPaymentGatewayCustomerId($customerId);
        $this->paymentGateway
            ->expects($this->once())
            ->method('createCustomer')
            ->willReturn($detailedCustomer);

        $this->customerServiceMock
            ->expects($this->once())
            ->method('create')
            ->with($detailedCustomer)
            ->willReturn($detailedCustomer);

        $this->paymentGateway
            ->expects($this->once())
            ->method('attachCustomerPaymentMethod')
            ->with($customerId, $paymentMethodId)
            ->willReturn($detailedCustomer);

        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setUserId($userId)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('create')
            ->with($detailedPaymentMethod);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->create($this->request);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent());
        $this->assertSame(trans('messages.success.MESSAGE_PAYMENT_METHOD_CREATED'), $data->message);
    }

    public function testCreateWithExistingCustomerSuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $userId = rand(10, 99);
        $paymentMethodId = 'pm_'.base_convert(rand(1e12, 1e14), 10, 36);

        $this->request->auth->user_id = $userId;

        $this->request->query->add([
            'payment_gateway_payment_method_id' => $paymentMethodId,
            'payment_gateway' => 'STRIPE',
        ]);

        $this->userServiceMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn($this->userMock);

        $customerId = 'cus_foo';
        $detailedCustomer = (new PaymentGatewayDetailedCustomer)
            ->setUserId($userId)
            ->setPaymentGatewayCustomerId($customerId);
        $collection = Collection::make([$detailedCustomer]);
        $this->customerServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($collection);

        $this->paymentGateway
            ->expects($this->never())
            ->method('createCustomer');

        $this->customerServiceMock
            ->expects($this->never())
            ->method('create');

        $this->paymentGateway
            ->expects($this->once())
            ->method('attachCustomerPaymentMethod')
            ->with($customerId, $paymentMethodId)
            ->willReturn($detailedCustomer);

        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setUserId($userId)
            ->setPaymentGatewayPaymentMethodId($paymentMethodId);
        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('create')
            ->with($detailedPaymentMethod);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->create($this->request);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent());
        $this->assertSame(trans('messages.success.MESSAGE_PAYMENT_METHOD_CREATED'), $data->message);
    }

    /**
     * @dataProvider  createValidationData
     */
    public function testCreateValidationError(array $requestPayload, array $validationErrors)
    {
        $userId = rand(10, 99);
        $this->request->auth->user_id = $userId;

        if (count($requestPayload)) {
            $this->request->query->add($requestPayload);
        }

        $expectedErrorMessage = implode(' ', $validationErrors);


        $this->userServiceMock
            ->expects($this->never())
            ->method('findById');

        $this->customerServiceMock
            ->expects($this->never())
            ->method('get');

        $this->paymentMethodServiceMock
            ->expects($this->never())
            ->method('get');

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->create($this->request);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $error = json_decode($response->getContent())->errors[0];
        $this->assertSame(Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY], $error->type);
        $this->assertEquals(config('constants.error_codes.ERROR_PAYMENT_METHOD_INVALID_DATA'), $error->code);
        $this->assertSame($expectedErrorMessage, $error->message);
    }

    /**
     * Update validation data provider.
     */
    public function createValidationData(): array
    {
        return [
            [
                [],  // empty request. nothing passed.
                [
                    'The payment gateway payment method id field is required.',
                    'The payment gateway field is required.',
                ],
            ],
            [
                [
                    'payment_gateway_payment_method_id' => 'pm_foo',
                    'payment_gateway' => 'STRIPE',
                ],
                [
                    'The payment gateway payment method id must be at least 7 characters.',
                ],
            ],
            [
                [
                    'payment_gateway_payment_method_id' => 'payment_method_id_with_different_prefix',
                    'payment_gateway' => 'STRIPE',
                ],
                [
                    'The payment gateway payment method id field must start with "pm_".',
                ],
            ],
        ];
    }

    public function testCreatePaymentGatewayException()
    {
        $userId = rand(10, 99);
        $paymentMethodId = 'pm_'.base_convert(rand(1e12, 1e14), 10, 36);

        $this->request->auth->user_id = $userId;

        $this->request->query->add([
            'payment_gateway_payment_method_id' => $paymentMethodId,
            'payment_gateway' => 'STRIPE',
        ]);

        $this->userServiceMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn($this->userMock);

        $customerId = 'cus_foo';
        $detailedCustomer = (new PaymentGatewayDetailedCustomer)
            ->setUserId($userId)
            ->setPaymentGatewayCustomerId($customerId);
        $collection = Collection::make([$detailedCustomer]);
        $this->customerServiceMock
            ->expects($this->once())
            ->method('get')
            ->willReturn($collection);

        $this->paymentGateway
            ->expects($this->never())
            ->method('createCustomer');

        $this->customerServiceMock
            ->expects($this->never())
            ->method('create');

        $paymentGatewayException = (new PaymentGatewayException)
            ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));
        $this->paymentGateway
            ->expects($this->once())
            ->method('attachCustomerPaymentMethod')
            ->with($customerId, $paymentMethodId)
            ->willThrowException($paymentGatewayException);

        $this->paymentMethodServiceMock
            ->expects($this->never())
            ->method('create');

        $paymentGatewayErrorResponse = $this->responseHelper->error(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            config('constants.error_codes.ERROR_PAYMENT_GATEWAY_UNKNOWN_FAILURE'),
            trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_UNKNOWN_FAILURE')
        );
        $this->paymentGateway
            ->expects($this->once())
            ->method('getResponseByException')
            ->with($paymentGatewayException)
            ->willReturn($paymentGatewayErrorResponse);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->create($this->request);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $error = json_decode($response->getContent())->errors[0];
        $this->assertSame(Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY], $error->type);
        $this->assertEquals(config('constants.error_codes.ERROR_PAYMENT_GATEWAY_UNKNOWN_FAILURE'), $error->code);
        $this->assertSame(trans('messages.custom_error_message.MESSAGE_PAYMENT_GATEWAY_UNKNOWN_FAILURE'), $error->message);
    }

    public function testUpdateSuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $userId = rand(10, 99);
        $id = $this->faker->uuid();

        $this->request->auth->user_id = $userId;

        $payload = [
            'billing' => [
                'address_line1' => $this->faker->address(),
                'address_line2' => $this->faker->secondaryAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->countryCode(),
            ],
            'card' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'phone' => $this->faker->e164PhoneNumber(),
                'expire_month' => rand(1, 12),
                'expire_year' => rand((int) date('Y'), 2030),
            ],
        ];
        $this->request->query->add($payload);

        $input = json_decode(json_encode($payload));
        $detailedPaymentMethod = (new PaymentGatewayDetailedPaymentMethod)
            ->setId($id)
            ->setUserId($userId)
            ->setAddressLine1($input->billing->address_line1)
            ->setAddressLine2($input->billing->address_line2)
            ->setCity($input->billing->city)
            ->setState($input->billing->state)
            ->setPostalCode($input->billing->postal_code)
            ->setCountry($input->billing->country)
            ->setDetails([
                'name' => $input->card->name,
                'email' => $input->card->email,
                'phone' => $input->card->phone,
                'expire_month' => $input->card->expire_month,
                'expire_year' => $input->card->expire_year,
            ]);

        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('update')
            ->with($detailedPaymentMethod);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->update($this->request, $id);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent());
        $this->assertSame(trans('messages.success.MESSAGE_PAYMENT_METHOD_UPDATED'), $data->message);
    }

    /**
     * @dataProvider  updateValidationData
     */
    public function testUpdateValidationError(array $requestPayload, array $validationErrors)
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $this->request->auth->user_id = $userId;

        if (count($requestPayload)) {
            $this->request->query->add($requestPayload);
        }

        $expectedErrorMessage = implode(' ', $validationErrors);


        $this->customerServiceMock
            ->expects($this->never())
            ->method('get');

        $this->paymentMethodServiceMock
            ->expects($this->never())
            ->method('get');

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->update($this->request, $id);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $error = json_decode($response->getContent())->errors[0];
        $this->assertSame(Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY], $error->type);
        $this->assertEquals(config('constants.error_codes.ERROR_PAYMENT_METHOD_INVALID_DATA'), $error->code);
        $this->assertSame($expectedErrorMessage, $error->message);
    }

    /**
     * Update validation data provider.
     */
    public function updateValidationData(): array
    {
        $faker = FakerFactory::create();
        return [
            [
                [], [ 'Nothing to update.' ], // empty request. nothing passed.
            ],
            [
                [
                    'card' => [
                        'name' => 'x',
                    ],
                ],
                [ 'The card.name must be at least 2 characters.' ],
            ],
            [
                [
                    'card' => [
                        'name' => 'xx',
                        'email' => 'foo',
                    ],
                ],
                [ 'The card.email must be a valid email address.' ],
            ],
            [
                [
                    'card' => [
                        'expire_month' => 2,
                    ],
                ],
                [ 'The card.expire year field is required when card.expire month is present.' ],
            ],
            [
                [
                    'card' => [
                        'expire_year' => (int) date('Y'),
                    ],
                ],
                [ 'The card.expire month field is required when card.expire year is present.' ],
            ],
            [
                [
                    'card' => [
                        'email' => 'foo@bar.com',
                    ],
                ],
                [ 'The card.name field is required when card.email / card.phone / billing.address line1 / billing.address line2 / billing.city / billing.state / billing.postal code / billing.country is present.' ],
            ],
            [
                [
                    'card' => [
                        'expire_month' => 13,
                        'expire_year' => (int) date('Y'),
                    ],
                ],
                [ 'The card.expire month field must have a value between 1 and 12.' ],
            ],
            [
                [
                    'card' => [
                        'expire_month' => 9,
                        'expire_year' => 2019,
                    ],
                ],
                [ sprintf('The card.expire year field must have a value between %s and 2099.', date('Y')) ],
            ],
            [
                [
                    'billing' => [
                        'address_line1' => $faker->address(),
                        'address_line2' => $faker->secondaryAddress(),
                        'city' => $faker->city(),
                        'state' => $faker->state(),
                        'postal_code' => $faker->postcode(),
                        'country' => $faker->countryCode(),
                    ],
                ],
                [ 'The card.name field is required when card.email / card.phone / billing.address line1 / billing.address line2 / billing.city / billing.state / billing.postal code / billing.country is present.' ],
            ],
            [
                [
                    'billing' => [
                        'address_line1' => $faker->address(),
                        'address_line2' => $faker->secondaryAddress(),
                        'city' => $faker->city(),
                        'state' => $faker->state(),
                    ],
                    'card' => [
                        'name' => 'foo',
                    ],
                ],
                [ 'The billing.country field is required when billing.address line1 / billing.address line2 / billing.city / billing.state / billing.postal code is present.' ],
            ],
            [
                [
                    'billing' => [
                        'address_line1' => $faker->address(),
                        'address_line2' => $faker->secondaryAddress(),
                        'country' => $faker->countryCode(),
                    ],
                    'card' => [
                        'name' => 'foo',
                    ],
                ],
                [ 'The billing.city field is required when billing.address line1 / billing.state / billing.postal code / billing.country is present.' ],
            ],
            [
                [
                    'billing' => [
                        'city' => $faker->city(),
                        'country' => $faker->countryCode(),
                    ],
                    'card' => [
                        'name' => 'foo',
                    ],
                ],
                [ 'The billing.address line1 field is required when billing.address line2 / billing.city / billing.state / billing.postal code / billing.country is present.' ],
            ],
        ];
    }

    public function testDeleteNotFound()
    {
        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $this->request->auth->user_id = $userId;

        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('delete')
            ->with($userId, $id)
            ->willThrowException(new ModelNotFoundException);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->delete($this->request, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $error = json_decode($response->getContent())->errors[0];
        $this->assertSame(Response::$statusTexts[Response::HTTP_NOT_FOUND], $error->type);
        $this->assertEquals(config('constants.error_codes.ERROR_PAYMENT_METHOD_NOT_FOUND'), $error->code);
        $this->assertSame(trans('messages.custom_error_message.MESSAGE_PAYMENT_METHOD_NOT_FOUND'), $error->message);
    }

    public function testDeleteSuccess()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $userId = rand(10, 99);
        $id = $this->faker->uuid();
        $this->request->auth->user_id = $userId;

        $this->paymentMethodServiceMock
            ->expects($this->once())
            ->method('delete')
            ->with($userId, $id);

        $paymentMethodController = $this->getPaymentMethodController();
        $response = $paymentMethodController->delete($this->request, $id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $message = json_decode($response->getContent())->message;
        $this->assertSame(trans('messages.success.MESSAGE_PAYMENT_METHOD_DELETED'), $message);
    }

    public function getPaymentMethodController()
    {
        $paymentMethodController = new PaymentMethodController(
            $this->userServiceMock,
            $this->customerServiceMock,
            $this->paymentMethodServiceMock,
            $this->paymentGatewayFactoryMock,
            $this->responseHelper
        );
        return $paymentMethodController;
    }
}
