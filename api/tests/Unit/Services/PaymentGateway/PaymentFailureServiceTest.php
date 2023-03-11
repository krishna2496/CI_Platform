<?php

namespace Tests\Unit\Services\PaymentGateway;

use App\Models\PaymentGateway\PaymentFailure;
use App\Repositories\PaymentGateway\PaymentFailureRepository;
use App\Services\PaymentGateway\PaymentFailureService;
use Faker\Factory as FakerFactory;
use Mockery;
use TestCase;

class PaymentFailureServiceTest extends TestCase
{
    /**
     * @var Faker
     */
    private $faker;

    /**
     * @var App\Services\PaymentGateway\PaymentFailureService
     */
    private $paymentFailureService;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
        $this->paymentFailureRepository = $this->mock(PaymentFailureRepository::class);
    }

    /**
     * @testdox Test create method on PaymentService class
     */
    public function testCreate()
    {
        $failureData = $this->getFailureDataFixture();

        $paymentFailure = (new PaymentFailure())
            ->setAttribute('payment_gateway_payment_id', 'pi_xxxxxxxxxxxxxxxx')
            ->setAttribute('failure_data', $failureData);

        $this->paymentFailureRepository
            ->shouldReceive('create')
            ->once()
            ->with($paymentFailure)
            ->andReturn($paymentFailure);

        $paymentFailureService = new PaymentFailureService($this->paymentFailureRepository);
        $response = $paymentFailureService->create($paymentFailure);

        $this->assertSame($response, $paymentFailure);
    }

    /**
     * Payment failure fixture data
     *
     * @return array
     */
    private function getFailureDataFixture(): array
    {
        return [
            'status' => 'requires_payment_method',
            'failure_code' => 'card_declined',
            'failure_message' => 'Your card was declined.',
            'outcome' => [
                'network_status' => 'declined_by_network',
                'reason' => 'generic_decline',
                'risk_level' => 'normal',
                'risk_score' => 46,
                'seller_message' => 'The bank did not return any further details with this decline.',
                'type' => 'issuer_declined',
            ],
            'last_payment_error' => [
                'charge' => 'ch_xxxxxxxxxxxxxxxx',
                'code' => 'card_declined',
                'decline_code' => 'generic_decline',
                'doc_url' => 'https://www.stripe.com/docs/error-codes/card-declined',
                'message' => 'Your card was declined.',
                'payment_method' => [
                    'id' => 'pm_xxxxxxxxxxxxxxxx',
                    'billing_details' => [
                        'address' => [
                            'city' => $this->faker->city(),
                            'country' => $this->faker->country(),
                            'line1' => $this->faker->streetAddress(),
                            'line2' => $this->faker->secondaryAddress(),
                            'postal_code' => $this->faker->postcode(),
                            'state' => $this->faker->state(),
                        ],
                        'email' => $this->faker->email(),
                        'name' => $this->faker->name(),
                        'phone' => $this->faker->e164PhoneNumber(),
                    ],
                    'card' => [
                        'brand' => 'visa',
                        'checks' => [
                            'address_line1_check' => null,
                            'address_postal_code_check' => null,
                            'cvc_check' => null,
                        ],
                        'country' => 'US',
                        'exp_month' => 12,
                        'exp_year' => 2030,
                        'fingerprint' => 'xxxxxxxxxxxxxxxx',
                        'funding' => 'credit',
                        'last4' => '2222',
                        'networks' => [
                            'available' => [
                                'visa',
                            ],
                        ],
                        'three_d_secure_usage' => [
                            'supported' => true,
                        ],
                        'wallet' => null,
                    ],
                    'created' => $this->faker->unixTime(),
                    'customer' => null,
                    'metadata' => [
                        'tenant_id' => $this->faker->numberBetween(1000, 9999),
                        'mission_id' => $this->faker->numberBetween(1000, 9999),
                        'organization_id' => $this->faker->uuid(),
                    ],
                    'type' => 'card',
                ],
                'type' => 'card_error',
            ],
        ];
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
