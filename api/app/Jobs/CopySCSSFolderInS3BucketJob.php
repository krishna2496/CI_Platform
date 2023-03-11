<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;
use App\Models\Tenant;

class CopySCSSFolderInS3BucketJob extends Job
{
    /**
     * @var String
     */
    private $tenantName;

    /**
     * Create a new job instance.
     * @param App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(String $tenantName)
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
        // Create folder on S3 using tenant's FQDN
        Storage::disk('s3')->makeDirectory($this->tenantName);

        // Copy default_theme folder which is already present on S3
        $files = Storage::disk('s3')->allFiles(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME').'/assets/scss');
        
        // Fetched files copy to created s3 folder
        foreach ($files as $key => $file) {
            // Remove default_theme path from file URL
            $sourcePath = str_replace(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME'), '', $file);

            // If file exist then delete it
            if (Storage::disk('s3')->exists($this->tenantName.'/'.$sourcePath)) {
                Storage::disk('s3')->delete($this->tenantName.'/'.$sourcePath);
            }
            // Copy and paste file into tenant's folders
            Storage::disk('s3')->copy($file, $this->tenantName.''.$sourcePath);
        }
    }
}
