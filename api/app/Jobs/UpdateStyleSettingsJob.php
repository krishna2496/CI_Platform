<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateStyleSettingsJob extends Job
{
    private const SCSS_PATH = '/assets/scss';

    /**
     * @var string
     */
    private $tenantName;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ?string
     */
    private $fileName;

    /**
     * UpdateStyleSettingsJob constructor.
     * @param string $tenantName
     * @param array $options
     * @param string|null $fileName
     */
    public function __construct(string $tenantName, array $options, ?string $fileName)
    {
        $this->tenantName = $tenantName;
        $this->options = $options;
        $this->fileName = $fileName;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        /*
         * The compiling must happen locally. For this reason, we must create a temporary folder
         * to allow the system to compile the custom styles.
         *
         * How does it work ?
         * - We first copy the default theme SCSS to the temp folder
         * - We fetch the tenant's custom SCSS from S3 and overwrite default theme files in temp folder
         * - Trigger the compilation
         * - Push the updated compiled style.css to S3 customer assets folder.
         */
        $tenantStylesExistLocally = Storage::disk('local')->exists($this->tenantName);
        if ($tenantStylesExistLocally) {
            // Only download the newly added file
            if (!empty($this->fileName)) {
                $filePath = $this->tenantName . self::SCSS_PATH . '/' . $this->fileName;
                $file = Storage::disk('s3')->get($filePath);
                Storage::disk('local')->put($filePath, $file);
            }
        } else {
            // Create the temporary SCSS directory
            dispatch(new CreateScssTemporaryFolderJob($this->tenantName));
        }

        // Compile SCSS files
        dispatch(new CompileAndUploadCustomCssJob($this->tenantName, $this->options));
    }
}
