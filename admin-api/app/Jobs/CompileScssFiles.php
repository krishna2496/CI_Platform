<?php

namespace App\Jobs;

use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Support\Facades\Storage;
use ScssPhp\ScssPhp\Compiler;
use App\Models\Tenant;

class CompileScssFiles extends Job
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Models\Tenant
     */
    private $tenant;

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
     * @param App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Job will try to attempt only one time. If need to re-attempt then it will delete job from table
        $scss = new Compiler();
        $scss->addImportPath(realpath(storage_path().'/app/'.$this->tenant->name.'/assets/scss'));

        $assetUrl = 'https://'.env("AWS_S3_BUCKET_NAME").'.s3.'
        .env("AWS_REGION", "eu-central-1").'.amazonaws.com/'.$this->tenant->name.'/assets/images';

        $importScss =
            '@import "_assets";
            $assetUrl: "'.$assetUrl.'";
            @import "_variables";
            @import "../../../../../node_modules/bootstrap/scss/bootstrap";
            @import "../../../../../node_modules/bootstrap-vue/src/index";
            @import "custom";';

        $css = $scss->compile($importScss);
        
        // Put compiled css file into local storage
        Storage::disk('local')->put($this->tenant->name.'\assets\css\style.css', $css);

        // Copy default theme folder to tenant folder on s3
        Storage::disk('s3')->put(
            $this->tenant->name.'/assets/css/style.css',
            Storage::disk('local')->get($this->tenant->name.'\assets\css\style.css')
        );
    }
}
