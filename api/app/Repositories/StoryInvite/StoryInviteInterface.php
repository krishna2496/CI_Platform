<?php
namespace App\Repositories\StoryInvite;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\StoryInvite;

interface StoryInviteInterface
{
    /**
     * Check already invited for story or not.
     *
     * @param int $storyId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return Illuminate\Support\Collection
     */
    public function getInviteStory(int $storyId, int $inviteUserId, int $fromUserId): Collection;

    /**
     * Invite for a story.
     *
     * @param int $storyId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return StoryInvite
     */
    public function inviteStory(int $storyId, int $inviteUserId, int $fromUserId): StoryInvite;
}
