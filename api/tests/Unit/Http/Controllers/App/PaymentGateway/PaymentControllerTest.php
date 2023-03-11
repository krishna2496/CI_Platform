<?php

namespace Tests\Unit\Http\Controllers\App\PaymentGateway;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\App\PaymentGateway\PaymentController;
use App\Http\Controllers\Controller;
use App\Libraries\Amount;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment as DetailedPayment;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;
use App\Models\Donation;
use App\Models\Mission;
use App\Models\Organization;
use App\Models\PaymentGateway\Payment;
use App\Models\PaymentGateway\PaymentGatewayAccount;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\Organization\OrganizationRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Repositories\User\UserRepository;
use App\Services\Donation\DonationService;
use App\Services\PaymentGateway\CustomerService;
use App\Services\PaymentGateway\PaymentMethodService;
use App\Services\PaymentGateway\PaymentService;
use App\Traits\RestExceptionHandlerTrait;
use App\User;
use Exception;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Mockery;
use StdClass;
use TestCase;
use Torann\GeoIP\Facades\GeoIP;
use Torann\GeoIP\Location as GeoLocation;
use Validator;

class PaymentControllerTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayFactory
     */
    private $paymentGatewayFactory;

    /**
     * @var App\Services\PaymentGateway\PaymentService
     */
    private $paymentService;

    /**
     * @var App\Service\PaymentGateway\CustomerService
     */
    private $customerService;

    /**
     * @var App\Service\PaymentGateway\PaymentMethodService
     */
    private $paymentMethodService;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Repositories\Organization\OrganizationRepository
     */
    private $organizationRepository;

    /**
     * @var App\Services\Donation\DonationService
     */
    private $donationService;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Http\Controllers\App\PaymentGateway\PaymentController
     */
    private $paymentController;

    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayInterface
     */
    private $paymentGateway;

    /**
     * @var Faker\Factory
     */
    private $faker;

    public function setup(): void
    {
        parent::setUp();

        $location = (new GeoLocation())
            ->setAttribute('iso_code', 'PH');
        GeoIP::shouldReceive('getLocation')->andReturn($location);
        $this->faker = FakerFactory::create();
        $this->paymentGatewayFactory = $this->mock(PaymentGatewayFactory::class);
        $this->paymentService = $this->mock(PaymentService::class);
        $this->customerService = $this->mock(CustomerService::class);
        $this->paymentMethodService = $this->mock(PaymentMethodService::class);
        $this->tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $this->missionRepository = $this->mock(MissionRepository::class);
        $this->userRepository = $this->mock(UserRepository::class);
        $this->organizationRepository = $this->mock(OrganizationRepository::class);
        $this->donationService = $this->mock(DonationService::class);
        $this->helpers = $this->mock(Helpers::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->paymentGateway = $this->mock(PaymentGatewayInterface::class);

        $this->paymentController = new PaymentController(
            $this->paymentGatewayFactory,
            $this->paymentService,
            $this->customerService,
            $this->paymentMethodService,
            $this->tenantActivatedSettingRepository,
            $this->missionRepository,
            $this->userRepository,
            $this->organizationRepository,
            $this->donationService,
            $this->helpers,
            $this->responseHelper
        );
    }

    /**
     * @testdox Payment with customer and without existing payment method
     */
    public function testStoreWithoutPaymentMethod()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = $this->getUser();
        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->with($request->auth->user_id)
            ->andReturn($user);

        $customer = $this->getCustomer();
        $this->customerService
            ->shouldReceive('get')
            ->with($request->auth->user_id)
            ->andReturn($customer);

        $organizationId = $this->faker->uuid;
        $mission = $this->getMissionWithOrganization($organizationId);
        $this->missionRepository
            ->shouldReceive('find')
            ->with($request->input('mission_id'))
            ->andReturn($mission);

        $this->missionRepository
            ->shouldReceive('isEligibleForDonation')
            ->with($request, $mission->mission_id)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('getOrganizationDetails')
            ->with($organizationId)
            ->andReturn($mission->organization);

        $tenant = new StdClass;
        $tenant->sponsor_id = 1;
        $tenant->tenant_id = 1;
        $this->helpers
            ->shouldReceive('getTenantIdAndSponsorIdFromRequest')
            ->with($request)
            ->andReturn($tenant);

        extract($request->all());
        $payment = $this->getPayment([
            'payment_gateway' => $payment_gateway,
            'payment_method_type' => $payment_method_type,
            'currency' => $currency,
            'payment_gateway_account_id' => 'ac_Hb7bm8kfzpeqF',
            'billing' => $billing,
            'email' => $user->email
        ]);
        if ($hundredPercentSetting) {
            $payment->setAttribute('cover_fee', $cover_fee);
        }

        $detailedPayment = (new DetailedPayment())
            ->setUserId($request->auth->user_id)
            ->setAmountDonated($request->input('amount'))
            ->setMissionId($request->input('mission_id'))
            ->setOrganizationId($this->faker->uuid)
            ->setTenantId($tenant->tenant_id)
            ->setHundredPercentSetting($hundredPercentSetting)
            ->setCustomerId($customer->first()->getPaymentGatewayCustomerId())
            ->setPayment($payment);

        $chargeAmount = 105.53;
        $transferAmount = 100;
        $payment->setAttribute('amount', $chargeAmount);
        $payment->setAttribute('transfer_amount', $transferAmount);
        $detailedPayment->setPayment($payment);

        $this->paymentGateway
            ->shouldReceive('computeChargesAndFees')
            ->with(Mockery::type(DetailedPayment::class))
            ->once()
            ->andReturn($detailedPayment);

        $paymentIntentId = 'pi_test_12345678';
        $clientSecret = 'pi_test_12345678_secret_654321';
        $detailedPayment
            ->setPaymentGatewayPaymentId($paymentIntentId)
            ->setClientSecret($clientSecret);
        $this->paymentGateway
            ->shouldReceive('createPayment')
            ->with(Mockery::type(DetailedPayment::class))
            ->once()
            ->andReturn($detailedPayment);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1) // stripe
            ->once()
            ->andReturn($this->paymentGateway);

        $paymentId = $this->faker->uuid;
        $this->paymentService
            ->shouldReceive('create')
            ->with(Mockery::type(Payment::class))
            ->once()
            ->andReturn($payment->setAttribute('id', $paymentId));

        $donation = (new Donation())
            ->setAttribute('mission_id', $mission->mission_id)
            ->setAttribute('payment_id', $paymentId)
            ->setAttribute('organization_id', $organizationId)
            ->setAttribute('user_id', $request->auth->user_id);
        $this->donationService
            ->shouldReceive('create')
            ->with(Mockery::type(Donation::class))
            ->once()
            ->andReturn($donation->setAttribute('id', $this->faker->uuid));

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_SUCCESSFULLY_CREATED'),
                [
                    'id' => $payment->id,
                    'client_secret' => $detailedPayment->getClientSecret()
                ]
            );

        $response = $this->paymentController->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Payment with customer record and active payment method
     */
    public function testStoreWithPaymentMethod()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true,
            'payment_method_id' => $this->faker->uuid
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = $this->getUser();
        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->with($request->auth->user_id)
            ->andReturn($user);

        $paymentMethodCollection = $this->getPaymentGatewayDetailedPaymentMethod();
        $paymentMethod = $paymentMethodCollection->first();
        $this->paymentMethodService
            ->shouldReceive('get')
            ->with(
                $request->auth->user_id,
                $request->input('payment_method_id')
            )
            ->andReturn($paymentMethodCollection);

        $customer = $this->getCustomer();
        $this->customerService
            ->shouldReceive('get')
            ->with($request->auth->user_id)
            ->andReturn($customer);

        $organizationId = $this->faker->uuid;
        $mission = $this->getMissionWithOrganization($organizationId);
        $this->missionRepository
            ->shouldReceive('find')
            ->with($request->input('mission_id'))
            ->andReturn($mission);

        $this->missionRepository
            ->shouldReceive('isEligibleForDonation')
            ->with($request, $mission->mission_id)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('getOrganizationDetails')
            ->with($organizationId)
            ->andReturn($mission->organization);

        $tenant = new StdClass;
        $tenant->sponsor_id = 1;
        $tenant->tenant_id = 1;
        $this->helpers
            ->shouldReceive('getTenantIdAndSponsorIdFromRequest')
            ->with($request)
            ->andReturn($tenant);

        $payment = $this->getPayment([
            'payment_gateway' => $paymentMethod->getPaymentGateway(),
            'payment_method_type' => $paymentMethod->getPaymentGatewayPaymentMethodType(),
            'currency' => $request->input('currency'),
            'payment_gateway_account_id' => 'ac_Hb7bm8kfzpeqF',
            'email' => $paymentMethod->getDetails()['email'],
            'billing' => [
                'name' => $paymentMethod->getDetails()['name'],
                'phone' => $paymentMethod->getDetails()['phone'],
                'address_line_1' => $paymentMethod->getAddressLine1(),
                'address_line_2' => $paymentMethod->getAddressLine2(),
                'city' => $paymentMethod->getCity(),
                'state' => $paymentMethod->getState(),
                'country' => $paymentMethod->getCountry(),
                'postal_code' => $paymentMethod->getPostalCode()
            ]
        ]);

        $detailedPayment = (new DetailedPayment())
            ->setUserId($request->auth->user_id)
            ->setAmountDonated($request->input('amount'))
            ->setMissionId($mission->mission_id)
            ->setOrganizationId($this->faker->uuid)
            ->setTenantId($tenant->tenant_id)
            ->setHundredPercentSetting($hundredPercentSetting)
            ->setCustomerId($customer->first()->getPaymentGatewayCustomerId())
            ->setPayment($payment);

        $chargeAmount = 105.53;
        $transferAmount = 100;
        $payment->setAttribute('amount', $chargeAmount);
        $payment->setAttribute('transfer_amount', $transferAmount);
        $detailedPayment->setPayment($payment);

        $this->paymentGateway
            ->shouldReceive('computeChargesAndFees')
            ->with(Mockery::type(DetailedPayment::class))
            ->andReturn($detailedPayment);

        $paymentIntentId = 'pi_test_12345678';
        $clientSecret = 'pi_test_12345678_secret_654321';
        $detailedPayment
            ->setPaymentGatewayPaymentId($paymentIntentId)
            ->setClientSecret($clientSecret);
        $this->paymentGateway
            ->shouldReceive('createPayment')
            ->with(Mockery::type(DetailedPayment::class))
            ->andReturn($detailedPayment);

        $paymentId = $this->faker->uuid;
        $this->paymentService
            ->shouldReceive('create')
            ->andReturn($payment->setAttribute('id', $paymentId));

        $donation = (new Donation())
            ->setAttribute('mission_id', $mission->mission_id)
            ->setAttribute('payment_id', $paymentId)
            ->setAttribute('organization_id', $organizationId)
            ->setAttribute('user_id', $request->auth->user_id);

        $this->donationService
            ->shouldReceive('create')
            ->with(Mockery::type(Donation::class))
            ->once()
            ->andReturn($donation->setAttribute('id', $this->faker->uuid));

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1) // stripe
            ->andReturn($this->paymentGateway);

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_SUCCESSFULLY_CREATED'),
                [
                    'id' => $payment->id,
                    'client_secret' => $detailedPayment->getClientSecret()
                ]
            );

        $response = $this->paymentController->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Payment using and invalid payment method
     */
    public function testStoreWithInvalidPaymentMethod()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true,
            'payment_method_id' => 'invalid-payment-method-id'
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->with($request->auth->user_id)
            ->andReturn($this->getUser());

        $customer = $this->getCustomer();
        $this->customerService
            ->shouldReceive('get')
            ->with($request->auth->user_id)
            ->andReturn($customer);

        $organizationId = $this->faker->uuid;
        $mission = $this->getMissionWithOrganization($organizationId);
        $this->missionRepository
            ->shouldReceive('find')
            ->with($request->input('mission_id'))
            ->andReturn($mission);

        $this->missionRepository
            ->shouldReceive('isEligibleForDonation')
            ->with($request, $mission->mission_id)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('getOrganizationDetails')
            ->with($organizationId)
            ->andReturn($mission->organization);

        $this->paymentMethodService
            ->shouldReceive('get')
            ->once()
            ->with(
                $request->auth->user_id,
                $request->input('payment_method_id')
            )
            ->andThrow(new ModelNotFoundException);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->never();

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_PAYMENT_METHOD_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_PAYMENT_METHOD_NOT_FOUND')
            );

        $response = $this->paymentController->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Payment without existing customer record and payment method
     */
    public function testStoreWithoutCustomer()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = $this->getUser();
        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->with($request->auth->user_id)
            ->andReturn($user);

        $this->paymentGateway
            ->shouldReceive('getType')
            ->andReturn(1); // STRIPE

        $customer = $this->getCustomer();
        $this->customerService
            ->shouldReceive('get')
            ->with($request->auth->user_id)
            ->andThrow(new ModelNotFoundException);

        $this->paymentGateway
            ->shouldReceive('createCustomer')
            ->with(Mockery::type(PaymentGatewayDetailedCustomer::class))
            ->andReturn($customer->first());

        $this->customerService
            ->shouldReceive('create')
            ->with(Mockery::type(PaymentGatewayDetailedCustomer::class))
            ->andReturn($customer->first());

        $organizationId = $this->faker->uuid;
        $mission = $this->getMissionWithOrganization($organizationId);
        $this->missionRepository
            ->shouldReceive('find')
            ->with($request->input('mission_id'))
            ->andReturn($mission);

        $this->missionRepository
            ->shouldReceive('isEligibleForDonation')
            ->with($request, $mission->mission_id)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('getOrganizationDetails')
            ->with($organizationId)
            ->andReturn($mission->organization);

        $tenant = new StdClass;
        $tenant->sponsor_id = 1;
        $tenant->tenant_id = 1;
        $this->helpers
            ->shouldReceive('getTenantIdAndSponsorIdFromRequest')
            ->with($request)
            ->andReturn($tenant);

        extract($request->all());
        $payment = $this->getPayment([
            'payment_gateway' => $payment_gateway,
            'payment_method_type' => $payment_method_type,
            'currency' => $currency,
            'payment_gateway_account_id' => 'ac_Hb7bm8kfzpeqF',
            'billing' => $billing,
            'email' => $user->email
        ]);
        if ($hundredPercentSetting) {
            $payment->setAttribute('cover_fee', $cover_fee);
        }

        $detailedPayment = (new DetailedPayment())
            ->setUserId($request->auth->user_id)
            ->setAmountDonated($request->input('amount'))
            ->setMissionId($request->input('mission_id'))
            ->setOrganizationId($this->faker->uuid)
            ->setTenantId($tenant->tenant_id)
            ->setHundredPercentSetting($hundredPercentSetting)
            ->setCustomerId($customer->first()->getPaymentGatewayCustomerId())
            ->setPayment($payment);

        $chargeAmount = 105.53;
        $transferAmount = 100;
        $payment->setAttribute('amount', $chargeAmount);
        $payment->setAttribute('transfer_amount', $transferAmount);
        $detailedPayment->setPayment($payment);

        $this->paymentGateway
            ->shouldReceive('computeChargesAndFees')
            ->with(Mockery::type(DetailedPayment::class))
            ->once()
            ->andReturn($detailedPayment);

        $paymentIntentId = 'pi_test_12345678';
        $clientSecret = 'pi_test_12345678_secret_654321';
        $detailedPayment
            ->setPaymentGatewayPaymentId($paymentIntentId)
            ->setClientSecret($clientSecret);
        $this->paymentGateway
            ->shouldReceive('createPayment')
            ->with(Mockery::type(DetailedPayment::class))
            ->once()
            ->andReturn($detailedPayment);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1) // stripe
            ->once()
            ->andReturn($this->paymentGateway);

        $paymentId = $this->faker->uuid;
        $this->paymentService
            ->shouldReceive('create')
            ->with(Mockery::type(Payment::class))
            ->once()
            ->andReturn($payment->setAttribute('id', $paymentId));

        $donation = (new Donation())
            ->setAttribute('mission_id', $mission->mission_id)
            ->setAttribute('payment_id', $paymentId)
            ->setAttribute('organization_id', $organizationId)
            ->setAttribute('user_id', $request->auth->user_id);
        $this->donationService
            ->shouldReceive('create')
            ->with(Mockery::type(Donation::class))
            ->once()
            ->andReturn($donation->setAttribute('id', $this->faker->uuid));

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_PAYMENT_SUCCESSFULLY_CREATED'),
                [
                    'id' => $payment->id,
                    'client_secret' => $detailedPayment->getClientSecret()
                ]
            );

        $response = $this->paymentController->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Payment without user
     */
    public function testStoreWithoutUser()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->with($request->auth->user_id)
            ->andThrow(new ModelNotFoundException);

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );

        $this->paymentController->store($request);
    }

    /**
     * @testdox Payment with mission's organization without payment gateway account
     */
    public function testStoreWithInvalidMissionOrganization()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = $this->getUser();
        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->with($request->auth->user_id)
            ->andReturn($user);

        $organizationId = $this->faker->uuid;
        $mission = $this->getMissionWithOrganization($organizationId, false);
        $this->missionRepository
            ->shouldReceive('find')
            ->with($request->input('mission_id'))
            ->andReturn($mission);

        $this->missionRepository
            ->shouldReceive('isEligibleForDonation')
            ->with($request, $mission->mission_id)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('getOrganizationDetails')
            ->with($organizationId)
            ->andReturn($mission->organization);

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_ORGANIZATION_DOES_NOT_SUPPORT_DONATION'),
                trans('messages.custom_error_message.ERROR_PAYMENT_ORGANIZATION_DOES_NOT_SUPPORT_DONATION')
            );

        $response = $this->paymentController->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Payment with mission not eligible for donation
     */
    public function testStoreWithMissionNotEligibleForDonation()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $user = $this->getUser();
        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->with($request->auth->user_id)
            ->andReturn($user);

        $organizationId = $this->faker->uuid;
        $mission = $this->getMissionWithOrganization($organizationId, false);
        $this->missionRepository
            ->shouldReceive('find')
            ->with($request->input('mission_id'))
            ->andReturn($mission);

        $this->missionRepository
            ->shouldReceive('isEligibleForDonation')
            ->with($request, $mission->mission_id)
            ->andReturn(false);

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_MISSION_NOT_ELIGIBLE_FOR_DONATION'),
                trans('messages.custom_error_message.ERROR_PAYMENT_MISSION_NOT_ELIGIBLE_FOR_DONATION')
            );

        $response = $this->paymentController->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Validation fails
     */
    public function testStoreValidationFails()
    {
        $request = $this->getRequest([
            'amount' => 100,
            'cover_fee' => true
        ]);

        $hundredPercentSetting = false;
        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->with(
                '100_percent_donation',
                $request
            )
            ->andReturn($hundredPercentSetting);

        $errors = new Collection([
            'sample-error message'
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_PAYMENT_DATA'),
                $errors->first()
            );

        $this->userRepository
            ->shouldReceive('findUserDetail')
            ->never();

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->never();

        $response = $this->paymentController->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Get request object
     * @param array $requestData
     *
     * @return Request $request
     */
    private function getRequest(array $requestData): Request
    {
        $request = new Request;
        $request->query->add([
            'currency' => 'EUR',
            'amount' => $requestData['amount'],
            'cover_fee' => $requestData['cover_fee'],
            'mission_id' => rand(1, 100)
        ]);

        if (isset($requestData['payment_method_id'])) {
            $request->query->add([
                'payment_method_id' => $requestData['payment_method_id']
            ]);
        } else {
            $request->query->add([
                'payment_gateway' => 'STRIPE',
                'payment_method_type' => 'CARD',
                'billing' => [
                    'name' => $this->faker->name,
                    'phone' => $this->faker->phoneNumber,
                    'address_line_1' => $this->faker->address,
                    'address_line_2' => $this->faker->secondaryAddress,
                    'city' => $this->faker->city,
                    'state' => $this->faker->state,
                    'country' => $this->faker->countryCode,
                    'postal_code' => $this->faker->postcode
                ],
            ]);
        }

        $request->auth = new StdClass;
        $request->auth->user_id = 1;

        return $request;
    }

    /**
     * Get Detailed PaymentGatewayPaymentMethod
     *
     * @return Collection
     */
    private function getPaymentGatewayDetailedPaymentMethod()
    {
        $paymentMethodCollection = Collection::make([]);
        $paymentGatewayDetailedPaymentMethod = new PaymentGatewayDetailedPaymentMethod();
        $paymentGatewayDetailedPaymentMethod
            ->setPaymentGateway(1) // STRIPE
            ->setPaymentGatewayPaymentMethodId('pm_1HXTpwBqyp6GnSrSHIXrRRm0')
            ->setPaymentGatewayPaymentMethodType('CARD')
            ->setAddressLine1($this->faker->address)
            ->setAddressLine2($this->faker->secondaryAddress)
            ->setCity($this->faker->city)
            ->setState($this->faker->state)
            ->setCountry($this->faker->countryCode)
            ->setPostalCode($this->faker->postcode)
            ->setDetails([
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'phone' => $this->faker->phoneNumber
            ]);

        $paymentMethodCollection->put(
            $this->faker->uuid,
            $paymentGatewayDetailedPaymentMethod
        );

        return $paymentMethodCollection;
    }

    /**
     * Get user model
     *
     * @return User
     */
    public function getUser()
    {
        return (new User())
            ->setAttribute('first_name', $this->faker->firstName)
            ->setAttribute('last_name', $this->faker->lastName)
            ->setAttribute('email', $this->faker->email);
    }

    /**
     * Gets customer collection
     *
     * @return Collection
     */
    private function getCustomer()
    {
        $customer = (new PaymentGatewayDetailedCustomer())
            ->setUserId(1)
            ->setPaymentGatewayCustomerId('cus_I7iz2eRfi02Oim')
            ->setPaymentGateway(1); // PaymentGatewayStripe
        $customerCollection = Collection::make([]);
        $customerCollection->put($this->faker->uuid, $customer);

        return $customerCollection;
    }

    /**
     * Gets payment object
     *
     * @param array $data
     *
     * @return Payment
     */
    private function getPayment(array $data)
    {
        $payment = (new Payment())
            ->setAttribute('payment_gateway', $data['payment_gateway'])
            ->setAttribute('payment_method_type', $data['payment_method_type'])
            ->setAttribute('currency', $data['currency'])
            ->setAttribute('payment_gateway_account_id', $data['payment_gateway_account_id'])
            ->setAttribute('billing_name', $data['billing']['name'])
            ->setAttribute('billing_email', $data['email'])
            ->setAttribute('billing_phone', $data['billing']['phone'])
            ->setAttribute('billing_address_line_1', $data['billing']['address_line_1'])
            ->setAttribute('billing_address_line_2', $data['billing']['address_line_2'])
            ->setAttribute('billing_city', $data['billing']['city'])
            ->setAttribute('billing_state', $data['billing']['state'])
            ->setAttribute('billing_country', $data['billing']['country'])
            ->setAttribute('billing_postal_code', $data['billing']['postal_code'])
            ->setAttribute('ip_address', $this->faker->ipv4)
            ->setAttribute('ip_address_country', GeoIP::getLocation($this->faker->ipv4)->iso_code);

        return $payment;
    }

    /**
     * Returns mission collection
     *
     * @param int $organizationId
     *
     * @return DBCollection
     */
    private function getMissionWithOrganization($organizationId, $withGatewayAccount = true)
    {
        $mission = new Mission();
        $organization = new Organization();
        $paymentGatewayAccount = $withGatewayAccount ? (new PaymentGatewayAccount())
            ->setAttribute('organization', $organizationId)
            ->setAttribute('payment_gateway_account_id', 'ac_Hb7bm8kfzpeqF') : null;

        $organization
            ->setAttribute('organization_id', $organizationId)
            ->setAttribute('paymentGatewayAccount', $paymentGatewayAccount);

        $mission->setAttribute('mission_id', 123);
        $mission->setAttribute('organization', $organization);
        $missionCollection = DBCollection::make([]);
        $missionCollection->put($this->faker->uuid, $mission);

        return $mission;
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