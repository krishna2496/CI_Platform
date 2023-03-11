<?php

namespace App\Listeners\ActivityLog;

use App\Events\User\UserActivityLogEvent;
use App\Notifiers\AppUserNotifiers\UserDatabaseNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
use App\Models\UserNotification;
use App\Repositories\ActivityLog\ActivityLogRepository;

class UserActivityLogListner
{
    /**
     * @var App\Repositories\Notification\ActivityLogRepository
     */
    public $activityLogRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ActivityLogRepository $activityLogRepository)
    {
        $this->activityLogRepository = $activityLogRepository;
    }

    /**
     * Handle the event.
     *
     * @param  UserActivityLogEvent  $data
     * @return void
     */
    public function handle(UserActivityLogEvent $data)
    {
        $this->activityLogRepository->storeActivityLog($data->activityDataArray);
    }
}
