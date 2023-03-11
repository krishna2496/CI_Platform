<?php

namespace Tests\Unit\Repositories\PaymentGateway;

use App\Models\PaymentGateway\Payment;
use App\Repositories\PaymentGateway\PaymentRepository;
use Faker\Factory as FakerFactory;
use Mockery;
use TestCase;

class PaymentRepositoryTest extends TestCase
{
    /**
     * @var App\Repositories\PaymentGateway\PaymentRepository
     */
    private $repository;

    /**
     * @var App\Models\PaymentGateway\Payment
     */
    private $payment;

    /**
     * @var Faker
     */
    private $faker;


    public function setUp(): void
    {
        $this->payment = $this->mock(Payment::class);
        $this->faker = FakerFactory::create();

        $this->repository = new PaymentRepository(
            $this->payment
        );
    }

    /**
     * @testdox Test create method on PaymentRepository Class
     */
    public function testCreate()
    {
        $data = [
            'payment_gateway' => 1,
            'payment_gateway_payment_id' => $this->faker->uuid,
            'payment_method_type' => 'CARD',
            'currency' => $this->faker->currencyCode,
            'amount' => 100,
            'transfer_amount' => 100,
            'billing_name' => $this->faker->name,
            'billing_email' => $this->faker->email,
            'billing_phone' => $this->faker->phoneNumber,
            'billing_address_line_1' => $this->faker->address,
            'billing_address_line_2' => $this->faker->secondaryAddress,
            'billing_city' => $this->faker->city,
            'billing_state' => $this->faker->state,
            'billing_country' => $this->faker->countryCode,
            'billing_postal_code' => $this->faker->postcode,
            'ip_address' => $this->faker->ipv4
        ];

        $payment = (new Payment())
            ->setAttribute('id', $this->faker->uuid)
            ->setAttribute('payment_gateway', $data['payment_gateway'])
            ->setAttribute('payment_gateway_payment_id', $data['payment_gateway_payment_id'])
            ->setAttribute('payment_method_type', $data['payment_method_type'])
            ->setAttribute('currency', $data['currency'])
            ->setAttribute('amount', $data['amount'])
            ->setAttribute('transfer_amount', $data['transfer_amount'])
            ->setAttribute('billing_name', $data['billing_name'])
            ->setAttribute('billing_email', $data['billing_email'])
            ->setAttribute('billing_phone', $data['billing_phone'])
            ->setAttribute('billing_address_line_1', $data['billing_address_line_1'])
            ->setAttribute('billing_address_line_2', $data['billing_address_line_2'])
            ->setAttribute('billing_city', $data['billing_city'])
            ->setAttribute('billing_state', $data['billing_state'])
            ->setAttribute('billing_country', $data['billing_country'])
            ->setAttribute('billing_postal_code', $data['billing_postal_code'])
            ->setAttribute('ip_address', $data['ip_address']);

        $paymentData = $payment->toArray();
        $paymentData['amount'] = $payment->amount->getValue();
        $paymentData['transfer_amount'] = $payment->transfer_amount->getValue();

        $this->payment
            ->shouldReceive('create')
            ->once()
            ->with($paymentData)
            ->andReturn($payment);

        $response = $this->repository->create($payment);

        $this->assertSame($response, $payment);
    }

    /**
     * @testdox Test update method on PaymentRepository Class
     */
    public function testUpdate()
    {
        $expected = true;
        $paymentGatewayPaymentId = $this->faker->uuid;

        $payment = new Payment();
        $payment->setAttribute(
            'payment_gateway_payment_id',
            $paymentGatewayPaymentId
        );

        $this->payment
            ->shouldReceive('where')
            ->once()
            ->with('payment_gateway_payment_id', $payment->payment_gateway_payment_id)
            ->andReturnSelf()
            ->shouldReceive('update')
            ->once()
            ->with($payment->toArray())
            ->andReturn($expected);

        $response = $this->repository->update(
            $payment
        );

        $this->assertSame($response, $expected);
    }

    /**
     * Mock an object
     *
     * @param string name
     *
     * @return Mockery
     */
    private function mock($class)
    {
        return Mockery::mock($class);
    }
}
