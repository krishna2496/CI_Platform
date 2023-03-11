<?php

namespace App\Repositories\Currency;

use App\Exceptions\CannotDeactivateDefaultTenantCurrencyException;
use App\Models\Tenant;
use App\Models\TenantAvailableCurrency;
use App\Repositories\Currency\CurrencyRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TenantAvailableCurrencyRepository
{
    /**
     * @var App\Models\TenantAvailableCurrency
     */
    private $tenantAvailableCurrency;

    /**
     * @var App\Models\Tenant
     */
    private $tenant;

    /**
     * Create a new Currency repository instance.
     *
     * @param App\Models\TenantAvailableCurrency $tenantCurrency
     * @param App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(
        TenantAvailableCurrency $tenantAvailableCurrency,
        Tenant $tenant
    ) {
        $this->tenantAvailableCurrency = $tenantAvailableCurrency;
        $this->tenant = $tenant;
    }

    /**
     * Store currency
     *
     * @param array $currency
     * @param int $tenantId
     * @return void
     */
    public function store(array $currency, int $tenantId)
    {
        $tenant = $this->tenant->findOrFail($tenantId);

        $currencyData = [
            'tenant_id' => $tenantId,
            'code' => $currency['code'],
            'is_active' => $currency['is_active']
        ];

        if (isset($currency['default'])) {
            $currencyData['default'] = $currency['default'];
        }

        if (isset($currency['default'])) {
            if ($currency['is_active'] == true && $currency['default'] == true) {
                $this->tenantAvailableCurrency
                ->where('tenant_id', $tenantId)
                ->update(['default' => '0']);
            }
        }

        $this->tenantAvailableCurrency->create($currencyData);
    }

    /**
     * Update currency
     *
     * @param array $currency
     * @param int $tenantId
     * @return void
     * @throws ModelNotFoundException
     * @throws CannotDeactivateDefaultTenantCurrencyException
     */
    public function update(array $currency, int $tenantId)
    {
        $tenantCurrency = $this->tenantAvailableCurrency
            ->where(['tenant_id' => $tenantId, 'code' => $currency['code']])
            ->firstOrFail();

        $currencyData = [
            'tenant_id' => $tenantId,
            'code' => $currency['code'],
            'is_active' => $currency['is_active']
        ];

        if ($currency['is_active'] == false
            && (!isset($currency['default']) || !$currency['default'])
        ) {
            if ($tenantCurrency->default) {
                throw new CannotDeactivateDefaultTenantCurrencyException();
            }
        }

        if (isset($currency['default'])) {
            $currencyData['default'] = $currency['default'];
            if ($currency['is_active'] == true && $currency['default'] == true) {
                $this->tenantAvailableCurrency
                    ->where('tenant_id', $tenantId)
                    ->update(['default' => '0']);
            }
        }

        $this->tenantAvailableCurrency
            ->where(['tenant_id' => $tenantId, 'code' => $currency['code']])
            ->update($currencyData);
    }

    /**
     * List of all tenant currency
     *
     * @param int $perPage
     * @param int $tenantId
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTenantCurrencyList(int $perPage, int $tenantId) : LengthAwarePaginator
    {
        // Check tenant is present in the system
        $tenantData = $this->tenant->findOrFail($tenantId);

        $currencyTenantDetails = $this->tenantAvailableCurrency
            ->where(['tenant_id' => $tenantId])
            ->orderBy('code', 'ASC')
            ->paginate($perPage);
        return $currencyTenantDetails;
    }
}
