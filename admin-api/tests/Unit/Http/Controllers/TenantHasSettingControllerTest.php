<?php

namespace Tests\Unit\Http\Controllers;

use App\Helpers\DatabaseHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\TenantHasSettingController;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Repositories\Tenant\TenantRepository;
use App\Repositories\TenantHasSetting\TenantHasSettingRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use TestCase;
use Validator;

class TenantHasSettingControllerTest extends TestCase
{
    private $tenantHasSettingRepository;
    private $tenantRepository;
    private $responseHelper;
    private $databaseHelper;
    private $tenantHasSettingController;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantHasSettingRepository = $this->mock(TenantHasSettingRepository::class);
        $this->tenantRepository = $this->mock(TenantRepository::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->databaseHelper = $this->mock(DatabaseHelper::class);

        $this->tenantHasSettingController = new TenantHasSettingController(
            $this->tenantHasSettingRepository,
            $this->tenantRepository,
            $this->responseHelper,
            $this->databaseHelper
        );
    }

    /**
     * @testdox Test store check volunteer time or goal should be enabled at time
     *
     * @return void
     */
    public function testStoreCheckVolunteeringTimeAndGoalSetting()
    {
        $tenantHasSettingRepository = $this->mock(TenantHasSettingRepository::class);
        $tenantRepository = $this->mock(TenantRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $databaseHelper = $this->mock(DatabaseHelper::class);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantId = rand();
        $requestData = [];
        $request = new Request();

        $tenantRepository->shouldReceive('find')
            ->once()
            ->andReturn(new Tenant());

        $tenantHasSettingRepository->shouldReceive('checkVolunteeringTimeAndGoalSetting')
            ->once()
            ->andReturn(false);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE'),
                trans('messages.custom_error_message.ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE')
            )
            ->andReturn(new JsonResponse());

        $controller = $this->getController(
            $tenantHasSettingRepository,
            $tenantRepository,
            $responseHelper,
            $databaseHelper
        );

        $response = $controller->store($request, $tenantId);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Test store check volunteer setting disabled or not
     *
     * @return void
     */
    public function testStoreCheckVolunteeringSettingDisabled()
    {
        $tenantHasSettingRepository = $this->mock(TenantHasSettingRepository::class);
        $tenantRepository = $this->mock(TenantRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $databaseHelper = $this->mock(DatabaseHelper::class);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantId = rand();
        $requestData = [];
        $request = new Request();

        $tenantRepository->shouldReceive('find')
            ->once()
            ->andReturn(new Tenant());

        $tenantHasSettingRepository->shouldReceive('checkVolunteeringTimeAndGoalSetting')
            ->once()
            ->andReturn(true);

        $tenantHasSettingRepository->shouldReceive('checkVolunteeringSettingDisabled')
            ->once()
            ->andReturn(false);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_VOLUNTEERING_SHOULD_BE_ENABLED'),
                trans('messages.custom_error_message.ERROR_VOLUNTEERING_SHOULD_BE_ENABLED')
            )
            ->andReturn(new JsonResponse());

        $controller = $this->getController(
            $tenantHasSettingRepository,
            $tenantRepository,
            $responseHelper,
            $databaseHelper
        );

        $response = $controller->store($request, $tenantId);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Test show method on TenantHasSettingController
     *
     * @return void
     */
    public function testShow()
    {
        $filters = [
            'keys' => [
                'donation'
            ]
        ];

        $request = new Request($filters);

        $tenant = new Tenant();
        $tenant
            ->setAttribute('tenant_id', 1)
            ->setAttribute('name', 'tenant_name');

        $this->tenantRepository
            ->shouldReceive('find')
            ->once()
            ->with($tenant->tenant_id)
            ->andReturn($tenant);

        $setting = new TenantSetting();
        $setting
            ->setAttribute('title', 'Donation')
            ->setAttribute('tenant_setting_id', 36)
            ->setAttribute('description', 'Enable/disable donation on the platform')
            ->setAttribute('key', 'donation')
            ->setAttribute('is_active', '1');

        $collection = new Collection([
            $setting
        ]);

        $this->tenantHasSettingRepository
            ->shouldReceive('getSettingsList')
            ->once()
            ->with($tenant->tenant_id, [
                'keys' => $request->get('keys')
            ])
            ->andReturn($collection);

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_TENANT_SETTING_LISTING'),
                $collection->toArray()
            )
            ->andReturn(new JsonResponse());

        $response = $this->tenantHasSettingController
            ->show($request, $tenant->tenant_id);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @testdox Test show method on TenantHasSettingController with Exception
     *
     * @return void
     */
    public function testShowException()
    {
        $request = new Request();

        $tenant = new Tenant();
        $tenant
            ->setAttribute('tenant_id', 1)
            ->setAttribute('name', 'tenant_name');

        $this->tenantRepository
            ->shouldReceive('find')
            ->once()
            ->with($tenant->tenant_id)
            ->andThrow(new ModelNotFoundException);

        $this->tenantHasSettingRepository
            ->shouldReceive('getSettingsList')
            ->never();

        $this->responseHelper
            ->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_TENANT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_TENANT_NOT_FOUND')
            )
            ->andReturn(new JsonResponse());

        $response = $this->tenantHasSettingController
            ->show($request, $tenant->tenant_id);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\TenantHasSetting\TenantHasSettingRepository $tenantHasSettingRepository
     * @param  App\Repositories\Tenant\TenantRepository $tenantRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\DatabaseHelper $databaseHelper
     * @return void
     */
    private function getController(
        TenantHasSettingRepository $tenantHasSettingRepository,
        TenantRepository $tenantRepository,
        ResponseHelper $responseHelper,
        DatabaseHelper $databaseHelper
    ) {
        return new TenantHasSettingController(
            $tenantHasSettingRepository,
            $tenantRepository,
            $responseHelper,
            $databaseHelper
        );
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

    /**
     * Close all mockery mock class
     */
    public function tearDown(): void
    {
        Mockery::close();
    }
}
