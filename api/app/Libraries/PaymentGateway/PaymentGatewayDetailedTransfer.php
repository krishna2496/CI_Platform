<?php

namespace App\Libraries\PaymentGateway;

use App\Libraries\Amount;

class PaymentGatewayDetailedTransfer
{
    const AMOUNT_PRECISION = 4;

    /**
     * @var Amount
     */
    private $amount;

     /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int
     * @return PaymentGatewayDetailedTransaction
     */
    public function setAmount($amount): PaymentGatewayDetailedTransfer
    {
        if (!empty($amount)) {
            $this->amount = (new Amount($amount))->divide(100);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'amount' => $this->getAmount() ?
                $this->getAmount()->getValue(self::AMOUNT_PRECISION) : null,
        ];
    }
}
