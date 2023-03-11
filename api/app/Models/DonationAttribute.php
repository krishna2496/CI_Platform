<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DonationAttribute extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'donation_attribute';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'donation_attribute_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mission_id',
        'goal_amount_currency',
        'goal_amount',
        'show_goal_amount',
        'show_donation_percentage',
        'show_donation_meter',
        'show_donation_count',
        'show_donors_count',
        'disable_when_funded',
        'is_disabled'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'goal_amount_currency',
        'goal_amount',
        'show_goal_amount',
        'show_donation_percentage',
        'show_donation_meter',
        'show_donation_count',
        'show_donors_count',
        'disable_when_funded',
        'is_disabled'
    ];

    protected $casts = [
        'show_goal_amount' => 'boolean',
        'show_donation_percentage' => 'boolean',
        'show_donation_meter' => 'boolean',
        'show_donation_count' => 'boolean',
        'show_donors_count' => 'boolean',
        'disable_when_funded' => 'boolean',
        'is_disabled' => 'boolean'
    ];

    /**
     * listen for any Eloquent events
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($donationAttribute) {
            if (! $donationAttribute->getKey()) {
                $donationAttribute->{$donationAttribute->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
