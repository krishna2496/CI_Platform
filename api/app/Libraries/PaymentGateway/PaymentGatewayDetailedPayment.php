<?php

namespace App\Libraries\PaymentGateway;

use App\Libraries\Amount;
use App\Models\PaymentGateway\Payment;
use JsonSerializable;

class PaymentGatewayDetailedPayment implements JsonSerializable
{
    /**
     * @var string
     */
    private $paymentGatewayPaymentId; // primary reference for payment gateway's payment object

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $missionId;

    /**
     * @var string
     */
    private $organizationId;

    /**
     * @var int
     */
    private $tenantId;

    /**
     * @var string
     */
    private $connectedAccountId;

    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string
     */
    private $paymentMethodId;

    /**
     * @var App\Models\PaymentGateway\Payment
     */
    private $payment;

    /**
     * @var int
     */
    private $amountDonated;

    /**
     * @var bool
     */
    private $hundredPercentSetting;

    /**
     * @return int
     */
    public function getPaymentGatewayPaymentId()
    {
        return $this->paymentGatewayPaymentId;
    }

    /**
     * @param int
     * @return PaymentGatewayDetailedPayment
     */
    public function setPaymentGatewayPaymentId($paymentGatewayPaymentId): PaymentGatewayDetailedPayment
    {
        if (!empty($paymentGatewayPaymentId)) {
            $this->paymentGatewayPaymentId = $paymentGatewayPaymentId;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param int
     * @return PaymentGatewayDetailedPayment
     */
    public function setClientSecret($clientSecret): PaymentGatewayDetailedPayment
    {
        if (!empty($clientSecret)) {
            $this->clientSecret = $clientSecret;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int
     * @return PaymentGatewayDetailedPayment
     */
    public function setUserId($userId): PaymentGatewayDetailedPayment
    {
        if (!empty($userId)) {
            $this->userId = $userId;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getMissionId()
    {
        return $this->missionId;
    }

    /**
     * @param int
     * @return PaymentGatewayDetailedPayment
     */
    public function setMissionId($missionId): PaymentGatewayDetailedPayment
    {
        if (!empty($missionId)) {
            $this->missionId = $missionId;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param string $organizationId
     * @return PaymentGatewayDetailedPayment
     */
    public function setOrganizationId($organizationId): PaymentGatewayDetailedPayment
    {
        $this->organizationId = $organizationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * @param string $tenantId
     * @return PaymentGatewayDetailedPayment
     */
    public function setTenantId($tenantId): PaymentGatewayDetailedPayment
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    /**
     * @return string
     */
    public function getConnectedAccountId()
    {
        return $this->connectedAccountId;
    }

    /**
     * @param string
     * @return PaymentGatewayDetailedPayment
     */
    public function setConnectedAccountId($connectedAccountId): PaymentGatewayDetailedPayment
    {
        if (!empty($connectedAccountId)) {
            $this->connectedAccountId = $connectedAccountId;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string
     * @return PaymentGatewayDetailedPayment
     */
    public function setCustomerId($customerId): PaymentGatewayDetailedPayment
    {
        if (!empty($customerId)) {
            $this->customerId = $customerId;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethodId()
    {
        return $this->paymentMethodId;
    }

    /**
     * @param string
     * @return PaymentGatewayDetailedPayment
     */
    public function setPaymentMethodId($paymentMethodId): PaymentGatewayDetailedPayment
    {
        if (!empty($paymentMethodId)) {
            $this->paymentMethodId = $paymentMethodId;
        }
        return $this;
    }

    /**
     * @return App\Models\PaymentGateway\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param App\Models\PaymentGateway\Payment
     * @return PaymentGatewayDetailedPayment
     */
    public function setPayment(Payment $payment): PaymentGatewayDetailedPayment
    {
        if (!empty($payment)) {
            $this->payment = $payment;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountDonated()
    {
        return $this->amountDonated;
    }

    /**
     * @param bool $amountDonated
     * @return PaymentGatewayDetailedPayment
     */
    public function setAmountDonated($amountDonated): PaymentGatewayDetailedPayment
    {
        if (!empty($amountDonated)) {
            $this->amountDonated = $amountDonated;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getHundredPercentSetting()
    {
        return $this->hundredPercentSetting;
    }

    /**
     * @param bool $hundredPercentSetting
     * @return PaymentGatewayDetailedPayment
     */
    public function setHundredPercentSetting($hundredPercentSetting): PaymentGatewayDetailedPayment
    {
        $this->hundredPercentSetting = $hundredPercentSetting;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'payment_gateway_payment_id' => $this->getPaymentGatewayPaymentId(),
            'user_id' => $this->getUserId(),
            'mission_id' => $this->getMissionId(),
            'organization_id' => $this->getOrganizationId(),
            'tenant_id' => $this->tenantId,
            'connected_account_id' => $this->getConnectedAccountId(),
            'customer_id' => $this->getCustomerId(),
            'payment_method_id' => $this->getPaymentMethodId(),
            'payment' => $this->getPayment(),
            'amount_donated' => $this->getAmountDonated(),
            'hundred_percent_setting' => $this->getHundredPercentSetting()
        ];
    }
}
