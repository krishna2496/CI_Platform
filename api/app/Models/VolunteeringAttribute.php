<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VolunteeringAttribute extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'volunteering_attribute';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'volunteering_attribute_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'mission_id,',
        'availability_id',
        'total_seats',
        'is_virtual'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mission_id,',
        'availability_id',
        'total_seats',
        'is_virtual'
    ];

    protected $casts = [
        'is_virtual' => 'boolean',
    ];

    /**
     * listen for any Eloquent events
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($volunteeringAttribute) {
            if (! $volunteeringAttribute->getKey()) {
                $volunteeringAttribute->{$volunteeringAttribute->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get availability associated with the volunteering attribute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class, 'availability_id', 'availability_id');
    }

    /**
     * Set volunteering attribute id
     *
     * @param  mixed $value
     * @return void
     */
    public function setVolunteeringAttributeIdAttribute(): void
    {
        $this->attributes['volunteering_attribute_id'] = (String) Str::uuid();
    }

    /**
     * Get users associated with the volunteering availability.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function availableUsers(): HasMany
    {
        return $this->hasMany('App\User', 'availability_id', 'availability_id');
    }

    /**
     * Get the mission application associated with the mission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionApplication(): HasMany
    {
        return $this->hasMany(MissionApplication::class, 'mission_id', 'mission_id');
    }
}
