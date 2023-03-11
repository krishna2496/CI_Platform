<?php

namespace Tests\Unit\Repositories\TenantHasSetting;

use App\Models\TenantHasSetting;
use App\Models\TenantSetting;
use App\Repositories\TenantHasSetting\TenantHasSettingRepository;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use TestCase;

class TenantHasSettingRepositoryTest extends TestCase
{
    private $tenantHasSetting;
    private $tenantSetting;
    private $tenantHasSettingRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->tenantHasSetting = $this->mock(TenantHasSetting::class);
        $this->tenantSetting = $this->mock(TenantSetting::class);

        $this->tenantHasSettingRepository = new TenantHasSettingRepository(
            $this->tenantHasSetting,
            $this->tenantSetting
        );
    }

    /**
     * @testdox Test getSettingsList
     *
     * @return void
     */
    public function testGetSettingsList()
    {
        $tenantId = 1;
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

        $this->tenantSetting
            ->shouldReceive('selectRaw')
            ->once()
            ->with('
                tenant_setting.title,
                tenant_setting.tenant_setting_id,
                tenant_setting.description,
                tenant_setting.key,
                CASE
                    WHEN tenant_has_setting.tenant_setting_id IS NULL THEN "0"
                    ELSE "1"
                END AS is_active
            ')
            ->andReturnSelf()
            ->shouldReceive('leftJoin')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('when')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        $response = $this->tenantHasSettingRepository->getSettingsList(
            $tenantId,
            []
        );

        $this->assertSame($collection, $response);
        $this->assertInstanceOf(Collection::class, $response);
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
