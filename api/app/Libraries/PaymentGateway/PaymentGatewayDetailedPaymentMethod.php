<?php

namespace App\Libraries\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use JsonSerializable;

class PaymentGatewayDetailedPaymentMethod implements JsonSerializable
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
    private $paymentGatewayPaymentMethodId;

    /**
     * @var string
     */
    private $paymentGatewayPaymentMethodType;

    /**
     * @var int
     */
    private $paymentGateway;

    /**
     * @var string
     */
    private $addressLine1;

    /**
     * @var string
     */
    private $addressLine2;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string (3 letter ISO code)
     */
    private $country;

    /**
     * @var array|null
     */
    private $details;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setId(?string $id): PaymentGatewayDetailedPaymentMethod
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
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setUserId(?int $userId): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($userId)) {
            $this->userId = $userId;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentGatewayPaymentMethodId(): ?string
    {
        return $this->paymentGatewayPaymentMethodId;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setPaymentGatewayPaymentMethodId(
        ?string $paymentGatewayPaymentMethodId
    ): PaymentGatewayDetailedPaymentMethod {
        if (!empty($paymentGatewayPaymentMethodId)) {
            $this->paymentGatewayPaymentMethodId = $paymentGatewayPaymentMethodId;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentGatewayPaymentMethodType(): ?string
    {
        return $this->paymentGatewayPaymentMethodType;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setPaymentGatewayPaymentMethodType(?string $type): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($type)) {
            $this->paymentGatewayPaymentMethodType = $type;
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
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setPaymentGateway(?string $paymentGateway): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($paymentGateway)) {
            $this->paymentGateway = $paymentGateway;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setAddressLine1(?string $addressLine1): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($addressLine1)) {
            $this->addressLine1 = $addressLine1;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setAddressLine2(?string $addressLine2): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($addressLine2)) {
            $this->addressLine2 = $addressLine2;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setCity(?string $city): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($city)) {
            $this->city = $city;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setState(?string $state): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($state)) {
            $this->state = $state;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setPostalCode(?string $postalCode): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($postalCode)) {
            $this->postalCode = $postalCode;
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
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setCountry(?string $country): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($country)) {
            $this->country = $country;
        }
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * @param array|null
     * @return PaymentGatewayDetailedPaymentMethod
     */
    public function setDetails(?array $details): PaymentGatewayDetailedPaymentMethod
    {
        if (!empty($details)) {
            $this->details = $details;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $paymentGatewayFactory = new PaymentGatewayFactory;
        $details = $this->getDetails();
        $jsonSerialize = [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'payment_gateway_payment_method_id' => $this->getPaymentGatewayPaymentMethodId(),
            'payment_gateway_payment_method_type' => $this->getPaymentGatewayPaymentMethodType(),
            'payment_gateway' => $this->getPaymentGateway() ? $paymentGatewayFactory->getNameByType($this->getPaymentGateway()) : null,
            'billing' => [
                'address_line1' => $this->getAddressLine1(),
                'address_line2' => $this->getAddressLine2(),
                'city' => $this->getCity(),
                'state' => $this->getState(),
                'postal_code' => $this->getPostalCode(),
                'country' => $this->getCountry(),
            ],
            'card' => [
                'name' => $details['name'] ?? null,
                'email' => $details['email'] ?? null,
                'phone' => $details['phone'] ?? null,
                'expire_month' => $details['expire_month'] ?? null,
                'expire_year' => $details['expire_year'] ?? null,
                'card_brand' => $details['card_brand'] ?? null,
                'last_4_digits' => $details['last_4_digits'] ?? null
            ],
        ];
        return $jsonSerialize;
    }
}
