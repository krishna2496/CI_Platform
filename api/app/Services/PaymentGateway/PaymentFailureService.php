<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway\PaymentFailure;
use App\Repositories\PaymentGateway\PaymentFailureRepository;

class PaymentFailureService
{
    /**
     * App\Repositories\PaymentGateway\PaymentRepository
     */
    private $paymentFailureRepository;

    /**
     * Create a new services instance.
     *
     * @param App\Repositories\PaymentGateway\PaymentRepository $paymentFailureRepository
     *
     * @return void
     */
    public function __construct(PaymentFailureRepository $paymentFailureRepository)
    {
        $this->paymentFailureRepository = $paymentFailureRepository;
    }

    /**
     * Create payment failure record
     *
     * @param App\Models\PaymentGateway\PaymentFailure $paymentFailure
     *
     * @return App\Models\PaymentGateway\PaymentFailure
     */
    public function create(PaymentFailure $paymentFailure): PaymentFailure
    {
        return $this->paymentFailureRepository->create($paymentFailure);
    }
}
