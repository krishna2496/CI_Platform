<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Storage;

class DownloadAssestFromLocalDefaultThemeToLocalStorageJob extends Job
{
    protected $tenantName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $tenantName)
    {
        $this->tenantName = $tenantName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Storage::disk('local')->makeDirectory($this->tenantName);
        
        $allDefaultFiles = Storage::disk('local')->allFiles(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME').'/assets');
        
        foreach ($allDefaultFiles as $key => $file) {
            $destinationPath = str_replace(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME'), $this->tenantName, $file);
            Storage::disk('local')->put($destinationPath, \file_get_contents(\storage_path('app/'.$file)));
        }
    }
}
