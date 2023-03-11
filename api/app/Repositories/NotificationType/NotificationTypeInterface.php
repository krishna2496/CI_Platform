<?php
namespace App\Repositories\NotificationType;

use Illuminate\Database\Eloquent\Collection;

interface NotificationTypeInterface
{
    /**
     * Get notification settings
     *
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getNotificationSettings(int $userId): Collection;
}
