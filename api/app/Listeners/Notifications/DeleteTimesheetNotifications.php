<?php

namespace App\Listeners\Notifications;

use App\Events\Mission\MissionDeletedEvent;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Timesheet;

class DeleteTimesheetNotifications
{
    /**
     * @param MissionDeletedEvent $event
     */
    public function handle(MissionDeletedEvent $event)
    {
        $timesheetIds = Timesheet::withTrashed()
            ->where('mission_id', '=', $event->missionId)
            ->get('timesheet_id')
            ->map(function (Timesheet $timesheet) {
                return $timesheet->timesheet_id;
            })
            ->toArray();

        $notificationTypeId = NotificationType::whereIn(
            'notification_type',
            [
                config("constants.notification_type_keys")["VOLUNTEERING_HOURS"],
                config("constants.notification_type_keys")["VOLUNTEERING_GOALS"]
            ])
            ->get('notification_type_id')
            ->map(function (NotificationType $notificationType) {
                return $notificationType->notification_type_id;
            })
            ->toArray();

        Notification::whereIn('notification_type_id', $notificationTypeId)
            ->whereIn('entity_id', $timesheetIds)
            ->delete();
    }
}
