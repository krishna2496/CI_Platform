<?php

namespace Tests\Unit\Libraries\PaymentGateway\Stripe\Events;

use App\Libraries\Amount;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedTransaction;
use App\Libraries\PaymentGateway\PaymentGatewayDetailedTransfer;
use App\Libraries\PaymentGateway\PaymentGatewayFactory;
use App\Libraries\PaymentGateway\Stripe\Events\PaymentEvent;
use App\Libraries\PaymentGateway\Stripe\StripePaymentGateway;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;
use Mockery;
use Stripe\ErrorObject;
use TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PaymentEventTest extends TestCase
{
    /**
     * @var Faker
     */
    private $faker;

    /*
     * @var Event
     */
    private $event;

    /*
     * @var StripePaymentGateway
     */
    private $stripePayment;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
        $this->stripePayment = $this->mock(StripePaymentGateway::class);

        $payment = $this->mock('overload:App\Libraries\PaymentGateway\PaymentGatewayFactory');
        $payment->shouldReceive('getPaymentGateway')
            ->once()
            ->andReturn($this->stripePayment);

        $this->event = PaymentEvent::constructFrom(
            $this->eventData()
        );
    }

    /**
     * @testdox Test get data and charge method on PaymentEvent Class
     */
    public function testGetDataCharge()
    {
        $data = $this->flattenArrayKeys($this->eventData());

        foreach ($data as $path => $value) {
            if (!strstr($path, 'data.object')) {
                $this->assertSame($value, $this->event->$path);
                continue;
            }
            $path = str_replace('data.object.', null, $path);
            $method ='getData';
            if (strstr($path, 'charges.data.0')) {
                $method = 'getCharge';
                $path = str_replace('charges.data.0.', null, $path);
            }
            $this->assertSame($value, $this->event->$method($path));
        }
    }

    /**
     * @testdox Test get transaction method on PaymentEvent Class
     */
    public function testGetTransaction()
    {
        $transactionId = $this->eventData()['data']['object']['charges']['data'][0]['balance_transaction'];
        $transaction = new PaymentGatewayDetailedTransaction;

        $this->stripePayment
            ->shouldReceive('getTransaction')
            ->once()
            ->with($transactionId)
            ->andReturn($transaction);

        $result = $this->event->getTransaction();

        $this->assertEquals($result, (object) $transaction->toArray());
    }

    /**
     * @testdox Test get transfer method on PaymentEvent Class
     */
    public function testGetTransfer()
    {
        $transferId = $this->eventData()['data']['object']['charges']['data'][0]['transfer'];
        $transfer = new PaymentGatewayDetailedTransfer;

        $this->stripePayment
            ->shouldReceive('getTransfer')
            ->once()
            ->with($transferId)
            ->andReturn($transfer);

        $result = $this->event->getTransfer();

        $this->assertEquals($result, (object) $transfer->toArray());
    }

    /**
     * @testdox Test get status method on PaymentEvent Class
     */
    public function testGetStatus()
    {
        $type = config('constants.payment_statuses');

        $expected = [
            'canceled' => $type['CANCELED'],
            'processing' => $type['PENDING'],
            'requires_action' => $type['FAILED'],
            'requires_capture' => $type['FAILED'],
            'requires_confirmation' => $type['FAILED'],
            'requires_payment_method' => $type['FAILED'],
            'succeeded' => $type['SUCCESS']
        ];

        foreach ($expected as $status => $type) {
            $this->event->data->object->status = $status;
            $this->assertSame($type, $this->event->getStatus());
        }
    }

    /**
     * @testdox Test get method method on PaymentEvent Class
     */
    public function testGetMethod()
    {
        $methodId = $this->eventData()['data']['object']['charges']['data'][0]['payment_method'];
        $method = $this->eventData()['data']['object']['charges']['data'][0]['payment_method_details']['card'];
        $expected = [
            'id' => $methodId,
            'card' => $method
        ];

        $result = $this->event->getMethod();
        $this->assertEquals($result, $expected);
    }

    /**
     * @testdox Test is payment successful method on PaymentEvent Class
     */
    public function testIsPaymentSuccessful()
    {
        $paymentEvent = new PaymentEvent;

        $paymentEvent->type = PaymentEvent::PAYMENT_SUCCESS;
        $result = $paymentEvent->isPaymentSuccessful();
        $this->assertIsBool($result);
        $this->assertTrue($result);

        $paymentEvent->type = PaymentEvent::PAYMENT_FAILED;
        $result = $paymentEvent->isPaymentSuccessful();
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * @testdox Test get failure data method on PaymentEvent Class
     */
    public function testGetFailureData()
    {
        $event = $this->eventFailureData();
        $eventData = $event['data']['object'];
        $chargesData = $eventData['charges']['data']['0'];
        unset($eventData['last_payment_error']['payment_method']);
        $failureData = [
            'api_version' => $event['api_version'],
            'status' => $eventData['status'],
            'failure_code' => $chargesData['failure_code'],
            'failure_message' => $chargesData['failure_message'],
            'outcome' => $chargesData['outcome'],
            'last_payment_error' => $eventData['last_payment_error'],
        ];

        $paymentEvent = PaymentEvent::constructFrom($event);
        $result = $paymentEvent->getFailureData();
        $this->assertEquals($result, $failureData);
    }

    /**
     * @testdox Test get failure data method on PaymentEvent Class for 3D Secure auth failure
     */
    public function testGetFailureDataFor3dSecure()
    {
        $event = $this->eventFailureData();
        // override event data for 3D secure failure
        $event['data']['object']['charges']['data'] = [];
        $event['data']['object']['status'] = 'requires_payment_method';
        unset($event['data']['object']['last_payment_error']['charge']);
        unset($event['data']['object']['last_payment_error']['decline_code']);
        $event['data']['object']['last_payment_error']['code'] = ErrorObject::CODE_PAYMENT_INTENT_AUTHENTICATION_FAILURE;
        $event['data']['object']['last_payment_error']['message'] = 'The provided PaymentMethod has failed authentication. You can provide payment_method_data or a new PaymentMethod to attempt to fulfill this PaymentIntent again.';

        $eventData = $event['data']['object'];

        unset($eventData['last_payment_error']['payment_method']);
        $failureData = [
            'api_version' => $event['api_version'],
            'status' => $eventData['status'],
            'failure_code' => null,
            'failure_message' => null,
            'outcome' => null,
            'last_payment_error' => $eventData['last_payment_error'],
        ];

        $paymentEvent = PaymentEvent::constructFrom($event);
        $result = $paymentEvent->getFailureData();
        $this->assertEquals($result, $failureData);
    }

    /**
     * Sample failure event data.
     *
     * @return array
     */
    private function eventFailureData(): array
    {
        return [
            'id' => 'evt_xxxxxxxxxxxxxxxx',
            'api_version' => '2020-01-01',
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
     * Flatten array keys with values
     *
     * @param array $event
     * @param array $keys
     * @param string $field
     *
     * @return array
     */
    private function flattenArrayKeys($event, $keys = [], $field = null)
    {
        $keys = $keys;
        foreach ($event as $key => $value) {
            $fieldKey = $field.($field ? '.' : null).$key;
            if (is_array($value)) {
                $keys = array_merge($this->flattenArrayKeys($value, $keys, $fieldKey));
                continue;
            }
            $keys[$fieldKey] = $value;
        }
        return $keys;
    }

    /**
     * Get sample event data that the method will receive
     *
     * @return array
     */
    private function eventData(): array
    {
        return [
            'id' => 'evt_00000000000000',
            'type' => 'payment_intent.payment_failed',
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
