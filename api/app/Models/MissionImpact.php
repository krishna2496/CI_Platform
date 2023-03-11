<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class MissionImpact extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_impact';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_impact_id';

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
        'mission_impact_id',
        'mission_id',
        'icon_path',
        'sort_key',
        'missionImpactLanguageDetails'
    ];

    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['missionImpactLanguageDetails'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_id', 'icon_path', 'sort_key'];

    /**
     * Binds creating/saving events to create UUIDs.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate UUID
            $model->mission_impact_id = Uuid::uuid4()->toString();
        });
    }

    /**
     * Get mission impact language details
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function missionImpactLanguageDetails() : HasMany
    {
        return $this->hasMany(MissionImpactLanguage::class, 'mission_impact_id', 'mission_impact_id');
    }

    /**
     * Soft delete the mission impact by mission_impact_id from the database.
     *
     * @param string $missionImpactId
     * @return bool
     */
    public function deleteMissionImpact(string $missionImpactId): bool
    {
        return static::findOrFail($missionImpactId)->delete();
    }
}
