<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant;
use App\Helpers\DatabaseHelper;
use DB;

class CreateFolderInS3BucketJob extends Job
{
    /**
     * @var App\Models\Tenant
     */
    private $tenant;

    /**
     * @var App\Helpers\DatabaseHelper
     */
    protected $databaseHelper;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * Create a new job instance.
     * @param App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->databaseHelper = new DatabaseHelper;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Create folder on S3 using tenant's FQDN
        Storage::disk('s3')->makeDirectory($this->tenant->name);

        // Copy default_theme folder which is already present on S3
        $files = Storage::disk('s3')->allFiles(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME').
        '/'.env('AWS_S3_ASSETS_FOLDER_NAME'));
        // Fetched files copy to created s3 folder
        foreach ($files as $key => $file) {
            // Remove default_theme path from file URL
            $sourcePath = str_replace(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME'), '', $file);

            // Copy and paste file into tenant's folders
            Storage::disk('s3')->copy($file, $this->tenant->name.'/'.$sourcePath);

            // Insert default logo image in database
            if (strpos(
                $file,
                env('AWS_S3_IMAGES_FOLDER_NAME').'/'.config('constants.AWS_S3_LOGO_IMAGE_NAME')
            )) {
                $logoPathInS3 = 'https://'.env('AWS_S3_BUCKET_NAME').'.s3.'.env('AWS_REGION').'.amazonaws.com/'.
                    $this->tenant->name.'/'.env('AWS_S3_ASSETS_FOLDER_NAME').
                    '/'.env('AWS_S3_IMAGES_FOLDER_NAME').'/'.config('constants.AWS_S3_LOGO_IMAGE_NAME');

                // Connect with tenant database
                $tenantOptionData['option_name'] = "custom_logo";
                $tenantOptionData['option_value'] = $logoPathInS3;

                // Create connection with tenant database
                $this->databaseHelper->connectWithTenantDatabase($this->tenant->tenant_id);
                DB::table('tenant_option')->insert($tenantOptionData);

                // Disconnect tenant database and reconnect with default database
                DB::disconnect('tenant');
                DB::reconnect('mysql');
                DB::setDefaultConnection('mysql');
            }
        }
    }
}
