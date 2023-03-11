<?php

namespace App\Models\PaymentGateway;

use App\Models\PaymentGateway\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentFailure extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_failure';

    /**
     * primaryKey
     *
     * @var null
     *
     * @access protected
     */
    protected $primaryKey = null;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates that timestamps will should not be used.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_gateway_payment_id',
        'failure_data',
        'received_at'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'payment_gateway_payment_id',
        'failure_data',
        'received_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'failure_data' => 'array'
    ];

    /**
     * Get payment record associated with this payment failure.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function Payment(): HasOne
    {
        return $this->hasOne(
            Payment::class,
            'payment_gateway_payment_id',
            'payment_gateway_payment_id'
        );
    }
}
