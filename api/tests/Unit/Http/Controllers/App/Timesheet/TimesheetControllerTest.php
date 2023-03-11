<?php

namespace Tests\Unit\Http\Controllers\Timesheet;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\App\Timesheet\TimesheetController;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\Timesheet\TimesheetRepository;
use Bschmitt\Amqp\Amqp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use TestCase;
use Validator;

class TimesheetControllerTest extends TestCase
{
    /**
     * Test index with validation failure
     *
     * @return void
     */
    public function testIndexValidationFailure()
    {
        $request = new Request();
        $timesheetRepository = $this->mock(TimesheetRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $helpers = $this->mock(Helpers::class);
        $amqp = $this->mock(Amqp::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);

        $errors = new Collection([
            'The type field is required.'
        ]);
        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(true)
            ->shouldReceive('errors')
            ->andReturn($errors);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_TIMESHEET_REQUIRED_FIELDS_EMPTY'),
                $errors->first()
            );

        $controllerInstance = $this->getController(
            $timesheetRepository,
            $responseHelper,
            $missionRepository,
            $tenantOptionRepository,
            $helpers,
            $amqp,
            $tenantActivatedSettingRepository
        );
        $response = $controllerInstance->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test index with setting disabled
     *
     * @return void
     */
    public function testIndexSettingDisabled()
    {
        $data = [
            'type' => 'goal'
        ];
        $request = new Request($data);
        $timesheetRepository = $this->mock(TimesheetRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $helpers = $this->mock(Helpers::class);
        $amqp = $this->mock(Amqp::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION'), $request)
            ->andReturn(false);

        $responseHelper->shouldReceive('error')
            ->once()
            ->with(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_TENANT_SETTING_DISABLED'),
                trans('messages.custom_error_message.ERROR_TENANT_SETTING_DISABLED')
            );

        $controllerInstance = $this->getController(
            $timesheetRepository,
            $responseHelper,
            $missionRepository,
            $tenantOptionRepository,
            $helpers,
            $amqp,
            $tenantActivatedSettingRepository
        );
        $response = $controllerInstance->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test index with success
     *
     * @return void
     */
    public function testIndexSuccess()
    {
        $data = [
            'type' => 'goal'
        ];
        $request = new Request($data);
        $timesheetRepository = $this->mock(TimesheetRepository::class);
        $responseHelper = $this->mock(ResponseHelper::class);
        $missionRepository = $this->mock(MissionRepository::class);
        $tenantOptionRepository = $this->mock(TenantOptionRepository::class);
        $helpers = $this->mock(Helpers::class);
        $amqp = $this->mock(Amqp::class);
        $tenantActivatedSettingRepository = $this->mock(TenantActivatedSettingRepository::class);

        $validator = $this->mock(\Illuminate\Validation\Validator::class);
        $validator->shouldReceive('fails')
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->andReturn($validator);

        $tenantActivatedSettingRepository->shouldReceive('checkTenantSettingStatus')
            ->once()
            ->with(config('constants.tenant_settings.VOLUNTEERING_GOAL_MISSION'), $request)
            ->andReturn(true);

        $paginator = $this->getPaginator(
                [],
                0,
                15
            );

        $timesheetRepository->shouldReceive('getAllTimesheetEntries')
            ->once()
            ->with($request, 'goal')
            ->andReturn($paginator);

        $responseHelper->shouldReceive('successWithPagination')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_NO_TIMESHEET_ENTRIES_FOUND'),
                $paginator,
            )
            ->andReturn(New JsonResponse);

        $controllerInstance = $this->getController(
            $timesheetRepository,
            $responseHelper,
            $missionRepository,
            $tenantOptionRepository,
            $helpers,
            $amqp,
            $tenantActivatedSettingRepository
        );
        $response = $controllerInstance->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Create a new controller instance.
     *
     * @param TimesheetRepository $timesheetRepository
     * @param ResponseHelper $responseHelper
     * @param MissionRepository $missionRepository
     * @param TenantOptionRepository $tenantOptionRepository
     * @param Helpers $helpers
     * @param Amqp $amqp
     * @param TenantActivatedSettingRepository $tenantActivatedSettingRepository
     *
     * @return void
     */
    private function getController(
        TimesheetRepository $timesheetRepository,
        ResponseHelper $responseHelper,
        MissionRepository $missionRepository,
        TenantOptionRepository $tenantOptionRepository,
        Helpers $helpers,
        Amqp $amqp,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository
    ) {
        return new TimesheetController(
            $timesheetRepository,
            $responseHelper,
            $missionRepository,
            $tenantOptionRepository,
            $helpers,
            $amqp,
            $tenantActivatedSettingRepository
        );
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
