<?php

namespace Tests\Unit\Libraries\PaymentGateway;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\PaymentGatewayInterface;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;
use TestCase;

class PaymentGatewayFactoryTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayInterface
     */
    private $paymentGatewayFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->paymentGatewayFactory = new PaymentGatewayFactory;
    }

    public function testGetNameByTypeSuccess()
    {
        $type = config('constants.payment_gateway_types.STRIPE');
        $name = $this->paymentGatewayFactory->getNameByType($type);
        $this->assertSame('STRIPE', $name);
    }

    public function testGetNameByTypeFail()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->paymentGatewayFactory->getNameByType(99);
    }

    public function testGetPaymentGatewayNoArgument()
    {
        $paymentGateway = $this->paymentGatewayFactory->getPaymentGateway();
        $this->assertInstanceOf(PaymentGatewayInterface::class, $paymentGateway);
        $this->assertInstanceOf(StripePaymentGateway::class, $paymentGateway);
    }

    public function testGetPaymentGatewayForStripe()
    {
        $type = config('constants.payment_gateway_types.STRIPE');
        $paymentGateway = $this->paymentGatewayFactory->getPaymentGateway($type);
        $this->assertSame(1, $type);
        $this->assertInstanceOf(PaymentGatewayInterface::class, $paymentGateway);
        $this->assertInstanceOf(StripePaymentGateway::class, $paymentGateway);
    }

    public function testGetPaymentGatewayUnknownType()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->expectExceptionMessage('Invalid payment gateway type. [99]');
        $unknownType = 99;
        $paymentGateway = $this->paymentGatewayFactory->getPaymentGateway($unknownType);
    }
}
