<?php

namespace App\Listeners\ActivityLog;

use App\Events\ActivityLogEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Repositories\ActivityLog\ActivityLogRepository;

class ActivityLogListner
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
     * @param  ActivityLogEvent  $data
     * @return void
     */
    public function handle(ActivityLogEvent $data)
    {
        $this->activityLogRepository->storeActivityLog($data->activityDataArray);
    }
}
