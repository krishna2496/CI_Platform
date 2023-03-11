<?php

return [
    'handlers' => [
        env('SQS_STRIPE_WEBHOOK_QUEUE', 'STRIPE_SQS_QUEUE') => App\Jobs\Sqs\StripeWebhookJob::class,
    ],

    'default-handler' => App\Jobs\Sqs\HandlerJob::class
];