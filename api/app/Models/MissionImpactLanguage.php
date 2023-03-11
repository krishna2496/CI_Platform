<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class MissionImpactLanguage extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_impact_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_impact_language_id';

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
    protected $visible = ['language_id', 'content'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_impact_id', 'language_id', 'content'];

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
            $model->mission_impact_language_id = Uuid::uuid4()->toString();
        });
    }
    
    /**
     * Store/update specified resource for mission impact translations.
     * 
     * @param array $condition
     * @param array $data
     * @return App\Models\MissionImpactLanguage 
     */
    public function createOrUpdateMissionImpactTranslation(
        array $condition,
        array $data
    ): MissionImpactLanguage {
        return static::updateOrCreate($condition, $data);
    }
}
