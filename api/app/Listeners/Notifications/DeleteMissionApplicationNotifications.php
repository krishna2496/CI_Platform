<?php

namespace App\Listeners\Notifications;

use App\Events\Mission\MissionDeletedEvent;
use App\Models\MissionApplication;
use App\Models\Notification;
use App\Models\NotificationType;

class DeleteMissionApplicationNotifications
{
    /**
     * @param MissionDeletedEvent $event
     */
    public function handle(MissionDeletedEvent $event)
    {
        $applicationIds = MissionApplication::withTrashed()
            ->where('mission_id', '=', $event->missionId)
            ->get('mission_application_id')
            ->map(function (MissionApplication $application) {
                return $application->mission_application_id;
            })
            ->toArray();

        $notificationTypeId = NotificationType::where(['notification_type' => config("constants.notification_type_keys")["MISSION_APPLICATION"]])
            ->get('notification_type_id')
            ->first()
            ->notification_type_id;

        Notification::where(['notification_type_id' => $notificationTypeId])
            ->whereIn('entity_id', $applicationIds)
            ->delete();
    }
}
