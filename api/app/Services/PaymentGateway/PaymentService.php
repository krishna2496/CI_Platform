<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway\Payment;
use App\Repositories\PaymentGateway\PaymentRepository;

class PaymentService
{
    /**
     * App\Repositories\PaymentGateway\PaymentRepository
     */
    private $paymentRepository;

    /**
     * Create a new services instance.
     *
     * @param App\Repositories\PaymentGateway\PaymentRepository $paymentRepository
     *
     * @return void
     */
    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Create Payment
     *
     * @param App\Models\PaymentGateway\Payment $payment
     *
     * @return App\Models\PaymentGateway\Payment
     */
    public function create(Payment $payment): Payment
    {
        return $this->paymentRepository->create($payment);
    }

    /**
     * Update Payment information
     *
     * @param App\Models\PaymentGateway\Payment $payment
     *
     * @return int
     */
    public function update(Payment $payment)
    {
        return $this->paymentRepository->update($payment);
    }
}