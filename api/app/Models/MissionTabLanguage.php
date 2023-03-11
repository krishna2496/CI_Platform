<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MissionTabLanguage extends Model
{
    use softDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_tab_language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_tab_language_id';

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
    protected $fillable = ['mission_tab_language_id', 'mission_tab_id', 'language_id', 'name', 'section'];

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
    protected $visible = ['mission_tab_id', 'language_id', 'name', 'section'];

    /**
     * Store/update specified resource for mission tab language.
     *
     * @param  array $condition
     * @param  array $data
     * @return App\Models\MissionTabLanguage
     */
    public function createOrUpdateMissionTabLanguage(array $condition, array $data): MissionTabLanguage
    {
        return static::updateOrCreate($condition, $data);
    }
}
