<?php

namespace App\Libraries\PaymentGateway\Stripe\Events;

use App\Libraries\PaymentGateway\PaymentGatewayFactory;

class PaymentEvent extends Event
{
    /**
     * Connect to stripe payment gateway
     */
    protected $connect = true;

    /**
     * Payment transfer data
     */
    private $transfer;

    /**
     * Payment transaction data
     */
    private $transaction;

    /**
     * Get event data object
     *
     * @param string $path key path
     *
     * @return mixed
     */
    public function getData($path = null)
    {
        return $this->getDataObject($path);
    }

    /**
     * Get event charges data
     *
     * @param string $path key path
     *
     * @return mixed
     */
    public function getCharge($path = null)
    {
        $charges = $this->getData('charges.data');

        if (empty($charges)) {
            return null;
        }

        return $this->getPath($charges[0], $path);
    }

    /**
     * Get event transaction data
     *
     * @param string $path key path
     *
     * @return mixed
     */
    public function getTransaction($path = null)
    {
        $transactionId = $this->getCharge('balance_transaction');

        if (!$transactionId) {
            $this->transfer = [];
        } elseif (!$this->transaction) {
            $this->transaction = $this->paymentGateway->getTransaction(
                $transactionId
            )->toArray();
        }

        return $this->getPath((object) $this->transaction, $path);
    }

    /**
     * Get event transfer data
     *
     * @param string $path key path
     *
     * @return mixed
     */
    public function getTransfer($path = null)
    {
        $transferId = $this->getCharge('transfer');

        if (!$transferId) {
            $this->transfer = [];
        } elseif (!$this->transfer) {
            $this->transfer = $this->paymentGateway->getTransfer(
                $transferId
            )->toArray();
        }

        return $this->getPath((object) $this->transfer, $path);
    }

    /**
     * Get event status data type
     *
     * @return int
     */
    public function getStatus()
    {
        $status = $this->getData('status');
        $type = config('constants.payment_statuses');

        $statuses = [
            'canceled' => $type['CANCELED'],
            'processing' => $type['PENDING'],
            'requires_action' => $type['FAILED'],
            'requires_capture' => $type['FAILED'],
            'requires_confirmation' => $type['FAILED'],
            'requires_payment_method' => $type['FAILED'],
            'succeeded' => $type['SUCCESS']
        ];

        return $statuses[$status] ?? $type['FAILED'];
    }

    /**
     * Get event method data
     *
     * @return int
     */
    public function getMethod()
    {
        $method = $this->getCharge('payment_method_details.card');

        if (!$method) {
            return [];
        }

        return [
            'id' => $this->getCharge('payment_method'),
            'card' => $method->toArray()
        ];
    }

    /**
     * Get event type
     *
     * @return bool
     */
    public function isPaymentSuccessful(): bool
    {
        return $this->type === self::PAYMENT_SUCCESS;
    }

    /**
     * Get all event failure information
     *
     * @return array
     */
    public function getFailureData(): array
    {
        $outcome = $this->getCharge('outcome');
        $lastPaymentError = $this->getData('last_payment_error')
            ->toArray();
        unset($lastPaymentError['payment_method']);

        return [
            'api_version' => $this->getApiVersion(),
            'status' => $this->getData('status'),
            'failure_code' => $this->getCharge('failure_code'),
            'failure_message' => $this->getCharge('failure_message'),
            // check first if outcome has value as some cases doesn't provide this value e.g invalid 3D auth
            'outcome' => $outcome ? $outcome->toArray() : null,
            'last_payment_error' => $lastPaymentError,
        ];
    }
}
