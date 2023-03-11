<?php

namespace App\Jobs\Sqs;

use App\Helpers\Helpers;
use App\Jobs\Job;
use App\Libraries\PaymentGateway\Stripe\Events\Event;
use App\Libraries\PaymentGateway\Stripe\Events\PaymentEvent;
use App\Models\PaymentGateway\Payment;
use App\Models\PaymentGateway\PaymentFailure;
use App\Services\PaymentGateway\PaymentFailureService;
use App\Services\PaymentGateway\PaymentService;
use DB;
use Exception;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Support\Facades\Log;

class StripeWebhookJob extends Job
{
    /**
     * App\Services\PaymentGateway\PaymentService
     */
    private $paymentService;

    /**
     * App\Services\PaymentGateway\PaymentFailureService
     */
    private $paymentFailureService;

    /**
     * App\Helpers\Helpers
     */
    private $helpers;

    /**
     * Create a new job instance.
     *
     * @param App\Services\PaymentGateway\PaymentService $paymentService
     * @param App\Services\PaymentGateway\PaymentFailureService $paymentFailureService
     * @param App\Helpers\Helpers $helpers
     *
     * @return void
     */
    public function __construct(
        PaymentService $paymentService,
        PaymentFailureService $paymentFailureService,
        Helpers $helpers
    ) {
        $this->paymentService = $paymentService;
        $this->paymentFailureService = $paymentFailureService;
        $this->helpers = $helpers;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(QueueJob $job, array $data)
    {
        Log::info('STRIPE EVENT:', $data);

        $event = Event::constructFrom($data);
        if (!isset($event->type)) {
            throw new Exception('Missing event type.');
        }

        $tenantId = $event->getDataObject('metadata.tenant_id');
        if (!$tenantId) {
            throw new Exception('Missing metadata tenant ID.');
        }

        // Configure application to access tenant database
        $this->helpers->createConnection((int) $tenantId);

        switch ($event->type) {
            case Event::PAYMENT_FAILED:
                $this->processPaymentFailure($event);
            case Event::PAYMENT_SUCCESS:
                $this->processPayment($event);
            break;
            default:
                throw new Exception('Unsupported event type.');
        }
    }

    /**
     * Handle stripe payment gateway events
     *
     * @param App\Libraries\PaymentGateway\Stripe\Events\Event $event
     *
     * @return bool
     */
    private function processPayment(Event $event)
    {
        $payment = PaymentEvent::constructFrom($event->toArray());

        $paymentModel = new Payment;
        $paymentModel
            ->setAttribute('payment_gateway_payment_id', $payment->getData('id'))
            ->setAttribute('status', $payment->getStatus())
            ->setAttribute('payment_method_details', $payment->getMethod())
            ->setAttribute('transfer_currency', $payment->getTransaction('currency'))
            ->setAttribute('amount_converted', $payment->getTransaction('amount'))
            ->setAttribute('transfer_amount_converted', $payment->getTransfer('amount'))
            ->setAttribute('transfer_exchange_rate', $payment->getTransaction('exchange_rate'))
            ->setAttribute('payment_gateway_fee', $payment->getTransaction('fee'));

        // Only update billing data when payment success
        if ($payment->isPaymentSuccessful()) {
            $paymentModel
                ->setAttribute('billing_phone', $payment->getCharge('billing_details.phone'))
                ->setAttribute('billing_address_line_1', $payment->getCharge('billing_details.address.line1'))
                ->setAttribute('billing_address_line_2', $payment->getCharge('billing_details.address.line2'))
                ->setAttribute('billing_city', $payment->getCharge('billing_details.address.city'))
                ->setAttribute('billing_state', $payment->getCharge('billing_details.address.state'))
                ->setAttribute('billing_postal_code', $payment->getCharge('billing_details.address.postal_code'));
        };

        return $this->paymentService->update($paymentModel);
    }

    /**
     * Handles failed stripe payment gateway events
     *
     * @param App\Libraries\PaymentGateway\Stripe\Events\Event $event
     *
     * @return bool
     */
    private function processPaymentFailure(Event $event)
    {
        $paymentEvent = PaymentEvent::constructFrom($event->toArray());

        $paymentFailure = (new PaymentFailure)
            ->setAttribute('payment_gateway_payment_id', $paymentEvent->getData('id'))
            ->setAttribute('failure_data', $paymentEvent->getFailureData());

        return $this->paymentFailureService->create($paymentFailure);
    }
}
