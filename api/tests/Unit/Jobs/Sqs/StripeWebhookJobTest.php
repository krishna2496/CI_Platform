<?php

namespace Tests\Unit\Jobs\Sqs;

use App\Helpers\Helpers;
use App\Jobs\Job;
use App\Jobs\Sqs\StripeWebhookJob;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedTransaction;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedTransfer;
use App\Libraries\PaymentGateway\Stripe\Events\Event;
use App\Libraries\PaymentGateway\Stripe\Events\PaymentEvent;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;
use App\Models\PaymentGateway\Payment;
use App\Models\PaymentGateway\PaymentFailure;
use App\Services\PaymentGateway\PaymentFailureService;
use App\Services\PaymentGateway\PaymentService;
use Exception;
use Faker\Factory as FakerFactory;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Support\Facades\Log;
use Mockery;
use TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class StripeWebhookJobTest extends TestCase
{
    /**
     * App\Services\PaymentGateway\PaymentService
     */
    private $paymentService;

    /**
     * App\Helpers\Helpers
     */
    private $helpers;

    /**
     * App\Jobs\Sqs\StripeWebhookJob
     */
    private $stripeWebhookJob;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
        $this->paymentService = $this->mock(PaymentService::class);
        $this->paymentFailureService = $this->mock(PaymentFailureService::class);
        $this->helpers = $this->mock(Helpers::class);

        $this->stripeWebhookJob = new StripeWebhookJob(
            $this->paymentService,
            $this->paymentFailureService,
            $this->helpers
        );
    }

    /**
     * @testdox Test handle method on Event Class
     */
    public function testHandleSuccess()
    {
        $data = $this->eventData();
        $queueJob = $this->mock(QueueJob::class);
        $tenantId = $data['data']['object']['metadata']['tenant_id'];
        $transactionId = $data['data']['object']['charges']['data'][0]['balance_transaction'];
        $transferId = $data['data']['object']['charges']['data'][0]['transfer'];

        $stripePayment = $this->mock(StripePaymentGateway::class);
        $stripePayment
            ->shouldReceive('getTransaction')
            ->once()
            ->with($transactionId)
            ->andReturn(new PaymentGatewayDetailedTransaction);
        $stripePayment
            ->shouldReceive('getTransfer')
            ->once()
            ->with($transferId)
            ->andReturn(new PaymentGatewayDetailedTransfer);

        $payment = $this->mock('overload:App\Libraries\PaymentGateway\PaymentGatewayFactory');
        $payment->shouldReceive('getPaymentGateway')
            ->once()
            ->andReturn($stripePayment);

        $this->helpers
            ->shouldReceive('createConnection')
            ->once()
            ->with((int) $tenantId)
            ->andReturn(true);

        $this->paymentService
            ->shouldReceive('update')
            ->once()
            ->andReturn(true);

        Log::shouldReceive('info')
            ->with('STRIPE EVENT:', $data);

        $response = $this->stripeWebhookJob->handle(
            $queueJob,
            $data
        );
    }

    /**
     * @testdox Test handle method on Event Class
     */
    public function testHandleFailure()
    {
        $event = $this->getFailureEventFixture();
        $queueJob = $this->mock(QueueJob::class);
        $eventData = $event['data']['object'];
        $tenantId = $eventData['metadata']['tenant_id'];
        $chargesData = $eventData['charges']['data']['0'];
        $failureData = [
            'status' => $eventData['status'],
            'failure_code' => $chargesData['failure_code'],
            'failure_message' => $chargesData['failure_message'],
            'outcome' => $chargesData['outcome'],
            'last_payment_error' => $eventData['last_payment_error'],
        ];

        Log::shouldReceive('info')
            ->with('STRIPE EVENT:', $event);

        $this->helpers
            ->shouldReceive('createConnection')
            ->once()
            ->with((int) $tenantId)
            ->andReturn(true);

        $paymentEvent = $this->mock('overload:App\Libraries\PaymentGateway\Stripe\Events\PaymentEvent');
        $paymentEvent
            ->shouldReceive('constructFrom')
            ->once()
            ->with($event)
            ->andReturn($paymentEvent);
        $paymentEvent
            ->shouldReceive('getData')
            ->once()
            ->with('id');
        $paymentEvent
            ->shouldReceive('getStatus')
            ->once()
            ->andReturn(config('constants.payment_statuses.FAILED'));
        $paymentEvent
            ->shouldReceive('getFailureData')
            ->once()
            ->andReturn($failureData);

        $paymentEvent
            ->shouldReceive('constructFrom')
            ->once()
            ->with($this->eventData())
            ->andReturn($paymentEvent);
        $paymentEvent
            ->shouldReceive('getCharge')
            ->once()
            ->with('payment_method_details.card');
        $paymentEvent
            ->shouldReceive('getMethod')
            ->once();
        $paymentEvent
            ->shouldReceive('getTransaction')
            ->once()
            ->with('currency')
            ->andReturn('PHP');
        $paymentEvent
            ->shouldReceive('getTransaction')
            ->once()
            ->with('amount')
            ->andReturn('1234.56');
        $paymentEvent
            ->shouldReceive('getTransfer')
            ->once()
            ->with('amount')
            ->andReturn('1234.56');
        $paymentEvent
            ->shouldReceive('getTransaction')
            ->once()
            ->with('exchange_rate')
            ->andReturn('12.34');
        $paymentEvent
            ->shouldReceive('getTransaction')
            ->once()
            ->with('fee')
            ->andReturn('1.234');
        $paymentEvent
            ->shouldReceive('isPaymentSuccessful')
            ->once()
            ->andReturn(false);

        $paymentFailure = (new PaymentFailure)
            ->setAttribute('payment_gateway_payment_id', 'pi_xxxxxxxxxxxxxxxx')
            ->setAttribute('failure_data', $failureData);
        $this->paymentFailureService
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::type(PaymentFailure::class))
            ->andReturn($paymentFailure);

        $payment = (new Payment)
            ->setAttribute('status', 123);
        $this->paymentService
            ->shouldReceive('update')
            ->once()
            ->with(Mockery::type(Payment::class))
            ->andReturn($payment);

        $this->stripeWebhookJob->handle(
            $queueJob,
            $event
        );
    }

    /**
     * @testdox Test handle method on Event Class with exception
     */
    public function testHandleException()
    {
        $this->expectException(Exception::class);

        $data = [];
        $queueJob = $this->mock(QueueJob::class);

        Log::shouldReceive('info')
            ->with('STRIPE EVENT:', $data);

        $response = $this->stripeWebhookJob->handle(
            $queueJob,
            $data
        );
    }

    /**
     * @testdox Test handle method on Event Class with tenant mission exception
     */
    public function testHandleTenantException()
    {
        $this->expectException(Exception::class);

        $data = $this->eventData();
        $data['data']['object']['metadata']['tenant_id'] = null;
        $queueJob = $this->mock(QueueJob::class);

        Log::shouldReceive('info')
            ->with('STRIPE EVENT:', $data);

        $response = $this->stripeWebhookJob->handle(
            $queueJob,
            $data
        );
    }

    /**
     * Get sample event data that the method will receive
     *
     * @return array
     */
    private function eventData()
    {
        return [
            'id' => 'evt_00000000000000',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_00000000000000',
                    'object' => 'payment_intent',
                    'charges' => [
                        'object' => 'list',
                        'data' => [
                            [
                                'id' => 'ch_0000000000',
                                'object' => 'charge',
                                'amount' => 10553,
                                'balance_transaction' => 'txn_0000000000000000',
                                'billing_details' => [
                                    'address' => [
                                        'city' => 'Cityqs',
                                        'country' => 'BE',
                                        'line1' => 'line 2',
                                        'line2' => 'line 3',
                                        'postal_code' => '11111',
                                        'state' => 'state',
                                    ],
                                    'email' => '0000000@optimy.com',
                                    'name' => 'Sample name',
                                    'phone' => null,
                                ],
                                'payment_method' => 'pm_00000000000',
                                'payment_method_details' => [
                                    'card' => [
                                        'brand' => 'visa',
                                        'checks' => [
                                            'address_line1_check' => 'pass',
                                            'address_postal_code_check' => 'pass',
                                            'cvc_check' => null,
                                        ],
                                        'country' => 'US',
                                        'exp_month' => 12,
                                        'exp_year' => 2024,
                                        'fingerprint' => '0000000000',
                                        'funding' => 'credit',
                                        'installments' => null,
                                        'last4' => '4242',
                                        'network' => 'visa',
                                        'three_d_secure' => [
                                            'authenticated' => false,
                                            'authentication_flow' => null,
                                            'result' => 'attempt_acknowledged',
                                            'result_reason' => null,
                                            'succeeded' => true,
                                            'version' => '1.0.2',
                                        ],
                                        'wallet' => null,
                                    ],
                                    'type' => 'card',
                                ],
                                'status' => 'succeeded',
                                'transfer' => 'tr_000000000000',
                                'transfer_data' => [
                                    'amount' => 10000,
                                    'destination' => 'acct_000000000',
                                ]
                            ]
                        ],
                        'has_more' => false,
                        'url' => '/v1/charges?payment_intent=0000000000000000'
                    ],
                    'metadata' => [
                      'tenant_id' => '34',
                      'mission_id' => '10',
                      'organization_id' => '9012929-ASDASD9AA-ASDASD-ASDASD-ASD'
                    ],
                    'status' => 'requires_payment_method',
                    'transfer_data' => null,
                    'transfer_group' => null
                ]
            ]
        ];
    }

    /**
     * Get sample event data that the method will receive
     *
     * @return array
     */
    private function getFailureEventFixture(): array
    {
        return [
            'id' => 'evt_xxxxxxxxxxxxxxxx',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_xxxxxxxxxxxxxxxx',
                    'object' => 'payment_intent',
                    'charges' => [
                        'object' => 'list',
                        'data' => [
                            [
                                'id' => 'ch_xxxxxxxxxxxxxxxx',
                                'object' => 'charge',
                                'amount' => $this->faker->randomNumber(),
                                'balance_transaction' => 'txn_xxxxxxxxxxxxxxxx',
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
                                'payment_method' => 'pm_xxxxxxxxxxxxxxxx',
                                'payment_method_details' => [
                                    'card' => [
                                        'brand' => 'visa',
                                        'checks' => [
                                            'address_line1_check' => 'pass',
                                            'address_postal_code_check' => 'pass',
                                            'cvc_check' => null,
                                        ],
                                        'country' => 'US',
                                        'exp_month' => 12,
                                        'exp_year' => 2030,
                                        'fingerprint' => 'xxxxxxxxxxxxxxxx',
                                        'funding' => 'credit',
                                        'installments' => null,
                                        'last4' => '2222',
                                        'network' => 'visa',
                                        'three_d_secure' => [
                                            'authenticated' => false,
                                            'authentication_flow' => null,
                                            'result' => 'attempt_acknowledged',
                                            'result_reason' => null,
                                            'succeeded' => true,
                                            'version' => '1.0.2',
                                        ],
                                        'wallet' => null,
                                    ],
                                    'type' => 'card',
                                ],
                                'status' => 'succeeded',
                                'transfer' => 'tr_xxxxxxxxxxxxxxxx',
                                'transfer_data' => [
                                    'amount' => $this->faker->randomNumber(),
                                    'destination' => 'acct_xxxxxxxxxxxxxxxx',
                                ],
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
                            ],
                        ],
                        'has_more' => false,
                        'url' => '/v1/charges?payment_intent=xxxxxxxxxxxxxxxx'
                    ],
                    'metadata' => [
                        'tenant_id' => $this->faker->numberBetween(1000, 9999),
                        'mission_id' => $this->faker->numberBetween(1000, 9999),
                        'organization_id' => $this->faker->uuid(),
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
                        'type' => 'card_error'
                    ],
                    'status' => 'requires_payment_method',
                    'transfer_data' => null,
                    'transfer_group' => null
                ]
            ]
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
