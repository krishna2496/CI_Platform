<?php
namespace App\Repositories\NotificationType;

use App\User;
use App\Models\NotificationType;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\NotificationType\NotificationTypeInterface;

class NotificationTypeRepository implements NotificationTypeInterface
{
    /**
     * @var App\Models\NotificationType
     */
    private $notificationType;

    /**
     * @var App\Models\UserNotification
     */
    private $userNotification;

    /**
     * @var App\User
     */
    private $user;

    /**
     * Create a new notification type repository instance.
     *
     * @param  App\Models\NotificationType $notificationType
     * @param  App\Models\UserNotification $userNotification
     * @return void
     */
    public function __construct(NotificationType $notificationType, UserNotification $userNotification, User $user)
    {
        $this->notificationType = $notificationType;
        $this->userNotification = $userNotification;
        $this->user = $user;
    }

    /**
     * Get notification settings
     *
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getNotificationSettings(int $userId): Collection
    {
        $notificationSettings = $this->notificationType
        ->selectRaw(
            "notification_type.notification_type,
            notification_type.notification_type_id,
            CASE WHEN user_notification.notification_type_id  IS NULL THEN '0' ELSE '1' END AS is_active"
        )
        ->leftJoin('user_notification', function ($join) use ($userId) {
            $join->on('notification_type.notification_type_id', '=', 'user_notification.notification_type_id')
            ->whereNull('user_notification.deleted_at')
            ->where('user_notification.user_id', $userId);
        })
        ->get();

        return $notificationSettings;
    }

    /**
     * Store or update user notification settings
     *
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function storeOrUpdateUserNotification(array $data, int $userId): array
    {
        $notificationSettings = [];
        foreach ($data['settings'] as $value) {
            if ($value['value'] == 1) {
                $this->userNotification->enableUserNotification($userId, $value['notification_type_id']);
            } else {
                $this->userNotification->disableUserNotification($userId, $value['notification_type_id']);
            }
            array_push(
                $notificationSettings,
                [
                    'notification_type_id' => $value['notification_type_id'],
                    'value' => $value['value']
                ]
            );
        }
        // Store settings, which are in user table.
        foreach ($data['user_settings'] as $key => $setting) {
            $this->user->whereUserId($userId)->update($setting);
        }
        return $notificationSettings;
    }
}
