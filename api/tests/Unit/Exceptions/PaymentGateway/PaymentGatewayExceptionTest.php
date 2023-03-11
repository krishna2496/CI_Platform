<?php

namespace Tests\Unit\Exceptions\PaymentGateway;

use App\Exceptions\PaymentGateway\PaymentGatewayException;
use Exception;
use Faker\Factory as FakerFactory;
use StdClass;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use TestCase;

class PaymentGatewayExceptionTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayException
     */
    private $paymentGatewayException;

    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->paymentGatewayException = new PaymentGatewayException;
    }

    public function testSetterGetter()
    {
        $paymentGatewayException = (new PaymentGatewayException)
            ->setPaymentGateway(config('constants.payment_gateway_types.STRIPE'));

        $this->assertSame(
            config('constants.payment_gateway_types.STRIPE'),
            $paymentGatewayException->getPaymentGateway()
        );
    }

    /**
     * @dataProvider  chainedExceptionData
     */
    public function testExceptionChaining(Exception $chainedException, string $exceptionClass)
    {
        $paymentGatewayException = new PaymentGatewayException('foo', 123, $chainedException);

        $this->assertInstanceOf($exceptionClass, $paymentGatewayException->getChainedException());
    }

    /**
     * Chained exceptions data provider.
     */
    public function chainedExceptionData(): array
    {
        return [
            [ new ApiConnectionException, ApiConnectionException::class ],
            [ new AuthenticationException, AuthenticationException::class ],
            [ new CardException, CardException::class ],
            [ new InvalidRequestException, InvalidRequestException::class ],
            [ new RateLimitException, RateLimitException::class ],
            [ new Exception, Exception::class ],
        ];
    }
}
