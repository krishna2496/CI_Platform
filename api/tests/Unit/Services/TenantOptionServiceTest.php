<?php

namespace Tests\App\Services;

use App\Models\TenantOption;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Services\TenantOptionService;
use Mockery;
use TestCase;

/**
 * @coversDefaultClass  \App\Services\TenantOptionService
 */
class TenantOptionServiceTest extends TestCase
{
    /**
     * @var  TenantOptionRepository
     */
    private $tenantOptionRepository;

    /**
     * @covers  ::getOptionValueFromOptionName
     */
    public function testGetOptionValueFromOptionName(): void
    {
        $optionName = 'some-option-name';
        $optionValue = 'some-option-value';

        $tenantOption = new TenantOption();
        $tenantOption->setAttribute('option_name', $optionName);
        $tenantOption->setAttribute('option_value', $optionValue);

        $this->tenantOptionRepository->shouldReceive('getOptionValueFromOptionName')
            ->once()
            // ->with($optionName)
            ->andReturn($tenantOption);

        $tenantOptionService = $this->getTenantOptionService();
        $res = $tenantOptionService->getOptionValueFromOptionName($optionValue);

        $this->assertSame($tenantOption, $res);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantOptionRepository = Mockery::mock(TenantOptionRepository::class);
    }

    /**
     * @return  TenantOptionService
     */
    private function getTenantOptionService(): TenantOptionService
    {
        return new TenantOptionService($this->tenantOptionRepository);
    }
}
