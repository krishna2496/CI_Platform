<?php

namespace Tests\Unit\Libraries\PaymentGateway;

use App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount;
use Faker\Factory as FakerFactory;
use StdClass;
use TestCase;

class PaymentGatewayDetailedAccountTest extends TestCase
{
    /**
     * @var App\Libraries\PaymentGateway\PaymentGatewayDetailedAccount
     */
    private $paymentGatewayDetailedAccount;

    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->paymentGatewayDetailedAccount = new PaymentGatewayDetailedAccount;
    }

    public function testSetterGetterJsonSerialize()
    {
        $account = new StdClass;
        $this->paymentGatewayDetailedAccount
            ->setOrganizationId($account->organization_id = $this->faker->uuid())
            ->setPaymentGatewayAccountId($account->payment_gateway_account_id = 'acct_foo')
            ->setName($account->name = $this->faker->company())
            ->setEmail($account->email = $this->faker->email())
            ->setCountry($account->country = $this->faker->countryCode())
            ->setDefaultCurrency($account->default_currency = $this->faker->currencyCode())
            ->setPayoutsEnabled($account->payouts_enabled = $this->faker->boolean())
            ;

        $this->assertSame($account->organization_id, $this->paymentGatewayDetailedAccount->getOrganizationId());
        $this->assertSame($account->payment_gateway_account_id, $this->paymentGatewayDetailedAccount->getPaymentGatewayAccountId());
        $this->assertSame($account->name, $this->paymentGatewayDetailedAccount->getName());
        $this->assertSame($account->email, $this->paymentGatewayDetailedAccount->getEmail());
        $this->assertSame($account->country, $this->paymentGatewayDetailedAccount->getCountry());
        $this->assertSame($account->default_currency, $this->paymentGatewayDetailedAccount->getDefaultCurrency());
        $this->assertSame($account->payouts_enabled, $this->paymentGatewayDetailedAccount->getPayoutsEnabled());

        $jsonAccount = json_decode(json_encode($account), true);
        $this->assertSame($jsonAccount, $this->paymentGatewayDetailedAccount->jsonSerialize());
    }
}
