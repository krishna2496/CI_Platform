<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use App\User;

class MissionInvite extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mission_invite';

    /**
     * The primary key for the model.
     *
     * @var int
     */
    protected $primaryKey = 'mission_invite_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mission_id', 'from_user_id', 'to_user_id'];
    
    /**
     * Get mission invite record for a user
     * @var int $missionId
     * @var int $inviteUserId
     * @var int $fromUserId
     * @return Illuminate\Support\Collection
     */
    public function getMissionInvite(int $missionId, int $inviteUserId, int $fromUserId): Collection
    {
        return static::where(['mission_id' => $missionId,
        'to_user_id' => $inviteUserId, 'from_user_id' => $fromUserId])->get();
    }

    /**
     * Get mission invite details
     *
     * @param int $inviteId
     * @return App\Models\MissionInvite
     */
    public function getDetails(int $inviteId): MissionInvite
    {
        return $this->withTrashed()->with(['toUser', 'fromUser', 'mission', 'mission.missionLanguage'])
        ->where('mission_invite_id', $inviteId)->first();
    }

    /**
     * Get the translations associated with the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function toUser(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'to_user_id')->withTrashed();
    }

    /**
     * Get the translations associated with the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fromUser(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'from_user_id')->withTrashed();
    }

    /**
     * Get the translations associated with the mission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mission(): HasOne
    {
        return $this->hasOne(Mission::class, 'mission_id', 'mission_id')->withTrashed();
    }
}
