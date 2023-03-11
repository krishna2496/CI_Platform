<?php

namespace App\Jobs;

class ResetStyleSettingsJob extends Job
{
    /**
     * @var string $tenantName
     */
    private $tenantName;

    /**
     * Create a new job instance.
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
        // Remove tenant's custom SCSS from S3
        dispatch(new DeleteCustomScssFromS3Job($this->tenantName));

        // Create the temporary SCSS directory
        dispatch(new CreateScssTemporaryFolderJob($this->tenantName));

        // Compile downloaded files and update css on s3
        dispatch(new CompileAndUploadCustomCssJob($this->tenantName));
    }
}
