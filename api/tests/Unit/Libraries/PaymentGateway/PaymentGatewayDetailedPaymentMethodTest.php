<?php

namespace Tests\Unit\Libraries\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod;
use Faker\Factory as FakerFactory;
use StdClass;
use TestCase;

class PaymentGatewayDetailedPaymentMethodTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayDetailedPaymentMethod
     */
    private $paymentGatewayDetailedPaymentMethod;

    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->paymentGatewayDetailedPaymentMethod = new PaymentGatewayDetailedPaymentMethod;
    }

    public function testSetterGetterJsonSerialize()
    {
        $paymentMethod = new StdClass;
        $paymentMethod->billing = new StdClass;
        $this->paymentGatewayDetailedPaymentMethod
            ->setId($paymentMethod->id = $this->faker->uuid())
            ->setUserId($paymentMethod->user_id = rand(10, 99))
            ->setPaymentGatewayPaymentMethodId($paymentMethod->payment_gateway_payment_method_id = 'pm_foo')
            ->setPaymentGatewayPaymentMethodType($paymentMethod->payment_gateway_payment_method_type = 'card')
            ->setPaymentGateway($paymentMethod->payment_gateway = 1)
            ->setAddressLine1($paymentMethod->billing->address_line1 = $this->faker->address())
            ->setAddressLine2($paymentMethod->billing->address_line2 = $this->faker->secondaryAddress())
            ->setCity($paymentMethod->billing->city = $this->faker->city())
            ->setState($paymentMethod->billing->state = $this->faker->state())
            ->setPostalCode($paymentMethod->billing->postal_code = $this->faker->postcode())
            ->setCountry($paymentMethod->billing->country = $this->faker->countryCode());
        $paymentMethod->card = new StdClass;
        $paymentMethod->card->name = $this->faker->name();
        $paymentMethod->card->email = $this->faker->email();
        $paymentMethod->card->phone = $this->faker->phoneNumber();
        $paymentMethod->card->expire_month = rand(1, 12);
        $paymentMethod->card->expire_year = rand((int) date('Y'), 2099);
        $paymentMethod->card->card_brand = 'visa';
        $paymentMethod->card->last_4_digits = '1234';
        $details = json_decode(json_encode($paymentMethod->card), true);
        $this->paymentGatewayDetailedPaymentMethod
            ->setDetails($details);

        $this->assertSame($paymentMethod->id, $this->paymentGatewayDetailedPaymentMethod->getId());
        $this->assertSame($paymentMethod->user_id, $this->paymentGatewayDetailedPaymentMethod->getUserId());
        $this->assertSame($paymentMethod->payment_gateway_payment_method_id, $this->paymentGatewayDetailedPaymentMethod->getPaymentGatewayPaymentMethodId());
        $this->assertSame($paymentMethod->payment_gateway_payment_method_type, $this->paymentGatewayDetailedPaymentMethod->getPaymentGatewayPaymentMethodType());
        $this->assertSame($paymentMethod->payment_gateway, $this->paymentGatewayDetailedPaymentMethod->getPaymentGateway());
        $this->assertSame($paymentMethod->billing->address_line1, $this->paymentGatewayDetailedPaymentMethod->getAddressLine1());
        $this->assertSame($paymentMethod->billing->address_line2, $this->paymentGatewayDetailedPaymentMethod->getAddressLine2());
        $this->assertSame($paymentMethod->billing->city, $this->paymentGatewayDetailedPaymentMethod->getCity());
        $this->assertSame($paymentMethod->billing->state, $this->paymentGatewayDetailedPaymentMethod->getState());
        $this->assertSame($paymentMethod->billing->postal_code, $this->paymentGatewayDetailedPaymentMethod->getPostalCode());
        $this->assertSame($paymentMethod->billing->country, $this->paymentGatewayDetailedPaymentMethod->getCountry());
        $this->assertEquals($details, $this->paymentGatewayDetailedPaymentMethod->getDetails());

        $paymentMethod->payment_gateway = 'STRIPE';  // numeric payment gateway is transformed to its name.
        $jsonPaymentMethod = json_decode(json_encode($paymentMethod), true);
        $this->assertEquals($jsonPaymentMethod, $this->paymentGatewayDetailedPaymentMethod->jsonSerialize());
    }
}
