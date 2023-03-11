<?php

namespace App\Listeners\Notifications;

use App\Events\Story\StoryDeletedEvent;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Story;

class DeleteStoryNotifications
{
    /**
     * @param StoryDeletedEvent $event
     */
    public function handle(StoryDeletedEvent $event)
    {
        $storyId = Story::withTrashed()
            ->where('story_id', $event->storyId)
            ->get('story_id')
            ->first()
            ->story_id;

        $notificationTypeId = NotificationType::where([
                'notification_type' => config("constants.notification_type_keys")["MY_STORIES"]
            ])
            ->get('notification_type_id')
            ->first()
            ->notification_type_id;

        return Notification::where(['notification_type_id' => $notificationTypeId])
            ->where('entity_id', $storyId)
            ->delete();
    }
}
