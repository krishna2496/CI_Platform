<?php

namespace App\Events\User;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use App\Models\NotificationType;

class UserNotificationEvent extends Event
{
    use SerializesModels;

    /**
     * @var string
     */
    public $notificationTypeId;

    /**
     * @var int
     */
    public $entityId;

    /**
     * @var string
     */
    public $action;

    /**
     * @var int|null
     */
    public $userId = null;

    /**
     * Create a new event instance.
     *
     * @param string $notificationType
     * @param int $entityId
     * @param string $action
     * @param int|null $userId
     * @return void
     */
    public function __construct(string $notificationType, int $entityId, string $action, int $userId = null)
    {
        $notificationTypeDetails = NotificationType::where('notification_type', $notificationType)->first();

        $this->notificationTypeId = $notificationTypeDetails->notification_type_id;
        $this->entityId = $entityId;
        $this->action = $action;
        $this->userId = $userId;
    }
}
