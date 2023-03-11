<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Story;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use App\User;

class StoryInvite extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'story_invite';

    /**
     * The primary key for the model.
     *
     * @var int
     */
    protected $primaryKey = 'story_invite_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['story_id', 'from_user_id', 'to_user_id'];
    
    /**
     * Get story invite record for a user
     * @var int $storyId
     * @var int $inviteUserId
     * @var int $fromUserId
     * @return Illuminate\Support\Collection
     */
    public function getStoryInvite(int $storyId, int $inviteUserId, int $fromUserId): Collection
    {
        return static::where(['story_id' => $storyId,
        'to_user_id' => $inviteUserId, 'from_user_id' => $fromUserId])->get();
    }

    /**
     * Get mission invite details
     *
     * @param int $inviteId
     * @return App\Models\StoryInvite
     */
    public function getDetails(int $inviteId): StoryInvite
    {
        return $this->withTrashed()->with(['toUser', 'fromUser', 'story'])
        ->where('story_invite_id', $inviteId)->first();
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
     * Get the translations associated with the story
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function story(): HasOne
    {
        return $this->hasOne(Story::class, 'story_id', 'story_id')->withTrashed();
    }
}
