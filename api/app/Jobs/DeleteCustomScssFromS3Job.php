<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;

class DeleteCustomScssFromS3Job extends Job
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
        // Retrieve list of custom scss on S3
        $customScssFolderPath = implode('/', [
            $this->tenantName,
            env('AWS_S3_ASSETS_FOLDER_NAME'),
            config('constants.AWS_S3_SCSS_FOLDER_NAME'),
        ]);
        $files = Storage::disk('s3')->allFiles($customScssFolderPath);

        // Fetched files copy to created s3 folder
        foreach ($files as $key => $file) {
            // If file exist then delete it
            Storage::disk('s3')->delete($file);
        }
    }
}
