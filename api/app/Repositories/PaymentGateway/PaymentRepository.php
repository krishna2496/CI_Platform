<?php

namespace App\Repositories\PaymentGateway;

use App\Models\PaymentGateway\Payment;

class PaymentRepository
{
    /**
     * App\Models\PaymentGateway\Payment
     */
    private $paymentModel;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\PaymentGateway\Payment $paymentModel
     *
     * @return void
     */
    public function __construct(Payment $paymentModel)
    {
        $this->paymentModel = $paymentModel;
    }

    /**
     * Create Payment record
     *
     * @param App\Models\PaymentGateway\Payment $payment
     *
     * @return App\Models\PaymentGateway\Payment
     */
    public function create(Payment $payment): Payment
    {
        $data = $payment->getAttributes();
        $data['amount'] = $payment->amount->getValue();
        $data['transfer_amount'] = $payment->transfer_amount->getValue();

        return $this->paymentModel->create($data);
    }

    /**
     * Save Payment information
     *
     * @param App\Models\PaymentGateway\Payment $payment
     *
     * @return int
     */
    public function update(Payment $payment)
    {
        return $this->paymentModel
            ->where('payment_gateway_payment_id', $payment->payment_gateway_payment_id)
            ->update($payment->getAttributes());
    }
}