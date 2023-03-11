<?php
namespace App\Providers;

use App\Events\ActivityLogEvent;
use App\Events\TenantLanguageAddedEvent;
use App\Listeners\CopyLanguageFileListener;
use App\Listeners\ActivityLog\ActivityLogListner;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ActivityLogEvent::class => [
            ActivityLogListner::class
        ],
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
