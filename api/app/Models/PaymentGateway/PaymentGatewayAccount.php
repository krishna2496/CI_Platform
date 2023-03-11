<?php

namespace App\Models\PaymentGateway;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentGatewayAccount extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_gateway_account';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'payment_gateway_account_id',
        'payment_gateway',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'payment_gateway_account_id',
        'payment_gateway',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'payment_gateway',
    ];

    /**
     * Calculates the name of payment gateway.
     */
    private function calculatePaymentGatewayName()
    {
        try {
            if (is_numeric($this->attributes['payment_gateway'])) {
                $type = $this->attributes['payment_gateway'];
                $name = (new PaymentGatewayFactory)->getNameByType($type);
                $this->attributes['payment_gateway'] = $name;
            }
        } catch (PaymentGatewayException $e) {
            // if type is invalid, just ignore and leave attribute as is.
        }
    }

    /**
     * Gets the name of payment gateway.
     *
     * @return ?string
     */
    public function getPaymentGatewayAttribute(): ?string
    {
        $this->calculatePaymentGatewayName();
        return $this->attributes['payment_gateway'] ?? null;
    }

    /**
     * Setup model event observers
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Creating observer: Automatically add primary key value when creating data
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    /**
     * Get the organization that owns the payment gateway account.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
