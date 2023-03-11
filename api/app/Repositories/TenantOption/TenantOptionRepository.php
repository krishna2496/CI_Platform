<?php
namespace App\Repositories\TenantOption;

use App\Repositories\TenantOption\TenantOptionInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Validator;
use App\Models\TenantOption;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TenantOptionRepository implements TenantOptionInterface
{
    /**
     * The tenantOption for the model.
     *
     * @var App\Models\TenantOption
     */
    public $tenantOption;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\TenantOption $tenantOption
     * @return void
     */
    public function __construct(TenantOption $tenantOption)
    {
        $this->tenantOption = $tenantOption;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function updateStyleSettings(Request $request): bool
    {
        if (!empty($request->primary_color)) {
            $tenantOption = [
                'option_name' => 'primary_color',
                'option_value' => $request->primary_color,
            ];
            $this->tenantOption->addOrUpdateColor($tenantOption);
        }

        if (!empty($request->primary_color)) {
            $tenantOption = [
                'option_name' => 'secondary_color',
                'option_value' => $request->secondary_color,
            ];
            $this->tenantOption->addOrUpdateColor($tenantOption);
        }

        return true;
    }

    /**
     * Get tenant option data
     *
     * @return Illuminate\Support\Collection
     */
    public function getOptions(): Collection
    {
        return $this->tenantOption->get(['option_name', 'option_value']);
    }

    /**
    * Get a listing of resource.
    *
    * @param array $conditions
    * @return App\Models\TenantOption|null
    */
    public function getOptionWithCondition(array $conditions = []): ?TenantOption
    {
        $optionQuery = $this->tenantOption;

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $optionQuery = $optionQuery->where($column, $value);
            }
        }
        if (is_null($optionQuery->first())) {
            throw new ModelNotFoundException(trans('messages.custom_error_message.ERROR_TENANT_OPTION_NOT_FOUND'));
        }
        return $optionQuery->first();
    }

    /**
     * Create new option
     *
     * @param array $option
     * @return App\Models\TenantOption
     */
    public function store(array $option): TenantOption
    {
        return $this->tenantOption->create($option);
    }

    /**
     * Select by option name
     *
     * @param String $data
     * @return null|Illuminate\Support\Collection
     */
    public function getOptionValue(string $data): ?Collection
    {
        return $this->tenantOption->whereOption_name($data)->get();
    }

    /**
     * Get option value by option name
     *
     * @param String $data
     * @return null|App\Models\TenantOption
     */
    public function getOptionValueFromOptionName(string $data): ?TenantOption
    {
        return $this->tenantOption->whereOption_name($data)->first();
    }
}
