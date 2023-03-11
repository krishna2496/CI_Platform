<?php

namespace App\Libraries\PaymentGateway;

use App\Libraries\Amount;

class PaymentGatewayDetailedTransaction
{
    const AMOUNT_PRECISION = 4;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var float
     */
    private $exchangeRate;

    /**
     * @var Amount
     */
    private $fee;

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string
     * @return PaymentGatewayDetailedTransaction
     */
    public function setCurrency($currency): PaymentGatewayDetailedTransaction
    {
        if (!empty($currency)) {
            $this->currency = $currency;
        }
        return $this;
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int
     * @return PaymentGatewayDetailedTransaction
     */
    public function setAmount($amount): PaymentGatewayDetailedTransaction
    {
        if (!empty($amount)) {
            $this->amount = (new Amount($amount))->divide(100);
        }
        return $this;
    }

    /**
     * @return float
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * @param float
     * @return PaymentGatewayDetailedTransaction
     */
    public function setExchangeRate($exchangeRate): PaymentGatewayDetailedTransaction
    {
        if (!empty($exchangeRate)) {
            $this->exchangeRate = $exchangeRate;
        }
        return $this;
    }

    /**
     * @return Amount
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param int
     * @return PaymentGatewayDetailedTransaction
     */
    public function setFee($fee): PaymentGatewayDetailedTransaction
    {
        if (!empty($fee)) {
            $this->fee = (new Amount($fee))->divide(100);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'currency' => $this->getCurrency(),
            'amount' => $this->getAmount() ?
                $this->getAmount()->getValue(self::AMOUNT_PRECISION) : null,
            'exchange_rate' => $this->getExchangeRate(),
            'fee' => $this->getFee() ?
                $this->getFee()->getValue(self::AMOUNT_PRECISION) : null
        ];
    }

}
