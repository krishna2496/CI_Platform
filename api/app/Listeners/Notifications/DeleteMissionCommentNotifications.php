<?php

namespace App\Listeners\Notifications;

use App\Events\Mission\MissionDeletedEvent;
use App\Models\Comment;
use App\Models\MissionApplication;
use App\Models\Notification;
use App\Models\NotificationType;

class DeleteMissionCommentNotifications
{
    /**
     * @param MissionDeletedEvent $event
     */
    public function handle(MissionDeletedEvent $event)
    {
        $commentIds = Comment::withTrashed()
            ->where('mission_id', '=', $event->missionId)
            ->get('comment_id')
            ->map(function (Comment $comment) {
                return $comment->comment_id;
            })
            ->toArray();

        $notificationTypeId = NotificationType::where(['notification_type' => config("constants.notification_type_keys")["MY_COMMENTS"]])
            ->get('notification_type_id')
            ->first()
            ->notification_type_id;

        Notification::where(['notification_type_id' => $notificationTypeId])
            ->whereIn('entity_id', $commentIds)
            ->delete();
    }
}
