<?php

namespace App\Repositories\PaymentGateway;

use App\Models\PaymentGateway\PaymentFailure;

class PaymentFailureRepository
{
    /**
     * App\Models\PaymentGateway\PaymentFailure
     */
    private $paymentFailure;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\PaymentGateway\PaymentFailure $paymentFailure
     *
     * @return void
     */
    public function __construct(PaymentFailure $paymentFailure)
    {
        $this->paymentFailure = $paymentFailure;
    }

    /**
     * Create payment failure record
     *
     * @param App\Models\PaymentGateway\PaymentFailure $payment
     *
     * @return App\Models\PaymentGateway\PaymentFailure
     */
    public function create(PaymentFailure $paymentFailure): PaymentFailure
    {
        $data = $paymentFailure->getAttributes();
        $data['failure_data'] = $paymentFailure->failure_data;
        return $this->paymentFailure->create($data);
    }
}
