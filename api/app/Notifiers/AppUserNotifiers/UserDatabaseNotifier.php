<?php
namespace App\Notifiers\AppUserNotifiers;

use App\User;
use App\Models\Notification;
use App\Repositories\Notification\NotificationRepository;

class UserDatabaseNotifier
{

    /**
     * Store notification to database
     *
     * @param int $notificationTypeId
     * @param int $entityId
     * @param string $action
     * @param int|null $userId
     *
     * @return App\Models\Notification
     */
    public static function notify(
        int $notificationTypeId,
        int $entityId,
        string $action,
        int $userId = null,
        int $isEmailNotification = 0
    ): Notification {
        $user = User::where('user_id', $userId)->first();

        $data['notification_type_id'] = $notificationTypeId;
        $data['entity_id'] = $entityId;
        $data['action'] = $action;
        $data['user_id'] = $userId;
        $data['is_email_notification'] = $isEmailNotification;
        
        
        return $user->notification()->create($data);
    }
}
