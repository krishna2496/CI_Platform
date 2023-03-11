<?php

namespace Tests\Unit\Http\Controllers\Admin\Tenant;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\Tenant\TenantActivatedSettingController;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use TestCase;
use Validator;
use App\Exceptions\VolunteeringTimeOrGoalSettingShouldBeActiveException;

class TenantActivatedSettingControllerTest extends TestCase
{

    /**
    * @testdox Test index with success status
    *
    * @return void
    */
    public function testIndexSuccess()
    {
        $request = new Request();
        $request->merge([
            'keys' => []
        ]);
        $mockResponse = $this->mockGetAllTenantSettingResponse();

        $helper = $this->mock(Helpers::class);
        $helper->shouldReceive('getAllTenantSetting')
            ->once()
            ->with($request)
            ->andReturn($mockResponse);

        $keys = $mockResponse
            ->keyBy('tenant_setting_id')
            ->keys()
            ->toArray();

        $repository = $this->mock(TenantActivatedSettingRepository::class);
        $repository->shouldReceive('getList')
            ->once()
            ->with($keys)
            ->andReturn(new Collection([
                (object) [
                    'tenant_setting_id' => 1,
                    'setting_id' => 1
                ],
                (object) [
                    'tenant_setting_id' => 2,
                    'setting_id' => 2
                ]
            ]));

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(Response::HTTP_OK, 'Settings listed successfully', $mockResponse->toArray());

        $controller = $this->getController(
            $repository,
            $responseHelper,
            $helper
        );

        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test index with not found status
    *
    * @return void
    */
    public function testIndexNotFound()
    {
        $request = new Request();
        $mockResponse = new Collection([]);

        $helper = $this->mock(Helpers::class);
        $helper->shouldReceive('getAllTenantSetting')
            ->once()
            ->with($request)
            ->andReturn($mockResponse);

        $keys = $mockResponse
            ->keyBy('tenant_setting_id')
            ->keys()
            ->toArray();

        $repository = $this->mock(TenantActivatedSettingRepository::class);
        $repository->shouldReceive('getList')
            ->once()
            ->with($keys)
            ->andReturn(new Collection([]));

        $responseHelper = $this->mock(ResponseHelper::class);
        $responseHelper->shouldReceive('success')
            ->once()
            ->with(Response::HTTP_OK, 'No records found', []);

        $controller = $this->getController(
            $repository,
            $responseHelper,
            $helper
        );

        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    private function mockGetAllTenantSettingResponse()
    {
        return new Collection([
            (object) [
                'tenant_setting_id' => 1,
                'key' => 'total_votes',
                'description' => 'setting description',
                'title' => 'Total Votes In The Platform'
            ],
            (object) [
                'tenant_setting_id' => 2,
                'key' => 'skills_enabled',
                'description' => 'User profile edit page - Add new skills (Allow the user to add or manage his skills. If enabled open modal)',
                'title' => 'skills enabled'
            ]
        ]);
    }

    /**
    * @testdox Test store check volunteering time and goal disabled conditions
    *
    * @return void
    */
    public function testStoreCheckVolunteeringTimeGoalSetting()
    {
        $responseHelper = $this->mock(ResponseHelper::class);
        $helper = $this->mock(Helpers::class);
        $repository = $this->mock(TenantActivatedSettingRepository::class);
        $requestData = new Request();
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $repository->shouldReceive('checkVolunteeringSettingDisabled')
            ->once()
            ->andReturn(true);
        
        $repository->shouldReceive('store')
            ->once()
            ->andThrow(new VolunteeringTimeOrGoalSettingShouldBeActiveException);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE'),
                trans('messages.custom_error_message.ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE')
            );
        
        $controller = $this->getController(
            $repository,
            $responseHelper,
            $helper
        );

        $response = $controller->store($requestData);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
    * @testdox Test store check volunteering setting disabled
    *
    * @return void
    */
    public function testStoreCheckVolunteeringSettingDisabled()
    {
        $responseHelper = $this->mock(ResponseHelper::class);
        $helper = $this->mock(Helpers::class);
        $repository = $this->mock(TenantActivatedSettingRepository::class);
        $requestData = new Request();
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $repository->shouldReceive('checkVolunteeringSettingDisabled')
            ->once()
            ->andReturn(false);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_VOLUNTEERING_SHOULD_BE_ENABLED'),
                trans('messages.custom_error_message.ERROR_VOLUNTEERING_SHOULD_BE_ENABLED')
            );
        
        $controller = $this->getController(
            $repository,
            $responseHelper,
            $helper
        );

        $response = $controller->store($requestData);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param  App\Helpers\Helpers $helpers
     *
     * @return void
     */
    private function getController(
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers
    ) {
        return new TenantActivatedSettingController(
            $tenantActivatedSettingRepository,
            $responseHelper,
            $helpers
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
}
