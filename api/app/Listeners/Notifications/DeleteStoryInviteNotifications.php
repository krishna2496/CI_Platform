<?php

namespace App\Listeners\Notifications;

use App\Events\Story\StoryDeletedEvent;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\StoryInvite;
use Illuminate\Support\Facades\Log;

class DeleteStoryInviteNotifications
{
    /**
     * @param StoryDeletedEvent $event
     */
    public function handle(StoryDeletedEvent $event)
    {
        $storyInviteIds = StoryInvite::withTrashed()
            ->where('story_id', '=', $event->storyId)
            ->get('story_invite_id')
            ->map(function (StoryInvite $storyInvite) {
                return $storyInvite->story_invite_id;
            })
            ->toArray();

        $notificationTypeId = NotificationType::where(['notification_type' => config("constants.notification_type_keys")["RECOMMENDED_STORY"]])
            ->get('notification_type_id')
            ->first()
            ->notification_type_id;

        Notification::where(['notification_type_id' => $notificationTypeId])
            ->whereIn('entity_id', $storyInviteIds)
            ->delete();
    }
}
