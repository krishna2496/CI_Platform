<?php

namespace Tests\Unit\Repositories\Currency;

use App\Exceptions\CannotDeactivateDefaultTenantCurrencyException;
use App\Models\Tenant;
use App\Models\TenantAvailableCurrency;
use App\Models\TenantCurrency;
use App\Repositories\Currency\Currency;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Currency\TenantAvailableCurrencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use TestCase;

class TenantAvailableCurrencyRepositoryTest extends TestCase
{
    /**
     * @testdox Test store success
     *
     * @return void
     */
    public function testStore()
    {
        $tenant = $this->mock(Tenant::class);
        $currencyRepository = $this->mock(CurrencyRepository::class);
        $tenantAvailableCurrency = $this->mock(TenantAvailableCurrency::class);
        $repository = $this->getRepository(
            $tenantAvailableCurrency,
            $tenant,
            $currencyRepository
        );
        $tenantId = 1;
        $data = [
            'code' => 'USD',
            'default' => true,
            'is_active' => true
        ];

        $currencyData = [
            'tenant_id' => $tenantId,
            'code' => $data['code'],
            'default' => $data['default'],
            'is_active' => $data['is_active']
        ];

        $tenant->shouldReceive('findOrFail')
            ->once()
            ->with($tenantId)
            ->andReturn($tenant);

        $tenantAvailableCurrency->shouldReceive('where')
            ->once()
            ->with('tenant_id', $tenantId)
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('update')
            ->with(['default' => 0])
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('create')
            ->once()
            ->with($currencyData);

        $repository->store($data, $tenantId);
    }

    /**
     * @testdox Test update success
     *
     * @return void
     */
    public function testUpdate()
    {
        $tenant = $this->mock(Tenant::class);
        $currencyRepository = $this->mock(CurrencyRepository::class);
        $tenantAvailableCurrency = $this->mock(TenantAvailableCurrency::class);
        $repository = $this->getRepository(
            $tenantAvailableCurrency,
            $tenant,
            $currencyRepository
        );

        $tenantId = 1;
        $data = [
            'code' => 'USD',
            'default' => true,
            'is_active' => true
        ];

        $currencyData = [
            'tenant_id' => $tenantId,
            'code' => $data['code'],
            'default' => $data['default'],
            'is_active' => $data['is_active']
        ];

        $tenantAvailableCurrency->shouldReceive('where')
            ->twice()
            ->with(['tenant_id' => $tenantId, 'code' => $data['code']])
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('firstOrFail')
            ->once()
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('where')
            ->once()
            ->with('tenant_id', $tenantId)
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('update')
            ->once()
            ->with(['default' => '0'])
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('update')
            ->once()
            ->with($currencyData)
            ->andReturn($tenantAvailableCurrency);

        $repository->update($data, $tenantId);
    }

    /**
     * @testdox Test update success
     *
     * @return void
     */
    public function testUpdateDeactivateDefaultCurrency()
    {
        $this->expectException(CannotDeactivateDefaultTenantCurrencyException::class);

        $tenant = $this->mock(Tenant::class);
        $currencyRepository = $this->mock(CurrencyRepository::class);
        $tenantAvailableCurrency = $this->mock(TenantAvailableCurrency::class);
        $repository = $this->getRepository(
            $tenantAvailableCurrency,
            $tenant,
            $currencyRepository
        );

        $tenantId = 1;
        $data = [
            'code' => 'USD',
            'is_active' => false
        ];

        $currencyData = [
            'tenant_id' => $tenantId,
            'code' => $data['code'],
            'is_active' => $data['is_active']
        ];

        $tenantAvailableCurrency->shouldReceive('getAttribute')
            ->once()
            ->with('default')
            ->andReturn(true);

        $tenantAvailableCurrency->shouldReceive('where')
            ->once()
            ->with(['tenant_id' => $tenantId, 'code' => $data['code']])
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('firstOrFail')
            ->once()
            ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('update')
            ->never();

        $repository->update($data, $tenantId);
    }

    /**
     * @testdox Test get tenant currency list
     *
     * @return void
     */
    public function testGetTenantCurrencyList()
    {
        $tenant = $this->mock(Tenant::class);
        $currencyRepository = $this->mock(CurrencyRepository::class);
        $tenantAvailableCurrency = $this->mock(TenantAvailableCurrency::class);
        $repository = $this->getRepository(
            $tenantAvailableCurrency,
            $tenant,
            $currencyRepository,
        );
        $data = ['perPage' => '10'];
        $perPage = 10;
        $tenantId = 1;

        $tenant->shouldReceive('findOrFail')
            ->once()
            ->with($tenantId)
            ->andReturn($tenant);

        $tenantAvailableCurrency->shouldReceive('where')
             ->once()
             ->with(['tenant_id' => $tenantId])
             ->andReturn($tenantAvailableCurrency);

        $tenantAvailableCurrency->shouldReceive('orderBy')
             ->once()
             ->with('code', 'ASC')
             ->andReturn($tenantAvailableCurrency);

        $items = [
            'code' => 'INR',
            'default' => 1,
            'is_active' => 1
        ];
        $mockTenantCurrencies = new LengthAwarePaginator($items, 0, 10, 1);
        $tenantAvailableCurrency->shouldReceive('paginate')
            ->once()
            ->with($data['perPage'])
            ->andReturn($mockTenantCurrencies);

        $tenantCurrencies = $repository->getTenantCurrencyList(10, $tenantId);
        $this->assertInstanceOf(LengthAwarePaginator::class, $tenantCurrencies);
        $this->assertSame($mockTenantCurrencies, $tenantCurrencies);
    }

    /**
     * Create a new repository instance.
     *
     * @param App\Models\TenantAvailableCurrency $tenantAvailableCurrency
     * @param App\Models\Tenant $tenant
     * @param App\Repositories\Currency\CurrencyRepository $currencyRepository
     * @return void
     */
    private function getRepository(
        TenantAvailableCurrency $tenantAvailableCurrency,
        Tenant $tenant,
        CurrencyRepository $currencyRepository
    ) {
        return new TenantAvailableCurrencyRepository(
            $tenantAvailableCurrency,
            $tenant,
            $currencyRepository
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
