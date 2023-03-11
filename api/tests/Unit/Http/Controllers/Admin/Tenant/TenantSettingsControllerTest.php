<?php

namespace Tests\Unit\Repositories\TenantHasSetting;

use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\Tenant\TenantSettingsController;
use App\Http\Controllers\Controller;
use App\Models\TenantSetting;
use App\Repositories\TenantSetting\TenantSettingRepository;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Mockery;
use TestCase;
use Validator;

class TenantSettingsControllerTest extends TestCase
{
    private $tenantSettingRepository;
    private $responseHelper;
    private $helpers;
    private $tenantSettingsController;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantSettingRepository = $this->mock(TenantSettingRepository::class);
        $this->responseHelper = $this->mock(ResponseHelper::class);
        $this->helpers = $this->mock(Helpers::class);

        $this->tenantSettingsController = new TenantSettingsController(
            $this->tenantSettingRepository,
            $this->responseHelper,
            $this->helpers
        );
    }

    /**
     * @testdox Test index
     *
     * @return void
     */
    public function testIndex()
    {
        $filters = [
            'keys' => [
                'donation'
            ]
        ];
        $request = new Request($filters);
        $mockResponse = $this->mockGetAllTenantSettingResponse();

        $this->helpers
            ->shouldReceive('getAllTenantSetting')
            ->once()
            ->with($request)
            ->andReturn($mockResponse);

        $keys = $mockResponse
            ->keyBy('tenant_setting_id')
            ->keys()
            ->toArray();

        $this->tenantSettingRepository
            ->shouldReceive('fetchAllTenantSettings')
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

        $this->responseHelper
            ->shouldReceive('success')
            ->once()
            ->with(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_TENANT_SETTINGS_LISTING'),
                $mockResponse->toArray()
            );

        $response = $this->tenantSettingsController->index($request);

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
