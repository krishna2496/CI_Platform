<?php

namespace Tests\Unit\Http\Controllers\Admin\Organization;

use App\Events\User\UserActivityLogEvent;
use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\Organization\OrganizationController;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;
use App\Models\Organization;
use App\Models\PaymentGateway\PaymentGatewayAccount;
use App\Repositories\Organization\OrganizationRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Services\PaymentGateway\AccountService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Mockery;
use TestCase;
use Validator;

class OrganizationControllerTest extends TestCase
{
    private $organization;
    private $responseHelper;
    private $organizationRepository;
    private $accountService;
    private $paymentGatewayFactory;
    private $tenantActivatedSettingRepository;
    private $organizationController;

    public function setUp(): void
    {
        parent::setUp();
        $this->organization = $this->mock(Organization::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->organizationRepository = $this->mock(OrganizationRepository::class);
        $this->accountService = $this->mock(AccountService::class);
        $this->paymentGatewayFactory = $this->mock(PaymentGatewayFactory::class);
        $this->tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);
        $request = new Request();
        $this->organizationController = new OrganizationController(
            $this->responseHelper,
            $this->organizationRepository,
            $this->accountService,
            $this->paymentGatewayFactory,
            $this->tenantActivatedSettingRepository,
            $request
        );
    }

    /**
     * Test index with success status
     *
     * @return void
     */
    public function testIndex()
    {
        $request = new Request();
        $organizations = $this->organizations(10);

        $paginator = $this->getPaginator(
            $organizations,
            $organizations->count(),
            10
        );

        $this->organizationRepository->shouldReceive('getOrganizationList')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $this->organization
            ->shouldReceive('setHidden')
            ->never();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_LISTING');

        $jsonResponse = response()->json([
            'status' => $apiStatus,
            'message' => $apiMessage,
            'data' => [],
        ], $apiStatus);

        $this->responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(
                $apiStatus,
                $apiMessage,
                $paginator,
                [],
                false
            )
            ->andReturn($jsonResponse);

        $response = $this->organizationController->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test index with success status
     *
     * @return void
     */
    public function testIndexoNoDonationSetting()
    {
        $request = new Request();
        $organizations = $this->organizations(10);

        $paginator = $this->getPaginator(
            $organizations,
            $organizations->count(),
            10
        );

        $this->organizationRepository->shouldReceive('getOrganizationList')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(false);

        $this->organization
            ->shouldReceive('setHidden')
            ->times($organizations->count())
            ->with(['paymentGatewayAccount']);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_LISTING');

        $jsonResponse = response()->json([
            'status' => $apiStatus,
            'message' => $apiMessage,
            'data' => [],
        ], $apiStatus);

        $this->responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(
                $apiStatus,
                $apiMessage,
                $paginator,
                [],
                false
            )
            ->andReturn($jsonResponse);

        $response = $this->organizationController->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test index with no result test
     *
     * @return void
     */
    public function testIndexNoResult()
    {
        $request = new Request();
        $collection = new Collection([]);
        $paginator = $this->getPaginator(
            $collection,
            $collection->count(),
            10
        );

        $this->organizationRepository->shouldReceive('getOrganizationList')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND');

        $this->responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(
                $apiStatus,
                $apiMessage,
                $paginator,
                [],
                false
            );

        $response = $this->organizationController->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test store method
     *
     * @return void
     */
    public function testStore()
    {
        $this->expectsEvents(UserActivityLogEvent::class);
        $organization = $this->organizations()->first();

        $request = new Request();
        $request->query->add([
            'payment_gateway_account' => [
                'payment_gateway' => 'STRIPE',
                'payment_gateway_account_id' => 2
            ]
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $params = $request->get('payment_gateway_account');

        $stripePaymentGateway = $this->mock(StripePaymentGateway::class);
        $paymentGatewayDetailedAccount = new PaymentGatewayDetailedAccount;
        $paymentGatewayDetailedAccount->setPayoutsEnabled(true);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1)
            ->andReturn($stripePaymentGateway);

        $stripePaymentGateway
            ->shouldReceive('getAccount')
            ->with($params['payment_gateway_account_id'])
            ->andReturn($paymentGatewayDetailedAccount);

        $this->organizationRepository->shouldReceive('store')
            ->once()
            ->with($request)
            ->andReturn($organization);

        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_CREATED');
        $apiData = ['organization_id' => $organization->organization_id];

        $this->responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData);

        $gatewayAccount = factory(PaymentGatewayAccount::class)->make([
            'organization_id' => $organization->organization_id,
            'payment_gateway_account_id' => $params['payment_gateway_account_id'],
            'payment_gateway' => $params['payment_gateway']
        ]);

        $this->accountService->shouldReceive('save')
            ->once()
            ->andReturn($gatewayAccount);

        $response = $this->organizationController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test store method with expected validation error
     *
     * @return void
     */
    public function testStoreValidation()
    {
        $request = new Request();
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

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ORGANIZATION_REQUIRED_FIELDS_EMPTY'),
                $errors->first()
            );

        $this->organizationRepository->shouldReceive('store')
            ->never();
        $response = $this->organizationController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test store method with payment gateway account that has payouts disabled (invalid)
     *
     * @return void
     */
    public function testStoreInvalidPaymentGatewayAccount()
    {
        $request = new Request();
        $request->query->add([
            'payment_gateway_account' => [
                'payment_gateway' => 'STRIPE',
                'payment_gateway_account_id' => 'invalid-account'
            ]
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $params = $request->get('payment_gateway_account');

        $stripePaymentGateway = $this->mock(StripePaymentGateway::class);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1)
            ->andReturn($stripePaymentGateway);

        $stripePaymentGateway
            ->shouldReceive('getAccount')
            ->with($params['payment_gateway_account_id'])
            ->andThrow(new PaymentGatewayException);

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                'Invalid payment gateway account id'
            );

        $this->organizationRepository->shouldReceive('store')
            ->never();

        $response = $this->organizationController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test store method with invalid payment gateway account
     *
     * @return void
     */
    public function testStorePaymentGatewayAccountPayoutsDisabled()
    {
        $request = new Request();
        $request->query->add([
            'payment_gateway_account' => [
                'payment_gateway' => 'STRIPE',
                'payment_gateway_account_id' => 2
            ]
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $params = $request->get('payment_gateway_account');

        $stripePaymentGateway = $this->mock(StripePaymentGateway::class);
        $paymentGatewayDetailedAccount = new PaymentGatewayDetailedAccount;
        $paymentGatewayDetailedAccount->setPayoutsEnabled(false);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1)
            ->andReturn($stripePaymentGateway);

        $stripePaymentGateway
            ->shouldReceive('getAccount')
            ->with($params['payment_gateway_account_id'])
            ->andReturn($paymentGatewayDetailedAccount);

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                'Account payouts is not enabled'
            );

        $this->organizationRepository->shouldReceive('store')
            ->never();

        $response = $this->organizationController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method
     *
     * @return void
     */
    public function testUpdateWithPaymentGateway()
    {
        $this->expectsEvents(UserActivityLogEvent::class);
        $organization = $this->organizations()->first();

        $request = new Request();
        $request->query->add([
            'name' => 'Foo Bar',
            'payment_gateway_account' => [
                'payment_gateway' => 'STRIPE',
                'payment_gateway_account_id' => 2
            ]
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $params = $request->get('payment_gateway_account');

        $stripePaymentGateway = $this->mock(StripePaymentGateway::class);
        $paymentGatewayDetailedAccount = new PaymentGatewayDetailedAccount;
        $paymentGatewayDetailedAccount->setPayoutsEnabled(true);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1)
            ->andReturn($stripePaymentGateway);

        $stripePaymentGateway
            ->shouldReceive('getAccount')
            ->with($params['payment_gateway_account_id'])
            ->andReturn($paymentGatewayDetailedAccount);

        $this->organizationRepository
            ->shouldReceive('find')
            ->never()
            ->shouldReceive('update')
            ->once()
            ->with($request, $organization->organization_id)
            ->andReturn($organization);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_UPDATED');
        $apiData = ['organization_id' => $organization->organization_id];

        $this->responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData);

        $gatewayAccount = factory(PaymentGatewayAccount::class)->make([
            'organization_id' => $organization->organization_id,
            'payment_gateway_account_id' => $params['payment_gateway_account_id'],
            'payment_gateway' => $params['payment_gateway']
        ]);

        $this->accountService->shouldReceive('save')
            ->once()
            ->andReturn($gatewayAccount);

        $response = $this->organizationController->update(
            $request,
            $organization->organization_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method with no organization update
     * but only delete payment gateway account.
     *
     * @return void
     */
    public function testUpdateDeletePaymentGatewayAccount()
    {
        $this->expectsEvents(UserActivityLogEvent::class);
        $organization = $this->organizations()->first();

        $request = new Request();
        $request->query->add([
            'payment_gateway_account' => null,
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator
            ->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $params = $request->get('payment_gateway_account');

        $stripePaymentGateway = $this->mock(StripePaymentGateway::class);
        $paymentGatewayDetailedAccount = new PaymentGatewayDetailedAccount;
        $paymentGatewayDetailedAccount->setPayoutsEnabled(true);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1)
            ->andReturn($stripePaymentGateway);

        $stripePaymentGateway
            ->shouldReceive('getAccount')
            ->never();

        $this->organizationRepository
            ->shouldReceive('isLinkedToMissionWithDonationAttribute')
            ->once()
            ->with($organization->organization_id)
            ->andReturn(false);

        $this->organizationRepository
            ->shouldReceive('find')
            ->once()
            ->with($organization->organization_id)
            ->andReturn($organization)
            ->shouldReceive('update')
            ->never();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_UPDATED');
        $apiData = ['organization_id' => $organization->organization_id];

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData);

        $gatewayAccount = factory(PaymentGatewayAccount::class)->make([
            'organization_id' => $organization->organization_id
        ]);

        $this->accountService
            ->shouldReceive('delete')
            ->once();

        $response = $this->organizationController->update(
            $request,
            $organization->organization_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method with invalid payment gateway
     *
     * @return void
     */
    public function testUpdateWithInvalidPaymentGateway()
    {
        $organization = $this->organizations()->first();

        $request = new Request();
        $request->query->add([
            'payment_gateway_account' => [
                'payment_gateway' => 'STRIPE',
                'payment_gateway_account_id' => 'invalid-account'
            ]
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $params = $request->get('payment_gateway_account');

        $stripePaymentGateway = $this->mock(StripePaymentGateway::class);

        $this->paymentGatewayFactory
            ->shouldReceive('getPaymentGateway')
            ->with(1)
            ->andReturn($stripePaymentGateway);

        $stripePaymentGateway
            ->shouldReceive('getAccount')
            ->with($params['payment_gateway_account_id'])
            ->andThrow(new PaymentGatewayException);

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID'),
                'Invalid payment gateway account id'
            );

        $this->organizationRepository->shouldReceive('update')
            ->never();

        $response = $this->organizationController->update(
            $request,
            $organization->organization_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method
     *
     * @return void
     */
    public function testUpdateWithoutPaymentGateway()
    {
        $this->expectsEvents(UserActivityLogEvent::class);
        $organization = $this->organizations()->first();

        $request = new Request();
        $request->query->add([
            'name' => 'Foo Bar',
            'payment_gateway_account' => null
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('isLinkedToMissionWithDonationAttribute')
            ->once()
            ->with($organization->organization_id)
            ->andReturn(false);

        $this->organizationRepository
            ->shouldReceive('find')
            ->never()
            ->shouldReceive('update')
            ->once()
            ->with($request, $organization->organization_id)
            ->andReturn($organization);

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_ORGANIZATION_UPDATED');
        $apiData = ['organization_id' => $organization->organization_id];

        $this->responseHelper->shouldReceive('success')
            ->once()
            ->with($apiStatus, $apiMessage, $apiData);

        $this->accountService
            ->shouldReceive('save')
            ->never()
            ->shouldReceive('delete')
            ->once()
            ->with([
                'organization_id' => $organization->organization_id
            ])
            ->andReturn(true);

        $response = $this->organizationController->update(
            $request,
            $organization->organization_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method without payment gateway then is linked to a mission with donation attribute
     *
     * @return void
     */
    public function testUpdateWithoutPaymentGatewayError()
    {
        $organization = $this->organizations()->first();

        $request = new Request();
        $request->query->add([
            'name' => 'Foo Bar',
            'payment_gateway_account' => null
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('isLinkedToMissionWithDonationAttribute')
            ->once()
            ->with($organization->organization_id)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('find')
            ->never()
            ->shouldReceive('update')
            ->once()
            ->with($request, $organization->organization_id)
            ->andReturn($organization);

        $this->accountService
            ->shouldReceive('save')
            ->never()
            ->shouldReceive('delete')
            ->never();

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ORGANIZATION_UPDATE_WITHOUT_ACCOUNT'),
                'Cannot unset the organization’s payment_gateway_account as it is linked to a donation mission.'
            );

        $response = $this->organizationController->update(
            $request,
            $organization->organization_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method with expected validation error
     *
     * @return void
     */
    public function testUpdateValidation()
    {
        $request = new Request();
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

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ORGANIZATION_REQUIRED_FIELDS_EMPTY'),
                $errors->first()
            );

        $this->organizationRepository->shouldReceive('update')
            ->never();

        $response = $this->organizationController->update($request, 'sampleID');

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test update method with expected exception
     *
     * @return void
     */
    public function testUpdateException()
    {
        $organization = $this->organizations()->first();

        $request = new Request();
        $request->query->add([
            'name' => 'Foo Bar',
        ]);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $this->tenantActivatedSettingRepository
            ->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with('donation', $request)
            ->andReturn(true);

        $this->organizationRepository
            ->shouldReceive('find')
            ->never()
            ->shouldReceive('update')
            ->once()
            ->with($request, $organization->organization_id)
            ->andThrow(new ModelNotFoundException);

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_ORGANIZATION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND')
            );

        $response = $this->organizationController->update(
            $request,
            $organization->organization_id
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test destroy method
     *
     * @return void
     */
    public function testDestroy()
    {
        $this->expectsEvents(UserActivityLogEvent::class);

        $request = new Request();
        $organization = $this->organizations()->first();

        $this->organizationRepository->shouldReceive('isOrganizationLinkedtoMission')
            ->once()
            ->with($organization->organization_id)
            ->andReturn(false);

        $this->organizationRepository->shouldReceive('delete')
            ->once()
            ->with($organization->organization_id)
            ->andReturn(true);

        $this->responseHelper->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_NO_CONTENT,
                trans('messages.success.MESSAGE_ORGANIZATION_DELETED')
            );

        $response = $this->organizationController->destroy($organization->organization_id);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test destroy method is linked
     *
     * @return void
     */
    public function testDestroyIsLinked()
    {
        $request = new Request();
        $organization = $this->organizations()->first();

        $this->organizationRepository->shouldReceive('isOrganizationLinkedtoMission')
            ->once()
            ->with($organization->organization_id)
            ->andReturn(true);

        $this->organizationRepository->shouldReceive('delete')
            ->never();

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_ORGANIZATION_LINKED_TO_MISSION'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_LINKED_TO_MISSION')
            );

        $response = $this->organizationController->destroy($organization->organization_id);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test destroy method throws exception
     *
     * @return void
     */
    public function testDestroyException()
    {
        $request = new Request();
        $organization = $this->organizations()->first();

        $this->organizationRepository->shouldReceive('isOrganizationLinkedtoMission')
            ->once()
            ->with($organization->organization_id)
            ->andThrow(new ModelNotFoundException);

        $this->organizationRepository->shouldReceive('delete')
            ->never();

        $this->responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_ORGANIZATION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_ORGANIZATION_NOT_FOUND')
            );

        $response = $this->organizationController->destroy($organization->organization_id);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Returns sample of organizations
     *
     * @return array
     */
    private function organizations($count = 1)
    {
        $this->organization
            ->shouldReceive('getAttribute')
            ->andReturn('foo');
        $organizations = array_fill(0, $count, $this->organization);
        return new Collection($organizations);
    }

    /**
     * Creates an instance of LengthAwarePaginator
     *
     * @param array $items
     * @param integer $total
     * @param integer $perPage
     *
     * @return LengthAwarePaginator
     */
    private function getPaginator($items, $total, $perPage)
    {
        return new LengthAwarePaginator($items, $total, $perPage);
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
