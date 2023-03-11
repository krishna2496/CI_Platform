<?php

namespace App\Jobs;

class RecompileCustomStylesJob extends Job
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
        // Create the temporary SCSS directory
        dispatch(new CreateScssTemporaryFolderJob($this->tenantName));

        // Compile downloaded files and update css on s3
        dispatch(new CompileAndUploadCustomCssJob($this->tenantName));
    }
}
