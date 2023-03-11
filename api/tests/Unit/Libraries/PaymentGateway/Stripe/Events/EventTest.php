<?php

namespace Tests\Unit\Libraries\PaymentGateway\Stripe\Events;

use App\Libraries\Amount;
use App\Libraries\PaymentGateway\Stripe\Events\Event;
use TestCase;

class EventTest extends TestCase
{
    /*
     * @var Event
     */
    private $event;

    public function setUp(): void
    {
        $this->event = Event::constructFrom(
            $this->eventData()
        );
    }

    /**
     * @testdox Test getDataObject method on Event Class
     */
    public function testGetDataObject()
    {
        $data = $this->flattenArrayKeys($this->eventData());

        foreach ($data as $path => $value) {
            if (!strstr($path, 'data.object')) {
                $this->assertSame($value, $this->event->$path);
                continue;
            }
            $path = str_replace('data.object.', null, $path);
            $this->assertSame($value, $this->event->getDataObject($path));
        }
    }

    /**
     * @testdox Test get api version method on Event Class
     */
    public function testGetApiVersion()
    {
        $event = $this->eventData();
        $expected = $event['api_version'];

        $event = Event::constructFrom($event);
        $result = $event->getApiVersion();
        $this->assertEquals($result, $expected);
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
    private function eventData()
    {
        return [
            'created' => 1326853478,
            'livemode' => false,
            'id' => 'evt_00000000000000',
            'type' => 'payment_intent.payment_failed',
            'object' => 'event',
            'request' => null,
            'pending_webhooks' => 1,
            'api_version' => '2020-03-02',
            'data' => [
                'object' => [
                    'id' => 'pi_00000000000000',
                    'object' => 'payment_intent',
                    'amount' => 1000,
                    'amount_capturable' => 0,
                    'amount_received' => 0,
                    'application' => null,
                    'application_fee_amount' => null,
                    'canceled_at' => null,
                    'cancellation_reason' => null,
                    'capture_method' => 'automatic',
                    'charges' => [
                      'object' => 'list',
                      'data' => [],
                      'has_more' => false,
                      'url' => '/v1/charges?payment_intent=pi_1H8JzcBqyp6GnSrSbOQNMmbx'
                    ],
                    'client_secret' => 'pi_1H8JzcBqyp6GnSrSbOQNMmbx_secret_dWEwHkjTR3mvz7yllyZClbNvw',
                    'confirmation_method' => 'automatic',
                    'created' => 1595570572,
                    'currency' => 'usd',
                    'customer' => null,
                    'description' => 'Created by stripe.com/docs demo',
                    'invoice' => null,
                    'last_payment_error' => [
                      'code' => 'payment_intent_payment_attempt_failed',
                      'doc_url' => 'https://stripe.com/docs/error-codes/payment-intent-payment-attempt-failed',
                      'message' => 'The payment failed.',
                      'type' => 'invalid_request_error'
                    ],
                    'livemode' => false,
                    'metadata' => [
                      'tenant_id' => '34',
                      'mission_id' => '10',
                      'organization_id' => '9012929-ASDASD9AA-ASDASD-ASDASD-ASD'
                    ],
                    'next_action' => null,
                    'on_behalf_of' => null,
                    'payment_method' => null,
                    'payment_method_options' => null,
                    'payment_method_types' => [
                    ],
                    'receipt_email' => null,
                    'review' => null,
                    'setup_future_usage' => null,
                    'shipping' => null,
                    'statement_descriptor' => null,
                    'statement_descriptor_suffix' => null,
                    'status' => 'requires_payment_method',
                    'transfer_data' => null,
                    'transfer_group' => null
                ]
            ]
        ];
    }
}
