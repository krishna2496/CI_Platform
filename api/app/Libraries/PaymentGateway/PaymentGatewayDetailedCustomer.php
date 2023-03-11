<?php

namespace App\Libraries\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use JsonSerializable;

class PaymentGatewayDetailedCustomer implements JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $paymentGatewayCustomerId;

    /**
     * @var int
     */
    private $paymentGateway;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $billingEmail;

    /**
     * @var string
     */
    private $languageCode;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setId(?string $id): PaymentGatewayDetailedCustomer
    {
        if (!empty($id)) {
            $this->id = $id;
        }
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setUserId(?int $userId): PaymentGatewayDetailedCustomer
    {
        if (!empty($userId)) {
            $this->userId = $userId;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentGatewayCustomerId(): ?string
    {
        return $this->paymentGatewayCustomerId;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setPaymentGatewayCustomerId(?string $paymentGatewayCustomerId): PaymentGatewayDetailedCustomer
    {
        if (!empty($paymentGatewayCustomerId)) {
            $this->paymentGatewayCustomerId = $paymentGatewayCustomerId;
        }
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPaymentGateway(): ?int
    {
        return $this->paymentGateway;
    }

    /**
     * @param int|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setPaymentGateway(?int $paymentGateway): PaymentGatewayDetailedCustomer
    {
        if (!empty($paymentGateway)) {
            $this->paymentGateway = $paymentGateway;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setName(?string $name): PaymentGatewayDetailedCustomer
    {
        if (!empty($name)) {
            $this->name = $name;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setEmail(?string $email): PaymentGatewayDetailedCustomer
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $this->email = $email;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBillingEmail(): ?string
    {
        return $this->billingEmail;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setBillingEmail(?string $billingEmail): PaymentGatewayDetailedCustomer
    {
        if (filter_var($billingEmail, FILTER_VALIDATE_EMAIL) !== false) {
            $this->billingEmail = $billingEmail;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedCustomer
     */
    public function setLanguageCode(?string $languageCode): PaymentGatewayDetailedCustomer
    {
        if (!empty($languageCode)) {
            $this->languageCode = $languageCode;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $paymentGatewayFactory = new PaymentGatewayFactory;
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'payment_gateway_customer_id' => $this->getPaymentGatewayCustomerId(),
            'payment_gateway' => $this->getPaymentGateway() ? $paymentGatewayFactory->getNameByType($this->getPaymentGateway()) : null,
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'billing_email' => $this->getBillingEmail(),
            'language_code' => $this->getLanguageCode(),
        ];
    }
}
