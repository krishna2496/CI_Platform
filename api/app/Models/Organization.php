<?php

namespace App\Models;

use App\Models\City;
use App\Models\Country;
use App\Models\PaymentGateway\PaymentGatewayAccount;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organization';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'organization_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'name',
        'legal_number',
        'phone_number',
        'address_line_1',
        'address_line_2',
        'city_id',
        'country_id',
        'postal_code'
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'organization_id',
        'name',
        'legal_number',
        'phone_number',
        'address_line_1',
        'address_line_2',
        'city_id',
        'country_id',
        'postal_code',
        'paymentGatewayAccount'
    ];

    /**
     * Get city id if exist in system
     *
     */
    public function getCityIdAttribute($value)
    {
        return (!empty(City::find($value))) ? City::find($value)->city_id : null;
    }

    /**
     * Get country id if exist in system
     *
     */
    public function getCountryIdAttribute($value)
    {
        return (!empty(Country::find($value))) ? Country::find($value)->country_id : null;
    }

    /**
     * Get the payment gateway account record associated with the organization.
     */
    public function paymentGatewayAccount()
    {
        return $this->hasOne(PaymentGatewayAccount::class, 'organization_id', 'organization_id');
    }
}
