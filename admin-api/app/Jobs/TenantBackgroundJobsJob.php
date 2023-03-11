<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Queue;
use App\Helpers\EmailHelper;

class TenantBackgroundJobsJob extends Job
{
    /**
     * @var App\Models\Tenant
     */
    protected $tenant;

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
     * @var App\Helpers\EmailHelper
     */
    private $emailHelper;

    /**
     * Create a new job instance.
     * @param App\Models\Tenant $tenant
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->emailHelper = new EmailHelper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->tenant->update(
                [
                    'background_process_status' => config('constants.background_process_status.IN_PROGRESS')
                ]
            );

            // ONLY FOR DEVELOPMENT MODE. (PLEASE REMOVE THIS CODE IN PRODUCTION MODE)
            if (env('APP_ENV')=='testing') {
                dispatch(new TenantDefaultLanguageJob($this->tenant));
            }

            // Job dispatched to create new tenant's database and migrations
            dispatch(new TenantMigrationJob($this->tenant));

            // Create assets folder for tenant on AWS s3 bucket
            dispatch(new CreateFolderInS3BucketJob($this->tenant));

            $this->tenant->update(
                [
                    'background_process_status' => config('constants.background_process_status.COMPLETED')
                ]
            );

            // Send success mail notification to admin
            $this->sendEmailNotification(true);

        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            throw $exception;
        }
    }

    /**
     * The job failed to process.
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        $this->tenant->update(['background_process_status' => config('constants.background_process_status.FAILED')]);
        $this->sendEmailNotification(false);
    }

    /**
     * Send email notification to admin
     * @param bool $isSuccess
     * @return void
     */
    public function sendEmailNotification(bool $isSuccess)
    {
        $status = ($isSuccess) ? trans('messages.email_text.PASSED') : trans('messages.email_text.FAILED');
        $subjectStatus = ($isSuccess) ? trans("messages.email_text.SUCCESS") : trans("messages.email_text.ERROR");

        $message = "<p> ".trans('messages.email_text.TENANT')." : " .$this->tenant->name. "<br>";
        $message .= trans('messages.email_text.BACKGROUND_JOB_STATUS')." : ".$status
        ." <br>";

        $data = array(
            'message'=> $message,
            'tenant_name' => $this->tenant->name
        );

        $params['to'] = config('constants.ADMIN_EMAIL_ADDRESS'); //required
        $params['template'] = config('constants.EMAIL_TEMPLATE_FOLDER').'.'.config('constants.EMAIL_TEMPLATE_JOB_NOTIFICATION'); //path to the email template
        $params['subject'] = $subjectStatus. " : "
        .trans('messages.email_text.ON_BACKGROUND_JOBS')." ". $this->tenant->name . " "
        .trans("messages.email_text.TENANT");

        $params['data'] = $data;

        $this->emailHelper->sendEmail($params);
    }
}
