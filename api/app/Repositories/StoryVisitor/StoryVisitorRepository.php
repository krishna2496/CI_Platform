<?php

namespace App\Repositories\StoryVisitor;

use App\Models\StoryVisitor;
use App\Repositories\StoryVisitor\StoryVisitorInterface;
use Illuminate\Http\Request;

class StoryVisitorRepository implements StoryVisitorInterface
{
    /**
     *
     * @var App\Models\StoryVisitor
     */
    private $storyVisitor;

    /**
     * Create a new Story visitor repository instance.
     *
     * @param  App\Models\StoryVisitor $storyVisitor
     * @return void
     */
    public function __construct(
        StoryVisitor $storyVisitor
    ) {
        $this->storyVisitor = $storyVisitor;
    }

    /**
     * Update story view count per visitor & return story view count
     *
     * @param array $story
     * @param integer $userId
     * @return int $storyViewCount
     */
    public function updateStoryViewCount(array $story, int $userId): int
    {
        // not found same story user & login user & story status is published then only count story view
        if ($story['story_user_id'] != $userId && $story['status'] ==  config('constants.story_status.PUBLISHED')) {
            $storyVisitorDataArray = array(
                'story_id' => $story['story_id'],
                'user_id' => $userId,
            );
            $storyVisitorData = $this->storyVisitor->updateOrCreate($storyVisitorDataArray);
        }
        return $storyViewCount = $this->storyVisitor->where('story_id', $story['story_id'])->count();
    }

    /**
     * Get story visitor data
     *
     * @param int $storyId
     * @param int $userId
     * @return App\Models\StoryVisitor
     */
    public function getStoryVisitorData(int $storyId, int $userId): StoryVisitor
    {
        return $this->storyVisitor->where('story_id', $storyId)->where('user_id', $userId)->first();
    }
}
