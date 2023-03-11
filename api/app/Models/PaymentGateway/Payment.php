<?php

namespace App\Models\PaymentGateway;

use App\Casts\Amount;
use App\Models\PaymentGateway\PaymentGatewayAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Payment extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_gateway',
        'payment_gateway_payment_id',
        'status',
        'payment_method_type',
        'payment_method_details',
        'currency',
        'cover_fee',
        'amount',
        'transfer_amount',
        'transfer_currency',
        'amount_converted',
        'transfer_amount_converted',
        'transfer_exchange_rate',
        'payment_gateway_fee',
        'payment_gateway_account_id',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_postal_code',
        'ip_address',
        'ip_address_country',
        'payment_gateway_payment_method_id'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'payment_gateway',
        'payment_gateway_payment_id',
        'status',
        'payment_method_type',
        'payment_method_details',
        'currency',
        'cover_fee',
        'amount',
        'transfer_amount',
        'transfer_currency',
        'amount_converted',
        'transfer_amount_converted',
        'transfer_exchange_rate',
        'payment_gateway_fee',
        'payment_gateway_account_id',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_postal_code',
        'ip_address',
        'ip_address_country',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => Amount::class,
        'transfer_amount' => Amount::class,
        'amount_converted' => Amount::class,
        'transfer_amount_converted' => Amount::class,
        'payment_gateway_fee' => Amount::class,
        'payment_method_details' => 'array'
    ];

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
     * Get the donation record of this payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    /**
     * Get the payment gateway account associated with this payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function paymentGatewayAccount(): HasOne
    {
        return $this->hasOne(
            PaymentGatewayAccount::class,
            'payment_gateway_account_id',
            'payment_gateway_account_id'
        );
    }
}
