<?php

namespace App\Providers;

use App\Events\Mission\MissionDeletedEvent;
use App\Events\Story\StoryDeletedEvent;
use App\Events\User\UserActivityLogEvent;
use App\Events\User\UserNotificationEvent;
use App\Listeners\ActivityLog\UserActivityLogListner;
use App\Listeners\Notifications\DeleteMissionApplicationNotifications;
use App\Listeners\Notifications\DeleteMissionCommentNotifications;
use App\Listeners\Notifications\DeleteMissionInviteNotifications;
use App\Listeners\Notifications\DeleteMissionNotifications;
use App\Listeners\Notifications\DeleteStoryInviteNotifications;
use App\Listeners\Notifications\DeleteStoryNotifications;
use App\Listeners\Notifications\DeleteTimesheetNotifications;
use App\Listeners\Notifications\UserNotificationListner;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserNotificationEvent::class => [
            UserNotificationListner::class
        ],
        UserActivityLogEvent::class => [
            UserActivityLogListner::class
        ],
        MissionDeletedEvent::class => [
            DeleteMissionApplicationNotifications::class,
            DeleteMissionCommentNotifications::class,
            DeleteMissionInviteNotifications::class,
            DeleteMissionNotifications::class,
            DeleteTimesheetNotifications::class,

        ],
        StoryDeletedEvent::class => [
            DeleteStoryNotifications::class,
            DeleteStoryInviteNotifications::class
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
