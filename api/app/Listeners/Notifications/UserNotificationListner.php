<?php

namespace App\Listeners\Notifications;

use App\User;
use App\Models\UserNotification;
use App\Events\User\UserNotificationEvent;
use App\Repositories\Mission\MissionRepository;
use App\Notifiers\AppUserNotifiers\UserDatabaseNotifier;
use App\Repositories\Notification\NotificationRepository;

class UserNotificationListner
{
    /**
     * @var App\Repositories\Notification\NotificationRepository
     */
    public $notificationRepository;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    public $missionRepository;

    /**
     * Create the event listener.
     * @param NotificationRepository $notificationRepository
     * @return void
     */
    public function __construct(
        NotificationRepository $notificationRepository,
        MissionRepository $missionRepository
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->missionRepository = $missionRepository;
    }

    /**
     * Handle the event.
     *
     * @param  UserNotificationEvent  $mission
     * @return bool
     */
    public function handle(UserNotificationEvent $data): bool
    {
        $userId = null;
        if (is_null($data->userId)) {
            $users = User::all();
            foreach ($users as $userDetails) {
                $userId = $userDetails->user_id;
                $this->storeNotificationToDatabase($data, $userId);
            }
            return true;
        }
        $this->storeNotificationToDatabase($data);
        return true;
    }

    /**
     * Store notification data into database, if user have activated settings
     * @param UserNotificationEvent $data
     * @return void
     */
    public function storeNotificationToDatabase(UserNotificationEvent $data, int $userId = null)
    {
        $userId = is_null($userId) ? $data->userId : $userId;

        // Checking user have activated notification setting or not
        $isNotificationActive = $this->notificationRepository->userNotificationSetting(
            $userId,
            $data->notificationTypeId
        );
        if (config('constants.notification_type_keys.NEW_MISSIONS')
            === $this->notificationRepository->getNotificationType($data->notificationTypeId)
            && !is_null($isNotificationActive)
        ) {
            // This is mission create notification,
            // here need to check user's skill and availability match with mission or not.
            $isUserRelatedToMission = $this->missionRepository->checkIsMissionRelatedToUser(
                $data->entityId,
                $userId
            );
            if ($isUserRelatedToMission > 0) {
                $this->sendDatabaseNotification($data, $userId);
            }
        } else {
            if ($isNotificationActive) {
                $this->sendDatabaseNotification($data, $userId);
            }
        }
    }

    /**
     * Store notification data into database
     * @param UserNotificationEvent $data
     * @return void
     */
    public function sendDatabaseNotification(UserNotificationEvent $data, int $userId)
    {
        $isEmailNotification = 0;
        if (User::whereUserId($userId)->where('receive_email_notification', 1)
        ->whereNull('deleted_at')->count()) {
            $isEmailNotification = 1;
        }
        $notification = UserDatabaseNotifier::notify(
            $data->notificationTypeId,
            $data->entityId,
            $data->action,
            $userId,
            $isEmailNotification
        );
    }
}
