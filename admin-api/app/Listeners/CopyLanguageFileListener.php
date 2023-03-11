<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\TenantLanguageAddedEvent;

class CopyLanguageFileListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TenantLanguageAddedEvent  $event
     * @return void
     */
    public function handle(TenantLanguageAddedEvent $event)
    {
        $validFileName = $event->languageCode.'.json';
        if (
            Storage::disk('s3')->exists(env('AWS_S3_DEFAULT_LANGUAGE_FOLDER_NAME').'/'.$validFileName)
            &&
            !Storage::disk('s3')->exists($event->tenantName.'/languages/'.$validFileName)
        ) {
            $file = Storage::disk('s3')->get(
                env('AWS_S3_DEFAULT_LANGUAGE_FOLDER_NAME').'/'.$validFileName
            );
            Storage::disk('s3')->put($event->tenantName.'/languages/'.$validFileName, $file);
        }
    }
}
