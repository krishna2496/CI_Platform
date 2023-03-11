<?php

namespace App\Listeners\Notifications;

use App\Events\Mission\MissionDeletedEvent;
use App\Models\MissionInvite;
use App\Models\Notification;
use App\Models\NotificationType;

class DeleteMissionInviteNotifications
{
    /**
     * @param MissionDeletedEvent $event
     */
    public function handle(MissionDeletedEvent $event)
    {
        $missionInviteIds = MissionInvite::withTrashed()
            ->where('mission_id', '=', $event->missionId)
            ->get('mission_invite_id')
            ->map(function (MissionInvite $missionInvite) {
                return $missionInvite->mission_invite_id;
            })
            ->toArray();

        $notificationTypeId = NotificationType::where(['notification_type' => config("constants.notification_type_keys")["RECOMMENDED_MISSIONS"]])
            ->get('notification_type_id')
            ->first()
            ->notification_type_id;

        Notification::where(['notification_type_id' => $notificationTypeId])
            ->whereIn('entity_id', $missionInviteIds)
            ->delete();
    }
}
