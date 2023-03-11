<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Skill;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionSkill extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_skill';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'mission_skill_id';

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['mission_skill_id', 'skill_id', 'mission_id', 'skill', 'mission_count',
    'total_minutes', 'skill_name','translations'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_skill_id','skill_id', 'mission_id'];

    /**
     * Get the mission associated with the mission skill.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mission(): HasOne
    {
        return $this->hasOne(Mission::class, 'mission_id', 'mission_id');
    }

    /**
     * Get the skill associated with the mission skill.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function skill(): HasOne
    {
        return $this->hasOne(Skill::class, 'skill_id', 'skill_id');
    }
    
    /**
     * Store/update specified resource.
     *
     * @param  int  $missionId
     * @param  int  $skillId
     * @return array
     */
    public function linkMissionSkill(int $missionId, int $skillId)
    {
        return static::firstOrNew(array('mission_id' => $missionId, 'skill_id' => $skillId, 'deleted_at' => null))
            ->save();
    }
    
    /**
     * Get user skills based on mission's skills
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skilledUsers(): HasMany
    {
        return $this->hasMany('App\Models\UserSkill', 'skill_id', 'skill_id');
    }

    /**
     * Unlink mission skill.
     *
     * @param  int  $missionId
     *
     * @return bool
     */
    public function unlinkMissionSkill(int $missionId): bool
    {
        return static::where('mission_id', $missionId)->forceDelete();
    }
}
