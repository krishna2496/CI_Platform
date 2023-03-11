<?php
namespace App\Repositories\StoryInvite;

use App\Repositories\StoryInvite\StoryInviteInterface;
use App\Helpers\ResponseHelper;
use App\Models\StoryInvite;
use App\Models\Story;
use Illuminate\Support\Collection;

class StoryInviteRepository implements StoryInviteInterface
{
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Models\StoryInvite
     */
    public $storyInvite;

    /**
     * @var App\Models\Story
     */
    public $story;
    
    /**
     * Create a new StoryInvite repository instance.
     *
     * @param  Illuminate\Http\ResponseHelper $responseHelper
     * @param  App\Models\StoryInvite $storyInvite
     * @param  App\Models\Story $story
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        StoryInvite $storyInvite,
        Story $story
    ) {
        $this->responseHelper = $responseHelper;
        $this->storyInvite = $storyInvite;
        $this->story = $story;
    }

    /**
     * Check user is already invited for a story
     *
     * @param int $storyId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return Illuminate\Support\Collection
     */
    public function getInviteStory(int $storyId, int $inviteUserId, int $fromUserId): Collection
    {
        return $this->storyInvite->getStoryInvite($storyId, $inviteUserId, $fromUserId);
    }
    
    /**
     * Store a newly created resource into database
     *
     * @param int $storyId
     * @param int $inviteUserId
     * @param int $fromUserId
     * @return App\Models\StoryInvite
     */
    public function inviteStory(int $storyId, int $inviteUserId, int $fromUserId): StoryInvite
    {
        return $this->storyInvite
        ->create(['story_id' => $storyId, 'to_user_id' => $inviteUserId, 'from_user_id' => $fromUserId]);
    }
    
    /**
     * Get story details
     *
     * @param int $inviteId
     * @return App\Models\StoryInvite
     */
    public function getDetails(int $inviteId): StoryInvite
    {
        return $this->storyInvite->getDetails($inviteId);
    }
}
