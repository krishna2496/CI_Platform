<?php

namespace Tests\Unit\Libraries\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer;
use Faker\Factory as FakerFactory;
use StdClass;
use TestCase;

class PaymentGatewayDetailedCustomerTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayDetailedCustomer
     */
    private $paymentGatewayDetailedCustomer;

    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->paymentGatewayDetailedCustomer = new PaymentGatewayDetailedCustomer;
    }

    public function testSetterGetterJsonSerialize()
    {
        $customer = new StdClass;
        $this->paymentGatewayDetailedCustomer
            ->setId($customer->id = $this->faker->uuid())
            ->setUserId($customer->user_id = rand(10, 99))
            ->setPaymentGatewayCustomerId($customer->payment_gateway_customer_id = 'cus_foo')
            ->setPaymentGateway($customer->payment_gateway = 1)
            ->setName($customer->name = $this->faker->name())
            ->setEmail($customer->email = $this->faker->email())
            ->setBillingEmail($customer->billing_email = $this->faker->email())
            ->setLanguageCode($customer->language_code = $this->faker->languageCode());

        $this->assertSame($customer->id, $this->paymentGatewayDetailedCustomer->getId());
        $this->assertSame($customer->user_id, $this->paymentGatewayDetailedCustomer->getUserId());
        $this->assertSame($customer->payment_gateway_customer_id, $this->paymentGatewayDetailedCustomer->getPaymentGatewayCustomerId());
        $this->assertSame($customer->payment_gateway, $this->paymentGatewayDetailedCustomer->getPaymentGateway());
        $this->assertSame($customer->name, $this->paymentGatewayDetailedCustomer->getName());
        $this->assertSame($customer->email, $this->paymentGatewayDetailedCustomer->getEmail());
        $this->assertSame($customer->billing_email, $this->paymentGatewayDetailedCustomer->getBillingEmail());
        $this->assertSame($customer->language_code, $this->paymentGatewayDetailedCustomer->getLanguageCode());

        $customer->payment_gateway = 'STRIPE';  // numeric payment gateway is transformed to its name.
        $jsonCustomer = json_decode(json_encode($customer), true);
        $this->assertSame($jsonCustomer, $this->paymentGatewayDetailedCustomer->jsonSerialize());
    }
}
