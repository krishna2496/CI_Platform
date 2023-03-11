<?php

namespace Tests\Unit\Repositories\TenantActivatedSetting;

use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Helpers\Helpers;
use App\Models\TenantActivatedSetting;
use Mockery;
use TestCase;

class TenantActivatedSettingRepositoryTest extends TestCase
{
    /**
     * Test getList method on the TenantActivatedSettingRepository Class
     *
     * @return void
     */
    public function testGetList()
    {
        $modelData = factory(TenantActivatedSetting::class, 2)->make();
        $modelData = $modelData->map(function ($data) {
            $data->setAttribute('setting_id', rand (0, 10));
            return $data;
        });

        $model = $this->mock(TenantActivatedSetting::class);
        $model->shouldReceive('select')
            ->once()
            ->with(
                'ts.setting_id',
                'ts.tenant_setting_id'
            )
            ->andReturn($model)
            ->shouldReceive('join')
            ->once()
            ->with('tenant_setting AS ts', 'ts.tenant_setting_id', '=', 'tenant_activated_setting.tenant_setting_id')
            ->andReturn($model)
            ->shouldReceive('when')
            ->once()
            ->andReturn($model)
            ->shouldReceive('get')
            ->once()
            ->andReturn($modelData);

        $helper = $this->mock(Helpers::class);

        $response = $this->getRepository(
            $model,
            $helper
        )->getList();

        $this->assertSame($modelData, $response);

    }

    /**
     * Create a new repository instance.
     *
     * @param  TenantActivatedSetting $activatedSetting
     * @param  Helpers $helper
     *
     * @return void
     */
    private function getRepository(
        TenantActivatedSetting $activatedSetting,
        Helpers $helper
    ) {
        return new TenantActivatedSettingRepository(
            $activatedSetting,
            $helper
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