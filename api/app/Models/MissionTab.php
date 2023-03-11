<?php

namespace App\Models;

use App\Models\MissionTabLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class MissionTab extends Model
{
    use softDeletes, CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_tab';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_tab_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_tab_id', 'mission_id', 'sort_key'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'mission_tab_id', 'mission_id', 'sort_key', 'getMissionTabDetail'
    ];
    
    /*
     * Iatstuti\Database\Support\CascadeSoftDeletes;
     */
    protected $cascadeDeletes = ['getMissionTabDetail'];

    /**
     * Find the specified resource.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getMissionTabDetail()
    {
        return $this->hasMany(MissionTabLanguage::class, 'mission_tab_id', 'mission_tab_id');
    }

    /**
     * Soft delete the mission tab by mission_tab_id from the database.
     *
     * @param string $missionTabId
     * @return bool
     */
    public function deleteMissionTabByMissionTabId(string $missionTabId): bool
    {
        return static::findOrFail($missionTabId)->delete();
    }
}
