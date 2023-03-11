<?php

namespace App\Exceptions\PaymentGateway;

use Exception;

class PaymentGatewayException extends Exception
{
    /**
     * @var int
     */
    private $paymentGateway;

    /**
     * @var Exception
     */
    private $chainedException;

    /**
     * @param string|null
     * @param int|null
     * @param Exception|null
     * @return void
     */
    public function __construct(?string $message = null, ?int $code = null, ?Exception $exception = null)
    {
        // chain and store the original exception for later retrieval
        parent::__construct($message, $code, $exception);
        $this->chainedException = $exception;
    }

    /**
     * @return Exception|null
     */
    public function getChainedException(): ?Exception
    {
        return $this->chainedException;
    }

    /**
     * @return int|null
     */
    public function getPaymentGateway(): ?int
    {
        return $this->paymentGateway;
    }

    /**
     * @param int
     * @return PaymentGatewayException
     */
    public function setPaymentGateway(int $paymentGateway): PaymentGatewayException
    {
        if (!empty($paymentGateway)) {
            $this->paymentGateway = $paymentGateway;
        }
        return $this;
    }
}
