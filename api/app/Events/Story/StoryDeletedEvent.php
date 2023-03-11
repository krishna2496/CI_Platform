<?php

namespace App\Events\Story;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class StoryDeletedEvent extends Event
{
    use SerializesModels;

    /**
     * @var int
     */
    public $storyId;

    public function __construct(int $storyId)
    {
        $this->storyId = $storyId;
    }
}
