<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Tenant\TenantRepository;
use Illuminate\Support\Facades\Storage;

class CopyNonExistingAssetsFromDefaultThemeBucketToTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:copy {--folder=} {--file=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy non-existing assets in default theme s3 bucket to all existing tenants buckets.';

    /**
     * @var App\Repositories\Tenant\TenantRepository
     */
    private $tenantRepository;

    /**
     * Create a new command instance.
     *
     * @param App\Repositories\Tenant\TenantRepository $tenantRepository
     * @return void
     */
    public function __construct(TenantRepository $tenantRepository)
    {
        parent::__construct();
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // --folder option is available
        $folderPath = $this->option('folder');

        // --file option is available
        $files = $this->option('file');

        if (!empty($folderPath) && !empty($files)) {
            $this->error('Only single option is acceptable at a time');
            return;
        }

        if (empty($folderPath) && empty($files)) {
            $this->error('Folder or file option is missing');
            return;
        }

        $defaultThemePath = env('AWS_S3_DEFAULT_THEME_FOLDER_NAME');

        if (!empty($folderPath)) {
            $completeFolderPath = $defaultThemePath.'/'.$folderPath;
            if (!Storage::disk('s3')->exists($completeFolderPath)) {
                $this->error('Given folder path is not found');
                return;
            }
            $filesToBeCopied = Storage::disk('s3')->allFiles($completeFolderPath);
        } else {
            $filesToBeCopied = [];
            foreach ($files as $file) {
                $filepath = $defaultThemePath.'/'.$file;
                if (!Storage::disk('s3')->exists($filepath)) {
                    $this->error('Given filepath '.$file.' is not found');
                    return;
                }
                $filesToBeCopied[] = $filepath;
            }
        }

        $tenants = $this->tenantRepository->getAllTenants();
        $this->copyNonExistingIconPerTenant($tenants, $filesToBeCopied);
    }

    /**
     * Copy non existing files
     *
     * @param null|Illuminate\Support\Collection $tenants
     * @param array $files
     * @return void
     */
    private function copyNonExistingIconPerTenant($tenants, array $files)
    {
        if ($tenants->count() > 0) {
            $bar = $this->output->createProgressBar($tenants->count());
            $tenantsList = $tenants->toArray();
            $this->info('Total tenants: '. $tenants->count());
            $bar->start();

            foreach ($tenantsList as $tenant) {
                // Check if tenant directory exists or not
                if (!Storage::disk('s3')->exists($tenant['name'])) {
                    continue;
                }

                foreach ($files as $file) {
                    $destinationPath = substr(
                        $file,
                        strlen(env('AWS_S3_DEFAULT_THEME_FOLDER_NAME').'/')
                    );
                    $completePath = $tenant['name'].'/'.$destinationPath;
                    if (!Storage::disk('s3')->exists($completePath)) {
                        Storage::disk('s3')->copy(
                            $file,
                            $completePath
                        );
                    }
                }
                $bar->advance();
            }
            $bar->finish();

            $this->info("\n \nAll non-existing icons are copied from default_theme to tenant.");
        }
    }
}
