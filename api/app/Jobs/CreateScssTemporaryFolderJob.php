<?php

namespace App\Jobs;

use App\Helpers\S3Helper;
use App\Services\CustomStyling\CustomStyleFilenames;
use ScssPhp\ScssPhp\Compiler;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;

class CreateScssTemporaryFolderJob extends Job
{
    use RestExceptionHandlerTrait;

    private const SCSS_PATH = '/assets/scss';
    private const DEFAULT_THEME_SCSS_PATH = '/scss/default_theme/';

    /**
     * @var String
     */
    private $tenantName;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     * @param String $tenantName
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
        $tenantScssFolderName = $this->tenantName . self::SCSS_PATH;

        // Create the temporary directory
        Storage::disk('local')->makeDirectory($tenantScssFolderName);

        // Copy default theme SCSS in the temporary folder
        $defaultThemeScssFiles = Storage::disk('resources')->allFiles(self::DEFAULT_THEME_SCSS_PATH);
        foreach ($defaultThemeScssFiles as $scssFile) {
            $scssContent = Storage::disk('resources')->get($scssFile);
            Storage::disk('local')->put($tenantScssFolderName . '/' . basename($scssFile), $scssContent);
        }

        // Download the tenant's custom SCSS from S3 into the temporary folder
        $customScssFiles = Storage::disk('s3')->allFiles($tenantScssFolderName);
        foreach ($customScssFiles as $customScssFile) {
            // Skip files that are not custom SCSS
            if (!in_array(basename($customScssFile), CustomStyleFilenames::EDITABLE_FILES)) {
                continue;
            }

            $customScssContent = Storage::disk('s3')->get($customScssFile);
            Storage::disk('local')->put($tenantScssFolderName . '/' . basename($customScssFile), $customScssContent);
        }
    }
}
