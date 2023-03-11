<?php

namespace App\Listeners\Notifications;

use App\Events\Mission\MissionDeletedEvent;
use App\Models\Mission;
use App\Models\MissionApplication;
use App\Models\MissionInvite;
use App\Models\Notification;
use App\Models\NotificationType;

class DeleteMissionNotifications
{
    /**
     * @param MissionDeletedEvent $event
     */
    public function handle(MissionDeletedEvent $event)
    {
        $missionIds = Mission::withTrashed()
            ->where('mission_id', '=', $event->missionId)
            ->get('mission_id')
            ->map(function (Mission $mission) {
                return $mission->mission_id;
            })
            ->toArray();

        $notificationTypeId = NotificationType::where(['notification_type' => config("constants.notification_type_keys")["NEW_MISSIONS"]])
            ->get('notification_type_id')
            ->first()
            ->notification_type_id;

        Notification::where(['notification_type_id' => $notificationTypeId])
            ->whereIn('entity_id', $missionIds)
            ->delete();
    }
}
