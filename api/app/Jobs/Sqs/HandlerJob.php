<?php

namespace App\Jobs\Sqs;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Support\Facades\Log;

class HandlerJob extends Job
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(QueueJob $job, array $data)
    {

    }
}
