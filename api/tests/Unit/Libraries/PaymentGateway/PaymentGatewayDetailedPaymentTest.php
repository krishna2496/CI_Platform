<?php

namespace Tests\Unit\Libraries\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedPayment as DetailedPayment;
use App\Models\PaymentGateway\Payment;
use Faker\Factory as FakerFactory;
use TestCase;

class PaymentGatewayDetailedPaymentTest extends TestCase
{
    private $faker;

    private $paymentGatewayDetailedPayment;

    public function setUp(): void
    {
        $this->faker = FakerFactory::create();
    }

    public function testSetterGetterJsonSerialize()
    {
        $expected = new \StdClass;

        $payment = (new Payment())
            ->setAttribute('payment_gateway', 1)
            ->setAttribute('payment_method_type', 'CARD')
            ->setAttribute('currency', $this->faker->currencyCode)
            ->setAttribute('payment_gateway_account_id', 'acc_12345678')
            ->setAttribute('billing_name', $this->faker->name)
            ->setAttribute('billing_email', $this->faker->email)
            ->setAttribute('billing_phone', $this->faker->phoneNumber)
            ->setAttribute('billing_address_line_1',$this->faker->address)
            ->setAttribute('billing_address_line_2', $this->faker->secondaryAddress)
            ->setAttribute('billing_city', $this->faker->city)
            ->setAttribute('billing_state', $this->faker->state)
            ->setAttribute('billing_country', $this->faker->countryCode)
            ->setAttribute('billing_postal_code', $this->faker->postcode)
            ->setAttribute('ip_address', $this->faker->ipv4)
            ->setAttribute('ip_address_country', $this->faker->countryCode);

        $detailedPayment = (new DetailedPayment())
            ->setPaymentGatewayPaymentId($expected->paymenteGatewayPaymentId = 'pi_test_12345678')
            ->setUserId($expected->userId = 1)
            ->setAmountDonated($expected->amount = 100)
            ->setMissionId($expected->missionId = rand(1, 100))
            ->setHundredPercentSetting($expected->hundredPercentSetting = false)
            ->setCustomerId($expected->customerId = 'cus_1234567')
            ->setOrganizationId($expected->organizationId = $this->faker->uuid)
            ->setTenantId($expected->tenantId = 1)
            ->setPaymentMethodId($expected->paymentMethodId = $this->faker->uuid)
            ->setConnectedAccountId($expected->connectedAccountId = 'acc_12345678')
            ->setPayment($expected->payment = $payment);

        $response = $detailedPayment->jsonSerialize();
        $this->assertSame($response['payment_gateway_payment_id'], $expected->paymenteGatewayPaymentId);
        $this->assertSame($response['user_id'], $expected->userId);
        $this->assertSame($response['mission_id'], $expected->missionId);
        $this->assertSame($response['organization_id'], $expected->organizationId);
        $this->assertSame($response['tenant_id'], $expected->tenantId);
        $this->assertSame($response['connected_account_id'], $expected->connectedAccountId);
        $this->assertSame($response['payment_method_id'], $expected->paymentMethodId);
        $this->assertSame($response['payment'], $expected->payment);
        $this->assertSame($response['amount_donated'], $expected->amount);
        $this->assertSame($response['hundred_percent_setting'], $expected->hundredPercentSetting);
    }
}