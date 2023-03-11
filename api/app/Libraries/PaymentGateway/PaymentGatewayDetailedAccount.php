<?php

namespace App\Libraries\PaymentGateway;

use JsonSerializable;

class PaymentGatewayDetailedAccount implements JsonSerializable
{
    /**
     * @var string
     */
    private $organizationId;

    /**
     * @var string
     */
    private $paymentGatewayAccountId;

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
    private $country;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @var bool
     */
    private $payoutsEnabled;

    /**
     * @return string|null
     */
    public function getOrganizationId(): ?string
    {
        return $this->organizationId;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedAccount
     */
    public function setOrganizationId(?string $organizationId): PaymentGatewayDetailedAccount
    {
        if (!empty($organizationId)) {
            $this->organizationId = $organizationId;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentGatewayAccountId(): ?string
    {
        return $this->paymentGatewayAccountId;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedAccount
     */
    public function setPaymentGatewayAccountId(?string $paymentGatewayAccountId): PaymentGatewayDetailedAccount
    {
        $this->paymentGatewayAccountId = $paymentGatewayAccountId;
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
     * @return PaymentGatewayDetailedAccount
     */
    public function setName(?string $name): PaymentGatewayDetailedAccount
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
     * @return PaymentGatewayDetailedAccount
     */
    public function setEmail(?string $email): PaymentGatewayDetailedAccount
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $this->email = $email;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedAccount
     */
    public function setCountry(?string $country): PaymentGatewayDetailedAccount
    {
        if (!empty($country)) {
            $this->country = $country;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDefaultCurrency(): ?string
    {
        return $this->defaultCurrency;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedAccount
     */
    public function setDefaultCurrency(?string $defaultCurrency): PaymentGatewayDetailedAccount
    {
        if (!empty($defaultCurrency)) {
            $this->defaultCurrency = $defaultCurrency;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getPayoutsEnabled(): bool
    {
        return (bool) $this->payoutsEnabled;
    }

    /**
     * @param mixed
     * @return PaymentGatewayDetailedAccount
     */
    public function setPayoutsEnabled($payoutsEnabled): PaymentGatewayDetailedAccount
    {
        $payoutsEnabled = filter_var($payoutsEnabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->payoutsEnabled = $payoutsEnabled;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'organization_id' => $this->getOrganizationId(),
            'payment_gateway_account_id' => $this->getPaymentGatewayAccountId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'country' => $this->getCountry(),
            'default_currency' => $this->getDefaultCurrency(),
            'payouts_enabled' => $this->getPayoutsEnabled(),
        ];
    }
}
