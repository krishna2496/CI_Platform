<?php

namespace App\Jobs;

use App\Models\TenantOption;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\BucketNotFoundException;

class CopyDefaultThemeImagesToTenantImagesJob extends Job
{

    /**
     * @var string $tenantName
     */
    protected $tenantName;

    /**
     * Create a new job instance.
     *
     * @param string $tenantName
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
        // Copy default theme folder
        $allFiles = Storage::disk('s3')->allFiles(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME'));

        foreach ($allFiles as $key => $file) {
            // Only scss and css copy
            if (strpos($file, "/images")) {
                $sourcePath = str_replace(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME'), '', $file);
                // Delete if folder is already there
                if (Storage::disk('s3')->exists($this->tenantName . '/' . $sourcePath)) {
                    // Delete existing one
                    Storage::disk('s3')->delete($this->tenantName . '/' . $sourcePath);
                }
                // copy and paste file into tenant's folders
                Storage::disk('s3')->copy($file, $this->tenantName . '/' . $sourcePath);
            }
        }
    }
}
